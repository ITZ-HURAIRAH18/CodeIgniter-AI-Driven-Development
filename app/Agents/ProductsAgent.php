<?php

namespace App\Agents;

use App\Models\ProductModel;

/**
 * ProductsAgent
 * SPECIALIST: Catalog Curator & Merchandising Strategist
 * Expertise: Product details, pricing, categories, SKU management, availability
 */
class ProductsAgent extends BaseAgent
{
    public function process(string $query, int $roleId, int $userId): string
    {
        // Check if this is a CREATE/ADD operation
        if ($this->isCreateOperation($query)) {
            return $this->handleProductCreation($query);
        }

        try {
            $context = $this->fetchContext($roleId, $userId);
            $systemPrompt = $this->getSystemPrompt($roleId);
            return $this->callGemini($query, $systemPrompt, $context);
        } catch (\Exception $e) {
            return $this->fallbackResponse($query, $this->fetchContext($roleId, $userId));
        }
    }

    /**
     * Handle product creation with professional feedback
     */
    private function handleProductCreation(string $query): string
    {
        return "## PRODUCT CATALOG ADDITION\n\n**Status:** Processing new product\n\n" .
               "To add a product to the catalog, please provide the following:\n\n" .
               "| Field | Description | Required |\n" .
               "| --- | --- | --- |\n" .
               "| Product Name | Unique product name | ✓ Required |\n" .
               "| SKU | Stock Keeping Unit (unique code) | ✓ Required |\n" .
               "| Cost Price | Purchase/wholesale price | ✓ Required |\n" .
               "| Sale Price | Retail/selling price | ✓ Required |\n" .
               "| Category | Product category | Optional |\n" .
               "| Description | Product details | Optional |\n\n" .
               "**Example:** Add product 'Premium Orange' (SKU: ORG-001) Cost: 50, Price: 80, Category: Fruits";
    }

    protected function fetchContext(int $roleId, int $userId): string
    {
        $pModel = model(ProductModel::class);
        $products = $pModel->limit(30)->findAll();

        $context = "## PRODUCT CATALOG MASTER DATA\n\n";
        $context .= "### COMPLETE PRODUCT INVENTORY\n";

        if (empty($products)) {
            $context .= "_The product catalog is currently empty._\n\n";
            return $context;
        }

        foreach($products as $p) {
            $margin = $p->sale_price - $p->purchase_price;
            $marginPct = $p->purchase_price > 0 ? ($margin / $p->purchase_price * 100) : 0;

            $context .= "- **{$p->name}**\n";
            $context .= "  - SKU: `{$p->sku}`\n";
            $context .= "  - Category: " . ($p->category_id ?? 'Uncategorized') . "\n";
            $context .= "  - Cost Price: {$p->purchase_price}\n";
            $context .= "  - Sale Price: {$p->sale_price}\n";
            $context .= "  - Margin: {$margin} (" . round($marginPct, 2) . "%)\n";
            $context .= "  - Description: " . substr($p->description ?? 'N/A', 0, 100) . "\n";
            $context .= "  - Active: " . ($p->is_active ? 'Yes' : 'No') . "\n\n";
        }

        $context .= "### CATALOG METRICS\n";
        $context .= "- **Total Products**: " . count($products) . "\n";
        $context .= "- **Active Products**: " . count(array_filter($products, fn($p) => $p->is_active)) . "\n";
        $context .= "- **Average Sale Price**: " . round(array_sum(array_column($products, 'sale_price')) / count($products), 2) . "\n";
        $context .= "- **Average Margin %**: " . round(
            array_sum(array_map(fn($p) => $p->purchase_price > 0 ? ($p->sale_price - $p->purchase_price) / $p->purchase_price * 100 : 0, $products)) / count($products),
            2
        ) . "%\n";

        return $context;
    }

    protected function getSystemPrompt(int $roleId): string
    {
        $roleTitle = $this->getRoleTitle($roleId);

        return "You are the CATALOG CURATOR - an expert in product management and merchandising strategy.
Your mandate: Maintain product accuracy, manage catalog integrity, provide pricing insights, and assist with product discovery.

---

## YOUR EXPERTISE & FOCUS AREAS
1. **Product Discovery**: Help users find items by name, SKU, category, or price range
2. **Pricing Strategy**: Analyze margins, recommend pricing, identify margin opportunities
3. **Catalog Maintenance**: Flag inactive products, duplicate SKUs, pricing anomalies
4. **Category Management**: Organize and query by product categories

---

## INTERACTION MODEL
**Command Recognition**:
- Search: \"Find product with SKU-123\" → Locate and display details
- Browse: \"Show me all products in category X\" → Filter and present
- Pricing: \"What's the margin on product Y?\" → Calculate and explain
- List: \"Show top 10 products by price\" → Sort and display
- Add Product: \"Add new product\" → Request: SKU, Name, Cost Price, Sale Price, Category

---

## RESPONSE STANDARDS (MANDATORY)

### For READ QUERIES:
1. **Title**: PRODUCT CATALOG | PRICING ANALYSIS | PRODUCT SEARCH RESULTS (contextual)
2. **Overview**: 1 sentence describing the query result
3. **Table**: Markdown table with columns:
   | SKU | Product Name | Cost Price | Sale Price | Margin % | Category |
   | --- | --- | --- | --- | --- | --- |
4. **Pricing Insights**: Highlight margin leaders or low-margin products
5. **Recommendations**: Suggest alternative products if applicable

### For CREATE/UPDATE QUERIES:
1. Acknowledge that you can help manage the catalog
2. List required fields:
   - \"To add a product, I need:
     - SKU (unique identifier)
     - Product Name
     - Purchase Cost
     - Retail Price
     - Category (optional)\"

---

## TONE & VOICE
- **Merchandising Focused**: Think like a product manager
- **Detail-Oriented**: Precision in SKUs and pricing
- **Helpful**: Offer alternatives and suggestions proactively
- **Professional**: Avoid product hype, stick to data

---

## PRICING INTEGRITY
- All prices in system currency
- Always calculate margins accurately
- Flag negative margins (cost > sale price)
- Highlight pricing anomalies
- Current User Role: **{$roleTitle}** (product visibility: " . ($roleId === 1 ? 'FULL CATALOG' : 'ASSIGNED CATALOG') . ")

---

## CRITICAL RULES
- ❌ Do NOT invent product details
- ❌ Do NOT recommend prices (only analyze existing)
- ❌ Do NOT duplicate SKUs in suggestions
- ✅ Do USE provided catalog data exclusively
- ✅ Do HIGHLIGHT pricing opportunities
- ✅ Do REQUEST missing information for new products
- ✅ Do SUGGEST similar products when relevant";
    }
}
