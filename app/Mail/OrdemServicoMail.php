<?php

namespace App\Mail;

use App\Models\OrdemServico;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrdemServicoMail extends Mailable
{
    use Queueable, SerializesModels;

    protected OrdemServico $ordemServico;
    protected string $tipoDestinatario; // 'consultor' ou 'cliente'

    public function __construct(OrdemServico $ordemServico, string $tipoDestinatario = 'consultor')
    {
        $this->ordemServico = $ordemServico;
        $this->tipoDestinatario = $tipoDestinatario;
    }

    public function envelope(): Envelope
    {
        $destinatario = $this->tipoDestinatario === 'consultor' ? 'Consultor' : 'Cliente';

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: "Ordem de Serviço #" . $this->ordemServico->id . " - Personalitec",
        );
    }

    public function content(): Content
    {
        // Seleciona o template baseado no tipo de destinatário
        $view = $this->tipoDestinatario === 'consultor'
            ? 'emails.ordem-servico-consultor'
            : 'emails.ordem-servico-cliente';

        return new Content(
            view: $view,
            with: [
                'ordemServico' => $this->ordemServico,
                'tipoDestinatario' => $this->tipoDestinatario,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
