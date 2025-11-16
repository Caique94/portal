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

        return new Content(
            view: $view,
            with: [
                'os' => $this->os,
                'numero_os' => str_pad($this->os->id, 8, '0', STR_PAD_LEFT),
                'report' => $this->report,
                'recipient_name' => $this->recipientName,
            ],
        );
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
