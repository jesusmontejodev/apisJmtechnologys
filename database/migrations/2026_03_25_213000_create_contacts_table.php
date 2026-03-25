<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('subject')->nullable();
            $table->longText('message')->nullable();
            $table->json('form_data')->nullable(); // Todos los datos del formulario
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->float('recaptcha_score')->nullable();
            $table->enum('status', ['received', 'processing', 'sent', 'failed'])->default('received');
            $table->text('error_message')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamps();

            $table->index('project_id');
            $table->index('email');
            $table->index('created_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
