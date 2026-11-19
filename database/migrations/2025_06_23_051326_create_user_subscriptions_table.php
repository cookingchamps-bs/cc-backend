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
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->CascadeOnDelete();
            $table->foreignId('kid_id')->nullable()->constrained('kids')->CascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('role')->nullable();
            $table->string('sub_role')->nullable();
            $table->string('transaction_id')->nullable()->unique();
            $table->string('stripe_product_id')->nullable();
            $table->string('stripe_price_id')->nullable();
            $table->boolean('payment_status')->default(0);;
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(0)->comment('1 = Active subscription, 0 = Inactive subscription');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
