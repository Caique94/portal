<?php

namespace App\Mail;

use App\Models\OrdemServico;
use App\Services\OrdemServicoPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrdemServicoMail extends Mailable
{
    use Queueable, SerializesModels;

    protected OrdemServico $ordemServico;
    protected string $tipoDestinatario; // 'consultor' ou 'cliente'
    protected ?string $caminhoArquivoPdf = null;

    public function __construct(OrdemServico $ordemServico, string $tipoDestinatario = 'consultor')
    {
        $this->ordemServico = $ordemServico;
        $this->tipoDestinatario = $tipoDestinatario;

        // Gerar PDF automaticamente
        $this->gerarPdfAnexo();
    }

    /**
     * Gera PDF e armazena o caminho
     */
    private function gerarPdfAnexo(): void
    {
        try {
            // Gerar PDF conforme o tipo de destinatário
            $pdfContent = $this->tipoDestinatario === 'consultor'
                ? OrdemServicoPdfService::gerarPdfConsultor($this->ordemServico)
                : OrdemServicoPdfService::gerarPdfCliente($this->ordemServico);

            // Salvar em arquivo temporário
            $nomeArquivo = OrdemServicoPdfService::getNomeArquivoPdf($this->ordemServico);
            $this->caminhoArquivoPdf = OrdemServicoPdfService::salvarPdfTemporario($pdfContent, $nomeArquivo);
        } catch (\Exception $e) {
            // Log do erro mas não falha o envio do email
            \Illuminate\Support\Facades\Log::error('Erro ao gerar PDF da Ordem de Serviço', [
                'os_id' => $this->ordemServico->id,
                'error' => $e->getMessage(),
            ]);
        }
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
        // Se o PDF foi gerado com sucesso, anexar ao email
        if ($this->caminhoArquivoPdf && file_exists($this->caminhoArquivoPdf)) {
            $nomeArquivo = 'Ordem-de-Servico-' . $this->ordemServico->id . '.pdf';

            return [
                Attachment::fromPath($this->caminhoArquivoPdf)
                    ->as($nomeArquivo)
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
