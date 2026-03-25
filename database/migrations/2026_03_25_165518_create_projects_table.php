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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('endpoint_destino');
            $table->enum('recaptcha_type', ['v2', 'v3'])->default('v2');
            $table->text('recaptcha_site_key'); // Encriptada
            $table->text('recaptcha_secret_key'); // Encriptada
            $table->float('recaptcha_min_score')->default(0.5); // Para v3
            $table->json('allowed_origins')->nullable();
            $table->uuid('project_token')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
