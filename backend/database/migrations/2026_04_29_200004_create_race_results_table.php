<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('race_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('p1_driver_id')->constrained('drivers');
            $table->foreignId('p2_driver_id')->constrained('drivers');
            $table->foreignId('p3_driver_id')->constrained('drivers');
            $table->unsignedSmallInteger('dnf_count');
            $table->timestamp('recorded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_results');
    }
};
