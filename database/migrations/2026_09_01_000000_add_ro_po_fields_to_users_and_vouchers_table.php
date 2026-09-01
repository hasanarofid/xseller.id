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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'ro_points')) {
                $table->integer('ro_points')->default(0)->after('team_points');
            }
            if (!Schema::hasColumn('users', 'po_points')) {
                $table->integer('po_points')->default(0)->after('ro_points');
            }
        });

        Schema::table('vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('vouchers', 'voucher_type')) {
                $table->string('voucher_type')->default('activation')->after('package_name');
            }
        });

        if (!Schema::hasTable('repeat_orders')) {
            Schema::create('repeat_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->onDelete('set null');
                $table->string('voucher_code');
                $table->foreignId('sponsor_id')->nullable()->constrained('users')->onDelete('set null');
                $table->decimal('sponsor_bonus', 15, 2)->default(20000);
                $table->integer('ro_points')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('package_name');
                $table->decimal('amount', 15, 2);
                $table->integer('po_points')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('repeat_orders');

        Schema::table('vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('vouchers', 'voucher_type')) {
                $table->dropColumn('voucher_type');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'ro_points')) {
                $table->dropColumn('ro_points');
            }
            if (Schema::hasColumn('users', 'po_points')) {
                $table->dropColumn('po_points');
            }
        });
    }
};
