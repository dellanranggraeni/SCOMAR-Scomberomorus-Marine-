<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FishingGear extends Model
{
    use HasFactory;

    protected $table = 'fishing_gears';

    protected $fillable = [
        'name',
        'category',
        'target_species',
        'selectivity_level',
        'status',
        'description',
    ];
}
