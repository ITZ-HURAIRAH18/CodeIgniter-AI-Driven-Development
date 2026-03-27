<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\InventoryModel;
use App\Models\InventoryLogModel;
use App\Models\OrderModel;
use RuntimeException;

/**
 * OrderService
 *
 * Core business logic for order creation with safe, transactional stock deduction.
 * Uses pessimistic locking (SELECT ... FOR UPDATE) to prevent overselling under
 * high concurrency.
 */
class OrderService
{
    public function __construct(
        private InventoryModel    $inventoryModel,
        private InventoryLogModel $logModel,
        private OrderModel        $orderModel,
    ) {}

    /**
     * Create a completed order and deduct stock atomically.
     *
     * @param array $data   { branch_id, items: [{product_id, quantity}], notes }
     * @param int   $actorId  The authenticated user's ID
     *
     * @return array  The created order with items
     *
     * @throws \InvalidArgumentException   on validation failure
     * @throws InsufficientStockException  on stock shortage
     * @throws RuntimeException            on DB error
     */
    public function createOrder(array $data, int $actorId): array
    {
        $branchId = (int) $data['branch_id'];
        $items    = $data['items'];

        if (empty($items)) {
            throw new \InvalidArgumentException('Order must contain at least one item.');
        }

        $db = $this->inventoryModel->db;
        $db->transStart();

        try {
            $orderLines   = [];
            $subtotal     = 0.0;
            $totalTax     = 0.0;

            foreach ($items as $line) {
                $productId = (int) $line['product_id'];
                $qty       = (int) $line['quantity'];

                if ($qty <= 0) {
                    throw new \InvalidArgumentException("Quantity must be > 0 for product #{$productId}");
                }

                // ── PESSIMISTIC LOCK ───────────────────────────────────────────
                $invRow = $this->inventoryModel->getRowForUpdate($branchId, $productId);

                if (!$invRow) {
                    throw new InsufficientStockException(
                        "Product #{$productId} is not stocked at branch #{$branchId}."
                    );
                }

                if ($invRow->quantity < $qty) {
                    throw new InsufficientStockException(
                        "Insufficient stock for product #{$productId}. " .
                        "Requested: {$qty}, Available: {$invRow->quantity}"
                    );
                }

                // ── DEDUCT STOCK ──────────────────────────────────────────────
                $qtyBefore = $invRow->quantity;
                $qtyAfter  = $qtyBefore - $qty;

                $db->table('inventory')
                   ->where('id', $invRow->id)
                   ->update(['quantity' => $qtyAfter, 'updated_at' => date('Y-m-d H:i:s')]);

                // Fetch product for price --- query inside transaction is fine
                $product = $db->table('products')->where('id', $productId)->get()->getRow();

                $lineSubtotal = (float) $product->sale_price * $qty;
                $taxAmt       = round($lineSubtotal * ((float) $product->tax_percentage / 100), 4);
                $lineTotal    = $lineSubtotal + $taxAmt;

                $subtotal  += $lineSubtotal;
                $totalTax  += $taxAmt;

                $orderLines[] = [
                    'product_id' => $productId,
                    'quantity'   => $qty,
                    'unit_price' => $product->sale_price,
                    'tax_pct'    => $product->tax_percentage,
                    'tax_amount' => $taxAmt,
                    'line_total' => $lineTotal,
                    // store for logging
                    '_qty_before' => $qtyBefore,
                    '_qty_after'  => $qtyAfter,
                ];
            }

            // ── CREATE ORDER ──────────────────────────────────────────────────
            $orderNumber = $this->orderModel->generateOrderNumber();

            $orderId = $this->orderModel->insert([
                'branch_id'    => $branchId,
                'user_id'      => $actorId,
                'order_number' => $orderNumber,
                'status'       => 'completed',
                'subtotal'     => round((float) $subtotal, 4),
                'tax_amount'   => round((float) $totalTax, 4),
                'grand_total'  => round((float) ($subtotal + $totalTax), 4),
                'notes'        => $data['notes'] ?? null,
            ]);

            // ── INSERT LINE ITEMS & WRITE LEDGER ─────────────────────────────
            foreach ($orderLines as $line) {
                $db->table('order_items')->insert([
                    'order_id'   => $orderId,
                    'product_id' => $line['product_id'],
                    'quantity'   => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'tax_pct'    => $line['tax_pct'],
                    'tax_amount' => $line['tax_amount'],
                    'line_total' => $line['line_total'],
                ]);

                $this->logModel->record(
                    branchId:  $branchId,
                    productId: $line['product_id'],
                    userId:    $actorId,
                    type:      'sale',
                    qtyBefore: $line['_qty_before'],
                    qtyChange: -$line['quantity'],
                    qtyAfter:  $line['_qty_after'],
                    refType:   'order',
                    refId:     $orderId,
                    notes:     "Order #{$orderNumber}",
                );
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new RuntimeException('Transaction failed to commit.');
            }

            return $this->orderModel->find($orderId);

        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }
}
