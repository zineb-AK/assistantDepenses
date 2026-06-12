<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'texte_source' => 'required|string|min:10|max:10000',
        ];
    }

    public function messages(): array
    {
        return [
            'texte_source.required' => 'Le texte du reçu est requis.',
            'texte_source.min' => 'Le texte doit contenir au moins 10 caractères.',
            'texte_source.max' => 'Le texte ne peut pas dépasser 10 000 caractères.',
        ];
    }
}
