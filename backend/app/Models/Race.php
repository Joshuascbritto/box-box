<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Race extends Model
{
    use HasFactory;

    public const STATUS_UPCOMING = 'upcoming';
    public const STATUS_LOCKED = 'locked';
    public const STATUS_FINISHED = 'finished';

    protected $fillable = [
        'season',
        'round',
        'name',
        'circuit',
        'country',
        'race_date',
        'predictions_close_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'season' => 'integer',
            'round' => 'integer',
            'race_date' => 'datetime',
            'predictions_close_at' => 'datetime',
        ];
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(RaceResult::class);
    }
}
