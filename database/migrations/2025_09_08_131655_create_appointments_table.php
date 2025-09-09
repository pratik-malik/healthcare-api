<?php

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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('healthcare_professional_id')->constrained('healthcare_professionals')->cascadeOnDelete();
            $table->timestamp('appointment_start_time')->nullable();
            $table->timestamp('appointment_end_time')->nullable();
            $table->enum('status', ['booked', 'completed', 'cancelled'])->default('booked');
            $table->timestamps();

            $table->index(
                ['healthcare_professional_id', 'appointment_start_time', 'appointment_end_time'],
                'appointments_prof_start_end_idx'
            );
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
