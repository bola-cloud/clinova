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
        Schema::create('specialty_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialty_id')->constrained()->onDelete('cascade');
            $table->string('label');
            $table->string('type'); // text, select
            $table->json('options')->nullable(); // For select types
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specialty_fields');
    }
};
