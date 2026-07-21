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
        Schema::table('visits', function (Blueprint $table) {
            $table->enum('visit_mode', ['text', 'canvas'])->default('text')->after('type');
            $table->longText('canvas_data')->nullable()->after('visit_mode');
            $table->string('canvas_image_path')->nullable()->after('canvas_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn(['visit_mode', 'canvas_data', 'canvas_image_path']);
        });
    }
};
