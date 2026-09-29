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
            'event_type' => ['required', 'string', 'in:pitch,single,double,triple,home_run,walk,strikeout,out,stolen_base,substitution'],
            'current_batter_id' => ['nullable', 'uuid', 'exists:player_identities,id'],
            'current_pitcher_id' => ['nullable', 'uuid', 'exists:player_identities,id'],
            'payload' => ['nullable', 'array'],
            'players' => ['nullable', 'array'],
            'players.*.player_identity_id' => ['required', 'uuid', 'exists:player_identities,id'],
            'players.*.role' => ['required', 'string', 'in:batter,pitcher,runner,fielder'],
        ];
    }

    public function after(): array
    {
        return [function ($validator) {
            $game = $this->route('game');
            if (! $game || $validator->errors()->isNotEmpty()) { return; }
            if ($game->status === 'finalized') { $validator->errors()->add('game', 'Game is finalized.'); }
            if (in_array($this->input('event_type'), ['stolen_base', 'substitution'])) {
                $validator->errors()->add('event_type', 'This event is not supported by the beta scorer yet.');
            }
            foreach ($this->input('players', []) as $i => $player) {
                $eligible = $game->gameRosterEntries()->where('player_identity_id', $player['player_identity_id'])->where('eligible_to_play', true)->exists();
                // Existing games without frozen rosters may use active team membership.
                if (! $game->gameRosterEntries()->exists()) {
                    $eligible = \App\Models\TeamMembership::whereIn('team_id', [$game->home_team_id, $game->away_team_id])->where('status', 'active')->where('player_identity_id', $player['player_identity_id'])->exists();
                }
                if (! $eligible) { $validator->errors()->add("players.$i", 'Choose an eligible player from this game.'); }
                if ($game->gameRosterEntries()->exists() && in_array($player['role'], ['batter', 'pitcher'])) {
                    $half = \App\Models\GameStateSnapshot::where('game_id', $game->id)->orderByDesc('sequence_number')->value('half_inning') ?? 'top';
                    $battingTeam = $half === 'top' ? $game->away_team_id : $game->home_team_id;
                    $expectedTeam = $player['role'] === 'batter' ? $battingTeam : ($half === 'top' ? $game->home_team_id : $game->away_team_id);
                    if (! $game->rosterEntries()->where('team_id', $expectedTeam)->where('player_identity_id', $player['player_identity_id'])->exists()) {
                        $validator->errors()->add("players.$i", 'Select the player from the correct batting or fielding team.');
                    }
                }
            }
            if ($this->input('event_type') === 'pitch' && ! in_array($this->input('payload.pitch_result'), ['ball', 'strike'])) { $validator->errors()->add('payload', 'Choose ball or strike.'); }
        }];
    }
}
