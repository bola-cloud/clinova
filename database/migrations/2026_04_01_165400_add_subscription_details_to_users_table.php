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
            $table->string('subscription_plan')->default('trial')->after('subscription_active'); // trial, monthly, yearly
            $table->decimal('subscription_price', 10, 2)->default(0)->after('subscription_plan');
            $table->boolean('is_paid')->default(false)->after('subscription_price');
            $table->timestamp('subscription_start_at')->nullable()->after('is_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['subscription_plan', 'subscription_price', 'is_paid', 'subscription_start_at']);
        });
    }
};
