<?php

namespace App\Mail;

use App\Models\Report;
use App\Models\OrdemServico;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ReportMail extends Mailable
{
    use Queueable, SerializesModels;

    protected Report $report;
    protected OrdemServico $os;
    public string $recipientName;

    public function __construct(Report $report, OrdemServico $os, string $recipientName = '')
    {
        $this->report = $report;
        $this->os = $os;
        $this->recipientName = $recipientName;
    }

    public function envelope(): Envelope
    {
        $osNumber = str_pad($this->os->id, 8, '0', STR_PAD_LEFT);

        $subject = match ($this->report->type) {
            'os_consultor' => "Relatório de Ordem de Serviço #{$osNumber}",
            'os_cliente' => "Ordem de Serviço #{$osNumber} - Personalitec",
            default => "Relatório - OS #{$osNumber}",
        };

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: $subject,
        );
    }

    public function content(): Content
    {
        $view = match ($this->report->type) {
            'os_consultor' => 'emails.reports.os_consultor',
            'os_cliente' => 'emails.reports.os_cliente',
            default => 'emails.reports.default',
        };

        // Calculate totalizador data based on report type
        $totalizadorData = $this->calculateTotalizadorData();

        return new Content(
            view: $view,
            with: [
                'os' => $this->os,
                'numero_os' => str_pad($this->os->id, 8, '0', STR_PAD_LEFT),
                'report' => $this->report,
                'recipient_name' => $this->recipientName,
                'totalizador' => $totalizadorData,
            ],
        );
    }

    /**
     * Calculate totalizador data based on report type
     * For client reports: shows admin perspective (preco_produto based)
     * For consultant reports: shows consultant perspective (valor_hora_consultor based)
     */
    protected function calculateTotalizadorData(): array
    {
        $consultor = $this->os->consultor;
        $cliente = $this->os->cliente;

        $horas = floatval($this->os->qtde_total ?? 0);
        $km = floatval($this->os->km ?? 0);
        $deslocamento = floatval($this->os->deslocamento ?? 0);
        $despesas = floatval($this->os->valor_despesa ?? 0);
        $is_presencial = $this->os->is_presencial ?? false;

        // Get valores_km
        $valor_km_consultor = floatval($consultor->valor_km ?? 0);

        if ($this->report->type === 'os_cliente') {
            // Client email shows ADMIN perspective (what they pay)
            $valor_hora = floatval($cliente->valor_hora ?? 0);

            $valor_horas = $horas * $valor_hora;
            $valor_km_total = $is_presencial ? ($km * floatval($cliente->valor_hora ?? 0)) : 0;
            $valor_deslocamento = $is_presencial ? ($deslocamento * floatval($cliente->valor_hora ?? 0)) : 0;

            return [
                'tipo' => 'admin',
                'valor_hora_label' => 'Valor Hora Cliente',
                'valor_km_label' => 'Valor KM Cliente',
                'valor_hora' => $valor_hora,
                'valor_km' => floatval($cliente->valor_hora ?? 0),
                'horas' => $horas,
                'km' => $km,
                'deslocamento' => $deslocamento,
                'despesas' => $despesas,
                'is_presencial' => $is_presencial,
                'valor_horas' => $valor_horas,
                'valor_km_total' => $valor_km_total,
                'valor_deslocamento' => $valor_deslocamento,
                'total_servico' => $valor_horas + $valor_km_total + $valor_deslocamento,
                'total_geral' => $valor_horas + $valor_km_total + $valor_deslocamento + $despesas,
            ];
        } else {
            // Consultant email shows CONSULTANT perspective (what they receive)
            $valor_hora = floatval($consultor->valor_hora ?? 0);

            $valor_horas = $horas * $valor_hora;
            $valor_km_total = $is_presencial ? ($km * $valor_km_consultor) : 0;
            $valor_deslocamento = $is_presencial ? ($deslocamento * floatval($consultor->valor_hora ?? 0)) : 0;

            return [
                'tipo' => 'consultor',
                'valor_hora_label' => 'Valor Hora Consultor',
                'valor_km_label' => 'Valor KM Consultor',
                'valor_hora' => $valor_hora,
                'valor_km' => $valor_km_consultor,
                'horas' => $horas,
                'km' => $km,
                'deslocamento' => $deslocamento,
                'despesas' => $despesas,
                'is_presencial' => $is_presencial,
                'valor_horas' => $valor_horas,
                'valor_km_total' => $valor_km_total,
                'valor_deslocamento' => $valor_deslocamento,
                'total_servico' => $valor_horas + $valor_km_total + $valor_deslocamento,
                'total_geral' => $valor_horas + $valor_km_total + $valor_deslocamento + $despesas,
            ];
        }
    }

    public function attachments(): array
    {
        $attachments = [];

        if ($this->report->path) {
            $fullPath = Storage::disk('public')->path($this->report->path);

            if (file_exists($fullPath)) {
                $attachments[] = Attachment::fromPath($fullPath)
                    ->as(basename($this->report->path))
                    ->withMime('application/pdf');
            }
        }

        return $attachments;
    }
}
