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
        Schema::table('admins', function (Blueprint $table) {
            $table->decimal('tax_amount', 10, 2)
                  ->nullable()
                  ->default(0)
                  ->after('password')
                  ->comment('Global tax amount');

            $table->decimal('platform_fee', 10, 2)
                  ->nullable()
                  ->default(0)
                  ->after('tax_amount')
                  ->comment('Platform service fee');
    
            $table->decimal('shipping_charges', 10, 2)
                  ->nullable()
                  ->default(0)
                  ->after('platform_fee')
                  ->comment('Global shipping charge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn(['tax_amount','platform_fee','shipping_charges']);
        });
    }
};
