<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'race_id',
        'p1_driver_id',
        'p2_driver_id',
        'p3_driver_id',
        'dnf_count',
        'points',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'dnf_count' => 'integer',
            'points' => 'integer',
            'submitted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function p1Driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'p1_driver_id');
    }

    public function p2Driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'p2_driver_id');
    }

    public function p3Driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'p3_driver_id');
    }
}
