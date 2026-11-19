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
        Schema::table('vendors', function (Blueprint $table) {
            if (! Schema::hasColumn('vendors', 'name')) {
                $table->string('name')->nullable()->after('username');
            }
            if (! Schema::hasColumn('vendors', 'mobile_no')) {
                $table->string('mobile_no', 32)->nullable()->after('name');
            }
            if (! Schema::hasColumn('vendors', 'company_name')) {
                $table->string('company_name')->nullable()->after('mobile_no');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (Schema::hasColumn('vendors', 'company_name')) {
                $table->dropColumn('company_name');
            }
            if (Schema::hasColumn('vendors', 'mobile_no')) {
                $table->dropColumn('mobile_no');
            }
            if (Schema::hasColumn('vendors', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};
