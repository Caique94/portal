<?php

namespace App\Services;

use App\Enums\OrdemServicoStatus;
use App\Models\OrdemServico;
use Illuminate\Support\Collection;

class OSValidation
{
    protected OrdemServico $os;

    public function __construct(OrdemServico $os)
    {
        $this->os = $os;
    }

    /**
     * Validate OS before moving to "Aguardando Faturamento"
     */
    public function validateForBilling(): array
    {
        $errors = [];

        // 1. Check cliente exists and has required fiscal data
        if (!$this->os->cliente_id) {
            $errors[] = 'Cliente não definido na Ordem de Serviço.';
        } else {
            $cliente = $this->os->cliente;
            if (!$cliente) {
                $errors[] = 'Cliente inválido.';
            } else {
                // Check fiscal data
                if (empty($cliente->cpf_cnpj)) {
                    $errors[] = 'Cliente não possui CPF/CNPJ cadastrado.';
                }
                if (empty($cliente->email)) {
                    $errors[] = 'Cliente não possui email cadastrado.';
                }
            }
        }

        // 2. Check consultor exists
        if (!$this->os->consultor_id) {
            $errors[] = 'Consultor não definido na Ordem de Serviço.';
        } else {
            $consultor = $this->os->consultor;
            if (!$consultor) {
                $errors[] = 'Consultor inválido.';
            }
        }

        // 3. Check valor_total is set and valid
        if (empty($this->os->valor_total) || !is_numeric($this->os->valor_total) || $this->os->valor_total <= 0) {
            $errors[] = 'Valor total não definido ou inválido.';
        }

        // 4. Check if OS items/produtos exist
        if (!$this->hasValidItems()) {
            $errors[] = 'Ordem de Serviço não possui itens/produtos definidos.';
        }

        // 5. Check for duplicate RPS in same period
        if ($this->hasDuplicateRPS()) {
            $errors[] = 'Já existe uma RPS registrada para este cliente no mesmo período.';
        }

        // 6. Check data_emissao is valid
        if (empty($this->os->data_emissao)) {
            $errors[] = 'Data de emissão não definida.';
        }

        return $errors;
    }

    /**
     * Validate OS before moving to "Aguardando RPS"
     */
    public function validateForRPS(): array
    {
        $errors = [];

        // Must already be faturado
        if ($this->os->status !== OrdemServicoStatus::FATURADO->value) {
            $errors[] = 'Ordem de Serviço não foi faturada.';
        }

        // Must have nr_rps
        if (empty($this->os->nr_rps)) {
            $errors[] = 'Número de RPS não definido.';
        }

        // Must have valor_rps
        if (empty($this->os->valor_rps) || !is_numeric($this->os->valor_rps)) {
            $errors[] = 'Valor da RPS não definido ou inválido.';
        }

        return $errors;
    }

    /**
     * Check if OS can be edited
     */
    public function canEdit(): bool
    {
        $status = OrdemServicoStatus::tryFrom($this->os->status);
        return $status && $status->isEditable();
    }

    /**
     * Check if OS has valid items
     */
    public function hasValidItems(): bool
    {
        // Check produto_tabela_id
        if (!empty($this->os->produto_tabela_id)) {
            return true;
        }

        // Check if has related products through ordem_servico_produtos
        if (method_exists($this->os, 'produtos') && $this->os->produtos()->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Check if this OS has duplicate in same period
     */
    protected function hasDuplicateRPS(): bool
    {
        if (empty($this->os->cliente_id) || empty($this->os->data_emissao)) {
            return false;
        }

        // Check if another OS for same client with RPS exists in same month
        $startOfMonth = $this->os->data_emissao->startOfMonth();
        $endOfMonth = $this->os->data_emissao->endOfMonth();

        $exists = OrdemServico::where('cliente_id', $this->os->cliente_id)
            ->where('id', '!=', $this->os->id)
            ->whereBetween('data_emissao', [$startOfMonth, $endOfMonth])
            ->whereNotNull('nr_rps')
            ->exists();

        return $exists;
    }

    /**
     * Get all validation errors for given target status
     */
    public function validateTransition(OrdemServicoStatus $targetStatus): array
    {
        return match($targetStatus) {
            OrdemServicoStatus::FATURADO => $this->validateForBilling(),
            OrdemServicoStatus::AGUARDANDO_RPS => $this->validateForRPS(),
            default => [],
        };
    }

    /**
     * Check if transition is allowed with validations
     */
    public function canTransitionTo(OrdemServicoStatus $targetStatus): bool
    {
        return empty($this->validateTransition($targetStatus));
    }

    /**
     * Validate that all required fields are filled
     */
    public function validateRequiredFields(): array
    {
        $errors = [];

        $required = [
            'consultor_id' => 'Consultor',
            'cliente_id' => 'Cliente',
            'data_emissao' => 'Data de Emissão',
            'valor_total' => 'Valor Total',
        ];

        foreach ($required as $field => $label) {
            if (empty($this->os->{$field})) {
                $errors[$field] = "{$label} é obrigatório.";
            }
        }

        return $errors;
    }
}
