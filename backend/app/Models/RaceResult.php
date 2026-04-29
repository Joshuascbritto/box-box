<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'race_id',
        'p1_driver_id',
        'p2_driver_id',
        'p3_driver_id',
        'dnf_count',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'dnf_count' => 'integer',
            'recorded_at' => 'datetime',
        ];
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
