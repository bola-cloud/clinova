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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('specialty_id')->nullable()->constrained('specialties')->nullOnDelete();
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->json('specialty_data')->nullable(); // Stores dynamic field answers
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['specialty_id']);
            $table->dropColumn('specialty_id');
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn('specialty_data');
        });
    }
};
