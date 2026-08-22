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
        if (Schema::hasTable('withdrawals') && !Schema::hasColumn('withdrawals', 'proof_of_transfer')) {
            Schema::table('withdrawals', function (Blueprint $table) {
                $table->string('proof_of_transfer')->nullable()->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('withdrawals') && Schema::hasColumn('withdrawals', 'proof_of_transfer')) {
            Schema::table('withdrawals', function (Blueprint $table) {
                $table->dropColumn('proof_of_transfer');
            });
        }
    }
};
