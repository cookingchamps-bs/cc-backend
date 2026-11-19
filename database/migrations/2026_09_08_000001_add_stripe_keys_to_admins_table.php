<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->text('stripe_key')
                  ->nullable()
                  ->after('openai_api_key')
                  ->comment('Stripe publishable key');
            $table->text('stripe_secret')
                  ->nullable()
                  ->after('stripe_key')
                  ->comment('Stripe secret key');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn(['stripe_key', 'stripe_secret']);
        });
    }
};
