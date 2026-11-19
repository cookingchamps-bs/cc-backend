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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->comment('References the user ID');
            $table->unsignedBigInteger('kid_id')->nullable()->comment('References the kid ID');
            $table->string('role')->nullable()->comment('Role of the user or kid');
            $table->string('type')->nullable()->comment('Type of notification');
            $table->string('title')->nullable()->comment('Notification title');
            $table->text('message')->nullable()->comment('Notification message');
            $table->string('click_action')->nullable();
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->unsignedBigInteger('receiver_id')->nullable();
            $table->string('request_sent_by')->nullable();
            $table->string('request_sent_to')->nullable();
            $table->boolean('status')->default(0)->comment('0 = unseen, 1 = seen');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
