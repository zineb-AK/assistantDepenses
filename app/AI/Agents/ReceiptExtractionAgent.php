<?php

namespace App\Ai\Agents;

use App\Prompts\ReceiptExtractionPrompt;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

class ReceiptExtractionAgent implements Agent, Conversational, HasTools, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return (new ReceiptExtractionPrompt())->build();
    }

    public function messages(): iterable
    {
        return [];
    }

    public function tools(): iterable
    {
        return [];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'articles' => $schema->array()->items(
                $schema->object([
                    'libellé' => $schema->string()->description('Nom du produit'),
                    'quantité' => $schema->integer()->description('Quantité achetée'),
                    'prix_unitaire' => $schema->number()->description('Prix par unité en MAD'),
                    'catégorie' => $schema->string()->enum([
                        'alimentaire', 'boissons', 'hygiène', 'entretien', 'autre',
                    ])->description('Catégorie du produit'),
                ]),
            ),
            'total_estimé' => $schema->number()->description('Total estimé du reçu'),
            'devise' => $schema->string()->description('Devise, ex: MAD'),
        ];
    }
}
