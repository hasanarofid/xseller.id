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
        if (!Schema::hasColumn('users', 'saldo')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('saldo', 15, 2)->default(0)->after('package_name');
                $table->decimal('total_bonus', 15, 2)->default(0)->after('saldo');
            });
        }

        if (!Schema::hasTable('voucher_transfers')) {
            Schema::create('voucher_transfers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
                $table->string('voucher_code');
                $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_transfers');
        if (Schema::hasColumn('users', 'saldo')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['saldo', 'total_bonus']);
            });
        }
    }
};
