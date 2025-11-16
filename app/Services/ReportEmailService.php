<?php

namespace App\Services;

use App\Mail\ReportMail;
use App\Models\Report;
use App\Models\ReportEmailLog;
use App\Models\OrdemServico;
use Illuminate\Support\Facades\Mail;

class ReportEmailService
{
    public function send(Report $report): void
    {
        $os = OrdemServico::with(['consultor', 'cliente'])->findOrFail($report->ordem_servico_id);

        [$email, $name] = $this->getRecipient($os, $report->type);

        // Criar log de envio
        $emailLog = ReportEmailLog::create([
            'report_id' => $report->id,
            'recipient_email' => $email,
            'recipient_name' => $name,
            'status' => 'pending',
            'attempts' => 0,
        ]);

        try {
            $emailLog->update(['attempts' => $emailLog->attempts + 1]);

            Mail::to($email, $name)->send(new ReportMail($report, $os, $name));

            $emailLog->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

        } catch (\Exception $e) {
            $emailLog->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function getRecipient(OrdemServico $os, string $type): array
    {
        return match ($type) {
            'os_consultor' => [$os->consultor->email, $os->consultor->name],
            'os_cliente' => $this->getClienteEmailAndName($os),
            default => throw new \Exception("Tipo de relatório desconhecido: {$type}"),
        };
    }

    protected function getClienteEmailAndName(OrdemServico $os): array
    {
        // Buscar e-mail do cliente
        // Assumindo que existe um campo email na tabela cliente
        // Caso não exista, você pode buscar do contato principal
        if (isset($os->cliente->email) && !empty($os->cliente->email)) {
            return [$os->cliente->email, $os->cliente->nome];
        }

        // Fallback: buscar primeiro contato do cliente que recebe e-mails de OS
        $contato = \DB::table('contato')
            ->where('cliente_id', $os->cliente_id)
            ->where('recebe_email_os', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->first();

        if ($contato && !empty($contato->email)) {
            // Retorna o email e o NOME DO CONTATO
            return [$contato->email, $contato->nome];
        }

        throw new \Exception("Cliente #{$os->cliente_id} ({$os->cliente->nome}) não possui contato com e-mail configurado para receber relatórios de OS");
    }
}
