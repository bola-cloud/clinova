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
        Schema::table('users', function (Blueprint $column) {
            $column->integer('max_patients')->default(0)->after('followup_fee'); // 0 = unlimited
            $column->integer('max_storage_gb')->default(0)->after('max_patients'); // 0 = unlimited
            $column->bigInteger('used_storage_bytes')->default(0)->after('max_storage_gb');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $column) {
            $column->dropColumn(['max_patients', 'max_storage_gb', 'used_storage_bytes']);
        });
    }
};
