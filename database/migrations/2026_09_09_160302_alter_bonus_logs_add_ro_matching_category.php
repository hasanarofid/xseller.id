<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah ENUM category di bonus_logs → tambah 'ro_matching' dan 'generasi'
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

    public function down(): void
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
            'tpr',
            'tier'
        ) NOT NULL DEFAULT 'sponsor'");
    }
};
