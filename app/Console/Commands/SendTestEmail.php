<?php

namespace App\Console\Commands;

use App\Mail\TestMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestEmail extends Command
{
    protected $signature = 'mail:test {email}';

    protected $description = 'Send a test email to verify mail configuration';

    public function handle()
    {
        $email = $this->argument('email');

        $this->info("📧 Enviando email de prueba a: {$email}");
        $this->newLine();
        $this->info("Configuración actual:");
        $this->line("  MAIL_MAILER: " . config('mail.default'));
        $this->line("  MAIL_HOST: " . config('mail.mailers.smtp.host'));
        $this->line("  MAIL_PORT: " . config('mail.mailers.smtp.port'));
        $this->line("  MAIL_USERNAME: " . config('mail.mailers.smtp.username'));
        $this->line("  MAIL_FROM_ADDRESS: " . config('mail.from.address'));
        $this->line("  MAIL_FROM_NAME: " . config('mail.from.name'));
        $this->newLine();

        try {
            Mail::to($email)->send(new TestMail());

            $this->info("✅ Email enviado exitosamente a: {$email}");
            $this->info("Revisa tu bandeja de entrada (o spam)");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("❌ Error al enviar email:");
            $this->error($e->getMessage());
            $this->newLine();
            $this->warn("Stack trace:");
            $this->line($e->getTraceAsString());
            return self::FAILURE;
        }
    }
}
