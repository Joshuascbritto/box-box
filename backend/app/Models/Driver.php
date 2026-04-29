<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'team',
        'number',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'number' => 'integer',
        ];
    }
}
