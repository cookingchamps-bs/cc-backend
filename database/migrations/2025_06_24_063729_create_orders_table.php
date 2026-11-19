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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->CascadeOnDelete();
            
            $table->string('role')->nullable();
            $table->string('sub_role')->nullable();
            
            $table->decimal('total_amount', 10, 2)->nullable()->default(0);
            $table->decimal('tax_amount', 10, 2)->nullable()->default(0);
            $table->decimal('discount_amount', 10, 2)->nullable()->default(0);
            
            $table->tinyInteger('status')->default(0)
              ->comment('0=pending,1=paid,2=failed,3=cancelled');

            $table->tinyInteger('delivery_status')->default(0)
              ->comment('0=not_started,1=processing,2=shipped,3=delivered,4=failed');
            
            
            $table->string('transaction_id')->nullable();
            $table->string('payment_id')->nullable();
            $table->foreignId('shipping_address_id')->constrained('addresses')->CascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }
        
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
