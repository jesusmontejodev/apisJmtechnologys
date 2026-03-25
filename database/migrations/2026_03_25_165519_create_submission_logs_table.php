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
        Schema::create('submission_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->ipAddress('ip_address');
            $table->text('user_agent')->nullable();
            $table->enum('status', ['passed', 'blocked', 'error'])->default('error');
            $table->float('recaptcha_score')->nullable(); // Para v3
            $table->string('payload_hash')->nullable();
            $table->integer('response_code')->nullable();
            $table->string('blocked_reason')->nullable();
            $table->timestamps();
            $table->index('project_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_logs');
    }
};
