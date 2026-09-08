<?php

namespace App\Http\Requests\Teams;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Handled dynamically by our policy layer, but we default to true here
        // to let the request validation pass through to the controller check.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'sport_id' => ['required', 'uuid', 'exists:sports,id'],
            'age_group' => ['nullable', 'string', 'max:50'], // e.g., '12U', 'Varsity'
            'season_label' => ['nullable', 'string', 'max:50'], // e.g., 'Fall 2026'
        ];
    }
}