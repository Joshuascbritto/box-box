<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('races', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('season');
            $table->unsignedSmallInteger('round');
            $table->string('name');
            $table->string('circuit');
            $table->string('country');
            $table->timestamp('race_date');
            $table->timestamp('predictions_close_at');
            $table->enum('status', ['upcoming', 'locked', 'finished'])->default('upcoming');
            $table->timestamps();

            $table->unique(['season', 'round']);
            $table->index('status');
            $table->index('race_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('races');
    }
};
