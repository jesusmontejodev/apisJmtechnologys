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
        Schema::table('projects', function (Blueprint $table) {
            // Destination email where form submissions will be sent
            $table->string('destination_email')->nullable()->after('allowed_origins');
            
            // Email subject for notifications
            $table->string('email_subject')->default('Nuevo mensaje del formulario')->after('destination_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['destination_email', 'email_subject']);
        });
    }
};
