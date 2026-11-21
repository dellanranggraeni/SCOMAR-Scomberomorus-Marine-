<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoralReef extends Model
{
    use HasFactory;

    protected $table = 'coral_reefs';

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'condition_status',
        'coverage_percent',
        'friendly_gear',
        'description',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'coverage_percent' => 'float',
    ];
}
