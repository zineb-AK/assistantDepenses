<?php

namespace App\AI\Agents;

use App\Prompts\ReceiptExtractionPrompt;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasProviderOptions;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

class ReceiptExtractionAgent implements Agent, Conversational, HasProviderOptions, HasTools
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

    public function providerOptions(Lab|string $provider): array
    {
        return [
            'response_format' => ['type' => 'json_object'],
        ];
    }
}
