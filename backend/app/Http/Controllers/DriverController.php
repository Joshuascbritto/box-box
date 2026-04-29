<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\JsonResponse;

class DriverController extends Controller
{
    public function index(): JsonResponse
    {
        $drivers = Driver::query()
            ->where('active', true)
            ->orderBy('team')
            ->orderBy('number')
            ->get(['id', 'name', 'code', 'team', 'number']);

        return response()->json(['data' => $drivers]);
    }
}
