<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;

class CheckProjectEmails extends Command
{
    protected $signature = 'projects:check-emails';

    protected $description = 'Check which projects have/dont have destination_email configured';

    public function handle()
    {
        $this->info('📧 Verificando proyectos sin email configurado...');
        $this->newLine();

        $withoutEmail = Project::whereNull('destination_email')
            ->orWhere('destination_email', '')
            ->get();

        $withEmail = Project::whereNotNull('destination_email')
            ->where('destination_email', '!=', '')
            ->get();

        if ($withEmail->count()) {
            $this->info('✅ Proyectos CON email configurado:');
            foreach ($withEmail as $project) {
                $this->line("  • {$project->name}: {$project->destination_email}");
            }
            $this->newLine();
        }

        if ($withoutEmail->count()) {
            $this->error('❌ Proyectos SIN email configurado:');
            foreach ($withoutEmail as $project) {
                $this->line("  • {$project->name} (ID: {$project->id})");
            }
            $this->newLine();
            $this->warn('⚠️  Los formularios de estos proyectos NO enviarán emails');
        } else {
            $this->info('✅ Todos los proyectos tienen email configurado');
        }

        return self::SUCCESS;
    }
}
