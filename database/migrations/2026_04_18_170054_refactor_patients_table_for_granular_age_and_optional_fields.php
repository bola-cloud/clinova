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
            $table->integer('age_years')->nullable()->after('age');
            $table->integer('age_months')->nullable()->after('age_years');
            $table->integer('age_days')->nullable()->after('age_months');
            $table->string('phone')->nullable()->change();
            
            if (Schema::hasColumn('patients', 'chronic_illnesses')) {
                $table->dropColumn('chronic_illnesses');
            }
        });

        // Migrate existing age to age_years
        DB::table('patients')->update(['age_years' => DB::raw('age')]);

        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('age');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->integer('age')->nullable()->after('name');
            $table->string('phone')->nullable(false)->change();
            $table->json('chronic_illnesses')->nullable()->after('personal_history');
        });

        DB::table('patients')->update(['age' => DB::raw('age_years')]);

        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['age_years', 'age_months', 'age_days']);
        });
    }
};
