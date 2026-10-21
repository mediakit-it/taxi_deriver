<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PolicyDocument extends Model
{
    protected $fillable = [
        'title',
        'file_path',
        'sort_order',
    ];

    /**
     * Документы в порядке показа: сначала sort_order, затем id.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
