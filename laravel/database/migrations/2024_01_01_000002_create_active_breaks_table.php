<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('active_breaks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('break_id')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('user_name');
            $table->string('user_email');
            $table->string('department')->nullable();
            $table->string('tl_email')->nullable();
            $table->enum('break_type', ['15m', '60m']);
            $table->string('break_label');
            $table->integer('allowed_minutes');
            $table->timestamp('started_at');
            $table->timestamp('expected_end_at');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('break_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('break_id')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('user_name');
            $table->string('user_email');
            $table->string('department')->nullable();
            $table->string('tl_email')->nullable();
            $table->enum('break_type', ['15m', '60m']);
            $table->string('break_label');
            $table->integer('allowed_minutes');
            $table->timestamp('started_at');
            $table->timestamp('ended_at');
            $table->integer('duration_minutes')->default(0);
            $table->integer('duration_seconds')->default(0);
            $table->integer('over_minutes')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });

        // Indexes for break_history
        Schema::table('break_history', function (Blueprint $table) {
            $table->index('user_email');
            $table->index('tl_email');
            $table->index(['tl_email', 'started_at']);
            $table->index(['user_email', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('break_history');
        Schema::dropIfExists('active_breaks');
    }
};
