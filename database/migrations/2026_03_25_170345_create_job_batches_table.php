<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Note: job_batches table is already created by Laravel's default migrations
     */
    public function up(): void
    {
        // Table already exists in Laravel 13 - this migration is disabled
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed
    }
};

