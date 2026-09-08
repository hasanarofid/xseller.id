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
        if (!Schema::hasTable('bonus_logs')) {
            Schema::create('bonus_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->enum('category', ['sponsor', 'pasangan', 'titik', 'reward', 'penarikan', 'po', 'pal', 'ro', 'tpr', 'tier'])->default('sponsor');
                $table->string('transaction_code')->nullable();
                $table->foreignId('source_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('description');
                $table->decimal('amount', 15, 2);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonus_logs');
    }
};
