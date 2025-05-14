<?php

use App\Enums\ScheduleType;
use App\Models\Availability;
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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(Availability::class)->nullable();
            $table->string('name');
            $table->enum('type', ScheduleType::values())->nullable();
            $table->string('excluded_days')->nullable();
            $table->integer('price_in_gbp')->nullable();
            $table->integer('price_in_usd')->nullable();
            $table->integer('price_in_eur')->nullable();
            $table->boolean('active')->default(false);
            $table->date('active_from')->nullable();
            $table->date('active_to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
