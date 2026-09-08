<?php

namespace App\Http\Requests\Imports;

use Illuminate\Foundation\Http\FormRequest;

class StoreImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Require uploader context-permissions [83, 115]
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'import_type' => ['required', 'string', 'in:season_stats,game_log,team_history'],
            'context_type' => ['required', 'string', 'in:player,team'],
            'context_id' => ['required', 'uuid'],
            'source_label' => ['required', 'string', 'max:100'],
            'fidelity_level' => ['required', 'string', 'in:season_totals,box_score,game_log'],
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'], // Max 5MB [184]
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}