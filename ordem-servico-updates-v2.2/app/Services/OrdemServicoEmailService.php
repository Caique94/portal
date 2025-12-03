<?php

namespace App\Services;

use App\Models\OrdemServico;
use App\Mail\OrdemServicoMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class OrdemServicoEmailService
{
    /**
     * Enviar Ordem de Serviço para Consultor
     */
    public function enviarParaConsultor(OrdemServico $ordemServico): bool
    {
        try {
            // Carregar relacionamentos se não estiverem carregados
            if (!$ordemServico->relationLoaded('consultor')) {
                $ordemServico->load('consultor', 'cliente');
            }

            $consultor = $ordemServico->consultor;

            if (!$consultor || !$consultor->email) {
                Log::warning('Consultor não encontrado ou sem email', ['os_id' => $ordemServico->id]);
                return false;
            }

            Mail::to($consultor->email)
                ->send(new OrdemServicoMail($ordemServico, 'consultor'));

            Log::info('Ordem de Serviço enviada para Consultor', [
                'os_id' => $ordemServico->id,
                'consultor_email' => $consultor->email,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao enviar Ordem de Serviço para Consultor', [
                'os_id' => $ordemServico->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Enviar Ordem de Serviço para Cliente
     */
    public function enviarParaCliente(OrdemServico $ordemServico): bool
    {
        try {
            // Carregar relacionamentos se não estiverem carregados
            if (!$ordemServico->relationLoaded('cliente')) {
                $ordemServico->load('cliente', 'consultor');
            }

            $cliente = $ordemServico->cliente;

            if (!$cliente) {
                Log::warning('Cliente não encontrado', ['os_id' => $ordemServico->id]);
                return false;
            }

            // Buscar contato do cliente que tem flag recebe_email_os ativa
            $contato = $cliente->contatos()
                ->where('recebe_email_os', true)
                ->first();

            if (!$contato || !$contato->email) {
                Log::warning('Contato do cliente sem email ou sem permissão para receber OS', [
                    'os_id' => $ordemServico->id,
                    'cliente_id' => $cliente->id,
                ]);
                return false;
            }

            // Validar se é um email válido
            if (!filter_var($contato->email, FILTER_VALIDATE_EMAIL)) {
                Log::warning('Email do contato inválido', [
                    'os_id' => $ordemServico->id,
                    'cliente_id' => $cliente->id,
                    'email' => $contato->email,
                ]);
                return false;
            }

            Mail::to($contato->email)
                ->send(new OrdemServicoMail($ordemServico, 'cliente'));

            Log::info('Ordem de Serviço enviada para Cliente', [
                'os_id' => $ordemServico->id,
                'cliente_email' => $contato->email,
                'contato_id' => $contato->id,
                'contato_nome' => $contato->nome,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao enviar Ordem de Serviço para Cliente', [
                'os_id' => $ordemServico->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Enviar para ambos (Consultor e Cliente)
     */
    public function enviarParaAmbos(OrdemServico $ordemServico): array
    {
        return [
            'consultor' => $this->enviarParaConsultor($ordemServico),
            'cliente' => $this->enviarParaCliente($ordemServico),
        ];
    }
}
