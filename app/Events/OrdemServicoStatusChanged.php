<?php

namespace App\Events;

use App\Models\OrdemServico;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrdemServicoStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public OrdemServico $ordemServico;
    public string $oldStatus;
    public string $newStatus;
    public array $oldValues;
    public ?int $userId;

    public function __construct(
        OrdemServico $ordemServico,
        string $oldStatus,
        string $newStatus,
        array $oldValues = [],
        ?int $userId = null
    ) {
        $this->ordemServico = $ordemServico;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->oldValues = $oldValues;
        $this->userId = $userId;
    }
}
