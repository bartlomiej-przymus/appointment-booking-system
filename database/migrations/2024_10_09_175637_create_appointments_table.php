<?php

use App\Enums\AppointmentStatus;
use App\Models\Order;
use App\Models\Schedule;
use App\Models\User;
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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('time_slot');
            $table->integer('duration');
            $table->enum('status', AppointmentStatus::values());
            $table->foreignIdFor(Order::class)->nullable();
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(Schedule::class);
            $table->timestamps();
            $table->unique(['schedule_id', 'date', 'time_slot']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
