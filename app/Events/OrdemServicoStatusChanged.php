<?php

namespace App\Events;

use App\Models\OrdemServico;
use App\Enums\OrdemServicoStatus;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrdemServicoStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public OrdemServico $ordemServico;
    public OrdemServicoStatus|string $oldStatus;
    public OrdemServicoStatus|string $newStatus;
    public array $oldValues;
    public ?int $userId;

    public function __construct(
        OrdemServico $ordemServico,
        OrdemServicoStatus|string $oldStatus,
        OrdemServicoStatus|string $newStatus,
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
