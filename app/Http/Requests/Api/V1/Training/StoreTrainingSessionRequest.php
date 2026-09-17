<?php

namespace App\Http\Requests\Api\V1\Training;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'player_identity_id' => ['nullable', 'string', 'exists:player_identities,id'],
            'session_date'       => ['required', 'date'],
            'program_day'        => ['nullable', 'integer', 'min:1', 'max:7'],
            'workout_name'       => ['required', 'string', 'max:255'],
            'gym_location'       => ['nullable', 'string', 'max:255'],
            'general_notes'      => ['nullable', 'string'],
            'rpe_overall'        => ['nullable', 'numeric', 'min:1', 'max:10'],

            'sets'                   => ['required', 'array', 'min:1'],
            'sets.*.exercise_name'   => ['required', 'string'],
            'sets.*.exercise_id'     => ['nullable', 'string', 'exists:exercises,id'],
            'sets.*.section'         => ['nullable', 'string', 'in:primary,warmup,cooldown,accessory,optional'],
            'sets.*.set_number'      => ['required', 'integer', 'min:1'],
            'sets.*.weight_lbs'      => ['nullable', 'numeric', 'min:0'],
            'sets.*.reps'            => ['nullable', 'integer', 'min:0'],
            'sets.*.duration_seconds'=> ['nullable', 'integer', 'min:0'],
            'sets.*.rir'             => ['nullable', 'string', 'max:20'],
            'sets.*.tempo'           => ['nullable', 'string', 'max:20'],
            'sets.*.set_notes'       => ['nullable', 'string'],
        ];
    }
}
^X
x

