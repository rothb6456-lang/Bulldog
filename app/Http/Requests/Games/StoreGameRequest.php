<?php

namespace App\Http\Requests\Games;

use App\Models\Team;
use Illuminate\Foundation\Http\FormRequest;

class StoreGameRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Ensure the logged-in user is a Team Admin or Coach of the selected Home Team.
     */
    public function authorize(): bool
    {
        $homeTeamId = $this->input('home_team_id');
        if (!$homeTeamId) {
            return false;
        }

        $team = Team::find($homeTeamId);
        if (!$team) {
            return false;
        }

        // Check if user has admin/coach role on the Home Team
        return $team->roleAssignments()
            ->where('user_id', $this->user()->id)
            ->whereIn('role_type', ['team_admin', 'coach'])
            ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'home_team_id' => ['required', 'uuid', 'exists:teams,id'],
            'away_team_id' => [
                'required',
                'uuid',
                'exists:teams,id',
                'different:home_team_id' // A team cannot play itself
            ],
            'ruleset_id' => ['required', 'uuid', 'exists:rulesets,id'],
            'scheduled_at' => ['required', 'date', 'after_or_equal:today'],
            'location' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'away_team_id.different' => 'The Away Team must be different from the Home Team.',
            'scheduled_at.after_or_equal' => 'Matchups cannot be scheduled in the past.',
        ];
    }
}