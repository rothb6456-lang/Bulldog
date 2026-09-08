<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasUuid
{
    /**
     * Boot the trait to generate UUID on creation.
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Disable auto-incrementing.
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Set key type to string.
     */
    public function getKeyType(): string
    {
        return 'string';
    }
}
