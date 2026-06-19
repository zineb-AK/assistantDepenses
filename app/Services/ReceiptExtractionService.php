<?php

namespace App\Services;

use App\Ai\Agents\ReceiptExtractionAgent;

class ReceiptExtractionService
{
    public function extract(string $texteBrut): array
    {
        $agent = new ReceiptExtractionAgent;

        $response = $agent->prompt(
            prompt: $this->buildPrompt($texteBrut),
            provider: 'groq',
            model: config('ai.groq.model', 'llama-3.3-70b-versatile'),
        );

        return $response->structured;
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
