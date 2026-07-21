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
        Schema::table('visit_templates', function (Blueprint $table) {
            $table->string('visit_mode')->default('text')->after('template_name');
            $table->longText('canvas_data')->nullable()->after('visit_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visit_templates', function (Blueprint $table) {
            //
        });
    }
};
