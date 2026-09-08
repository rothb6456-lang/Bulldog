<?php

namespace App\Http\Requests\Scoring;

use Illuminate\Foundation\Http\FormRequest;

class StoreScoringEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Explicitly check game scoring authority via policy layer
        return $this->user()->can('score', $this->route('game'));
    }

    public function rules(): array
    {
        return [
            'event_family' => ['required', 'string', 'in:plate_appearance,baserunning,pitching,defense,game_admin'],
            'event_type' => ['required', 'string', 'in:pitch,single,double,triple,home_run,walk,strikeout,stolen_base,substitution'],
            'current_batter_id' => ['nullable', 'uuid', 'exists:player_identities,id'],
            'current_pitcher_id' => ['nullable', 'uuid', 'exists:player_identities,id'],
            'payload' => ['nullable', 'array'],
            'players' => ['nullable', 'array'],
            'players.*.player_identity_id' => ['required', 'uuid', 'exists:player_identities,id'],
            'players.*.role' => ['required', 'string', 'in:batter,pitcher,runner,fielder'],
        ];
    }
}