<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AppDeletionStep extends Model
{
    protected $fillable = [
        'image_path',
        'text',
        'sort_order',
    ];

    /**
     * Шаги в порядке показа: сначала sort_order, затем id.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
