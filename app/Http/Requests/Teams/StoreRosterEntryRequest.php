<?php

namespace App\Http\Requests\Teams;

use Illuminate\Foundation\Http\FormRequest;

class StoreRosterEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'player_identity_id' => ['nullable', 'uuid', 'exists:player_identities,id'],
            'display_name' => ['required_without:player_identity_id', 'nullable', 'string', 'max:100'],
            'birth_year' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'jersey_number' => ['nullable', 'string', 'max:10'],
            'positions' => ['nullable', 'array'],
            'positions.*' => ['string', 'max:10'], // e.g., 'CF', 'P', 'SS'
        ];
    }
}