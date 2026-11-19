<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keep one row per non-null transaction_id (lowest id wins).
        $duplicates = DB::table('user_subscriptions')
            ->select('transaction_id', DB::raw('MIN(id) as keep_id'))
            ->whereNotNull('transaction_id')
            ->where('transaction_id', '!=', '')
            ->groupBy('transaction_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('user_subscriptions')
                ->where('transaction_id', $dup->transaction_id)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }

        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->unique('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropUnique('user_subscriptions_transaction_id_unique');
        });
    }
};
