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
        Schema::table('submission_logs', function (Blueprint $table) {
            // Check if email_sent column doesn't exist
            if (!Schema::hasColumn('submission_logs', 'email_sent')) {
                $table->boolean('email_sent')->default(false)->after('blocked_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission_logs', function (Blueprint $table) {
            if (Schema::hasColumn('submission_logs', 'email_sent')) {
                $table->dropColumn('email_sent');
            }
        });
    }
};
