<?php

use App\Models\Day;
use App\Models\Schedule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('day_schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Day::class);
            $table->foreignIdFor(Schedule::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('day_schedule');
    }
};
