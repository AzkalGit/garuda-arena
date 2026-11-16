<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('field_id')->constrained()->restrictOnDelete();
            $table->date('date');
            $table->unsignedTinyInteger('start_hour');
            $table->unsignedTinyInteger('duration');
            $table->unsignedInteger('price_per_hour');
            $table->unsignedInteger('total');
            $table->string('status', 30);
            $table->timestamp('expires_at')->nullable();
            $table->string('proof_path')->nullable();
            $table->string('proof_name')->nullable();
            $table->timestamp('proof_submitted_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->string('cancelled_reason')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['field_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
