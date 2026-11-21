<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatchData extends Model
{
    use HasFactory;

    protected $table = 'catches';

    protected $fillable = [
        'year',
        'season',
        'period',
        'total_catch',
        'total_count',
        'trips',
        'cpue',
        'notes',
    ];

    protected $casts = [
        'total_catch' => 'float',
        'total_count' => 'integer',
        'trips' => 'integer',
        'cpue' => 'float',
    ];

    /**
     * Hitung CPUE otomatis saat data disimpan jika belum diisi.
     */
    protected static function booted(): void
    {
        static::saving(function ($model) {
            if ($model->trips > 0 && (!$model->cpue || $model->isDirty(['total_catch', 'trips']))) {
                $model->cpue = round($model->total_catch / $model->trips, 2);
            }
        });
    }
}
