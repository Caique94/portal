<?php

namespace App\Services;

use App\Models\OrdemServico;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class OrdemServicoPdfService
{
    /**
     * Gera PDF da Ordem de Serviço para o Consultor
     */
    public static function gerarPdfConsultor(OrdemServico $ordemServico): string
    {
        // Carregar relacionamentos se necessário
        if (!$ordemServico->relationLoaded('consultor')) {
            $ordemServico->load('consultor', 'cliente');
        }

        // Renderizar view do email
        $html = View::make('emails.ordem-servico-consultor', [
            'ordemServico' => $ordemServico,
            'tipoDestinatario' => 'consultor',
        ])->render();

        // Gerar PDF
        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4')
            ->setOption('margin-top', 10)
            ->setOption('margin-right', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('enable-local-file-access', true)
            ->setOption('isHtml5ParserEnabled', true);

        // Retornar conteúdo do PDF em string
        return $pdf->output();
    }

    /**
     * Gera PDF da Ordem de Serviço para o Cliente
     */
    public static function gerarPdfCliente(OrdemServico $ordemServico): string
    {
        // Carregar relacionamentos se necessário
        if (!$ordemServico->relationLoaded('cliente')) {
            $ordemServico->load('consultor', 'cliente');
        }

        // Renderizar view do email
        $html = View::make('emails.ordem-servico-cliente', [
            'ordemServico' => $ordemServico,
            'tipoDestinatario' => 'cliente',
        ])->render();

        // Gerar PDF
        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4')
            ->setOption('margin-top', 10)
            ->setOption('margin-right', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('enable-local-file-access', true)
            ->setOption('isHtml5ParserEnabled', true);

        // Retornar conteúdo do PDF em string
        return $pdf->output();
    }

    /**
     * Salva PDF em arquivo temporário
     */
    public static function salvarPdfTemporario(string $pdfContent, string $nomeArquivo): string
    {
        $caminho = storage_path('app/temp/' . $nomeArquivo);

        // Criar diretório se não existir
        if (!is_dir(dirname($caminho))) {
            mkdir(dirname($caminho), 0755, true);
        }

        file_put_contents($caminho, $pdfContent);

        return $caminho;
    }

    /**
     * Retorna nome do arquivo PDF
     */
    public static function getNomeArquivoPdf(OrdemServico $ordemServico): string
    {
        return 'ordem-servico-' . $ordemServico->id . '-' . date('Y-m-d-His') . '.pdf';
    }
}
