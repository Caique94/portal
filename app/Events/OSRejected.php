<?php

namespace App\Events;

use App\Models\OrdemServico;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OSRejected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public OrdemServico $ordemServico;
    public ?string $reason;

    public function __construct(OrdemServico $ordemServico, ?string $reason = null)
    {
        $this->ordemServico = $ordemServico;
        $this->reason = $reason;
    }
}
