<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FishingGround extends Model
{
    use HasFactory;

    protected $table = 'fishing_grounds';

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'coordinates_label',
        'depth_meters',
        'recommended_gear',
        'status',
        'description',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];
}
