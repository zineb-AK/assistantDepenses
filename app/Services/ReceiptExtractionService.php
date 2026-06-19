<?php

namespace App\Services;

use App\AI\Agents\ReceiptExtractionAgent;
use Illuminate\Support\Facades\Log;

class ReceiptExtractionService
{
    public function extract(string $texteBrut): array
    {
        $agent = new ReceiptExtractionAgent;

        $response = $agent->prompt(
            prompt: $this->buildPrompt($texteBrut),
            provider: 'groq',
            model: config('ai.providers.groq.model', 'llama-3.3-70b-versatile'),
        );

        $text = $response->text;

        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/', $text, $matches)) {
            $text = $matches[1];
        }

        $data = json_decode(trim($text), true);

        if (! is_array($data) || ! isset($data['articles'])) {
            Log::warning('ReceiptExtractionService: unexpected response', [
                'text' => $response->text,
            ]);
            throw new \RuntimeException('La réponse de l\'IA n\'est pas un JSON valide ou ne contient pas la structure attendue.');
        }

        return $data;
    }

    private function buildPrompt(string $texteBrut): string
    {
        return <<<PROMPT
Voici le texte d'un reçu fournisseur marocain :

---
{$texteBrut}
---

Extrais tous les articles et retourne le JSON structuré demandé.
PROMPT;
    }
}