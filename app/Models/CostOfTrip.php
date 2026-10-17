<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostOfTrip extends Model  // ← было LocationPrice, исправили на CostOfTrip
{
    use HasFactory;

    protected $table = 'cost_of_trip';
    
    protected $fillable = [
        'name',
        'price'
    ];
}