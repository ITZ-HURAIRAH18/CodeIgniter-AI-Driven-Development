<?php

namespace App\Agents;

/**
 * BaseAgent - Foundation for all specialized AI Agents
 * Provides common infrastructure, Gemini API communication, and shared utilities.
 */
abstract class BaseAgent
{
    protected $geminiKey;
    protected $geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function __construct(?string $geminiKey = null)
    {
        $this->geminiKey = $geminiKey ?? getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? null);
    }

    /**
     * Process the user query with specialized agent logic.
     * Each agent must implement their domain-specific behavior.
     */
    abstract public function process(string $query, int $roleId, int $userId): string;

    /**
     * Detect if query is for CREATE/UPDATE operation
     */
    protected function isCreateOperation(string $query): bool
    {
        $q = strtolower($query);
        return preg_match('/\b(add|create|insert|new|register|create|update|modify|change)\b/', $q) > 0;
    }

    /**
     * Fetch domain-specific data context.
     * Override in subclasses for specialized data retrieval.
     */
    abstract protected function fetchContext(int $roleId, int $userId): string;

    /**
     * Get the specialized system prompt for this agent.
     * Each agent defines its personality and instructions.
     */
    abstract protected function getSystemPrompt(int $roleId): string;

    /**
     * Generate professional CREATE/UPDATE request response
     * Override in subclasses for domain-specific fields
     */
    protected function handleCreateOperation(string $query): string
    {
        return "## REQUEST PROCESSING\n\nI can help you add or update inventory. Please provide the following details:\n\n- **Product Name or SKU**\n- **Quantity**\n- **Branch/Location**\n- **Reorder Level (optional)**\n\nExample: 'Add 50 units of Orange to East branch with reorder level of 10'";
    }

    /**
     * Base Communication with Gemini API with timeout protection
     */
    protected function callGemini(string $query, string $systemPrompt, string $context): string
    {
        // Validate API key exists before attempting call
        if (!$this->geminiKey || empty(trim($this->geminiKey))) {
            return $this->fallbackResponse($query, $context);
        }

        $url = $this->geminiUrl . '?key=' . trim($this->geminiKey);
        
        // Compress prompt to reduce payload size and improve speed
        $compactPrompt = $this->compressPrompt($systemPrompt);
        $payload = [
            'contents' => [[
                'parts' => [[
                    'text' => "$compactPrompt\n\nDATA:\n$context\n\nQ: $query"
                ]]
            ]]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);  // 12 second timeout (reduced from 30)
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        $res = curl_exec($ch);
        $errno = curl_errno($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Handle timeout gracefully
        if ($errno === CURLE_OPERATION_TIMEDOUT || $errno === CURLE_COULDNT_RESOLVE_HOST) {
            return $this->fallbackResponse($query, $context);
        }

        if ($code !== 200) {
            return $this->fallbackResponse($query, $context);
        }

        $json = json_decode($res, true);
        return trim($json['candidates'][0]['content']['parts'][0]['text'] ?? '');
    }

    /**
     * Fallback when API is unavailable or times out
     */
    protected function fallbackResponse(string $query, string $context): string
    {
        return "**SYSTEM NOTICE: PRIMARY AI SERVICE TEMPORARILY UNAVAILABLE**\n\nRaw System Context:\n" . substr($context, 0, 500) . "...\n\nPlease try again in a moment.";
    }

    /**
     * Compress system prompt to reduce payload size and improve response time
     */
    private function compressPrompt(string $prompt): string
    {
        // Remove verbose sections and compress into concise instructions
        $compact = preg_replace('/---+/', '-', $prompt); // Reduce separators
        $compact = preg_replace('/\n\n+/', "\n", $compact); // Remove extra newlines
        return $compact;
    }

    protected function getRoleTitle(int $roleId): string
    {
        return [1 => 'System Administrator', 2 => 'Regional Manager', 3 => 'Sales Associate'][$roleId] ?? 'System User';
    }
}
