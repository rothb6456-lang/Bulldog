<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class CareerAggregate extends Model
{
    use HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'subject_type',
        'subject_id',
        'stat_key',
        'stat_value',
        'source_type',
        'fidelity_level',
    ];

    public function subject()
    {
        return $this->morphTo();
    }
}