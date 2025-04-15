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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('np_city')->nullable();
            $table->string('np_city_ref')->nullable();
            $table->string('np_branch')->nullable();
            $table->string('np_branch_ref')->nullable();
            $table->string('delivery_method')->default('courier'); // nposhta або courier
            $table->text('address')->nullable()->change(); // робимо address необов’язковим
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('address')->nullable(false)->change(); // повернемо як було
            $table->dropColumn(['np_city', 'np_city_ref', 'np_branch', 'np_branch_ref', 'delivery_method']);
        });
    }
};
