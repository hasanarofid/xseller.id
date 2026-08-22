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
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'team_points')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('team_points')->default(0)->after('right_points');
            });
        }

        if (!Schema::hasTable('tpr_requests')) {
            Schema::create('tpr_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('package_name');
                $table->decimal('amount', 15, 2);
                $table->decimal('monthly_share_percent', 5, 2);
                $table->decimal('monthly_share_amount', 15, 2);
                $table->string('proof_of_transfer')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->text('admin_notes')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tpr_requests');

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'team_points')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('team_points');
            });
        }
    }
};
