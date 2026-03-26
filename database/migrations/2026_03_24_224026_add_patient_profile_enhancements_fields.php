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
        Schema::table('patients', function (Blueprint $table) {
            $table->text('personal_history')->nullable();
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->foreignId('parent_visit_id')->nullable()->constrained('visits')->onDelete('cascade');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('secretary_name')->nullable();
            $table->string('secretary_phone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('personal_history');
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_visit_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['secretary_name', 'secretary_phone']);
        });
    }
};
