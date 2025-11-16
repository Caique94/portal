<?php

namespace App\Mail;

use App\Models\RelatorioFechamento;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RelatorioFechamentoMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public RelatorioFechamento $relatorioFechamento)
    {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Seu Relatório de Fechamento #' . $this->relatorioFechamento->id . ' foi Aprovado',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.relatorio-fechamento',
            with: [
                'relatorio' => $this->relatorioFechamento,
                'consultor' => $this->relatorioFechamento->consultor,
                'periodo_inicio' => $this->relatorioFechamento->data_inicio->format('d/m/Y'),
                'periodo_fim' => $this->relatorioFechamento->data_fim->format('d/m/Y'),
                'valor_total' => number_format($this->relatorioFechamento->valor_total, 2, ',', '.'),
                'total_os' => $this->relatorioFechamento->total_os,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
