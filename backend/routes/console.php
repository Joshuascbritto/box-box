<?php

use App\Models\MagicLink;
use Illuminate\Support\Facades\Schedule;

// Prune magic-link tokens older than 24 hours (used or expired).
// In Laravel 11 the schedule lives in routes/console.php.
Schedule::call(function () {
    MagicLink::where('created_at', '<', now()->subDay())->delete();
})->daily()->name('magic-links:prune');
