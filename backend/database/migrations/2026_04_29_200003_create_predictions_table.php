<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('race_id')->constrained()->cascadeOnDelete();
            $table->foreignId('p1_driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->foreignId('p2_driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->foreignId('p3_driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->unsignedSmallInteger('dnf_count')->nullable();
            // Score calculated when the race finishes (Stage 3).
            $table->integer('points')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'race_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
