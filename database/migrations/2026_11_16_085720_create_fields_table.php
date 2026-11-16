<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 20);
            $table->unsignedInteger('price_per_hour');
            $table->string('location');
            $table->text('description');
            $table->string('size', 30);
            $table->string('capacity', 30);
            $table->json('facilities');
            $table->string('image');
            $table->decimal('rating', 2, 1)->default(0);
            $table->unsignedSmallInteger('reviews_count')->default(0);
            $table->string('status', 20)->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fields');
    }
};
