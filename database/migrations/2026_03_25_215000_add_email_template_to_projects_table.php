<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->enum('email_template', ['form', 'lead'])
                ->default('form')
                ->after('destination_email')
                ->comment('Template tipo para los emails: form (predeterminado) o lead');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('email_template');
        });
    }
};
