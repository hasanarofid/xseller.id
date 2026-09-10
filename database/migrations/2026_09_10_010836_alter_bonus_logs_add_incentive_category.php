<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE bonus_logs MODIFY COLUMN category ENUM(
            'sponsor',
            'pasangan',
            'titik',
            'reward',
            'penarikan',
            'po',
            'pal',
            'ro',
            'ro_matching',
            'tpr',
            'tier',
            'generasi',
            'incentive'
        ) NOT NULL DEFAULT 'sponsor'");

        Schema::table('bonus_logs', function (Blueprint $table) {
            $table->decimal('qualified_amount', 15, 2)->nullable()->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bonus_logs', function (Blueprint $table) {
            $table->dropColumn('qualified_amount');
        });

        DB::statement("ALTER TABLE bonus_logs MODIFY COLUMN category ENUM(
            'sponsor',
            'pasangan',
            'titik',
            'reward',
            'penarikan',
            'po',
            'pal',
            'ro',
            'ro_matching',
            'tpr',
            'tier',
            'generasi'
        ) NOT NULL DEFAULT 'sponsor'");
    }
};
