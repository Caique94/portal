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

        // Remover tags html/body/doctype que causam problema no DomPDF
        $html = self::prepararHtmlParaPdf($html);

        // Gerar PDF com opções otimizadas para DomPDF
        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 5)
            ->setOption('margin-right', 5)
            ->setOption('margin-bottom', 5)
            ->setOption('margin-left', 5)
            ->setOption('enable-local-file-access', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('disable_html5_dom', false)
            ->setOption('dpi', 96)
            ->setOption('defaultFont', 'Arial');

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

        // Remover tags html/body/doctype que causam problema no DomPDF
        $html = self::prepararHtmlParaPdf($html);

        // Gerar PDF com opções otimizadas para DomPDF
        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 5)
            ->setOption('margin-right', 5)
            ->setOption('margin-bottom', 5)
            ->setOption('margin-left', 5)
            ->setOption('enable-local-file-access', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('disable_html5_dom', false)
            ->setOption('dpi', 96)
            ->setOption('defaultFont', 'Arial');

        // Retornar conteúdo do PDF em string
        return $pdf->output();
    }

    /**
     * Prepara HTML para melhor renderização no DomPDF
     * Remove tags que causam problemas e normaliza o CSS
     */
    private static function prepararHtmlParaPdf(string $html): string
    {
        // Remover DOCTYPE
        $html = preg_replace('/<!\s*doctype[^>]*>/i', '', $html);

        // Remover tags html, head, body (DomPDF já lida com isso)
        $html = preg_replace('/<\s*html[^>]*>/i', '', $html);
        $html = preg_replace('/<\s*\/html\s*>/i', '', $html);
        $html = preg_replace('/<\s*head[^>]*>.*?<\s*\/head\s*>/is', '', $html);
        $html = preg_replace('/<\s*body[^>]*>/i', '', $html);
        $html = preg_replace('/<\s*\/body\s*>/i', '', $html);

        // Manter apenas o conteúdo da table principal
        // Isso preserva toda a estrutura visual

        return $html;
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
