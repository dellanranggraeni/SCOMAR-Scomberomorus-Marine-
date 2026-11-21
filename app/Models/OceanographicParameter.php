<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OceanographicParameter extends Model
{
    use HasFactory;

    protected $table = 'oceanographic_parameters';

    protected $fillable = [
        'year',
        'temp',
        'chlorophyll',
        'tss',
        'current',
        'status',
        'habitat_score',
        'source',
        'notes',
    ];

    protected $casts = [
        'temp' => 'float',
        'chlorophyll' => 'float',
        'tss' => 'float',
        'current' => 'float',
    ];
}
