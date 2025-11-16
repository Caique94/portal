<?php

namespace App\Events;

use App\Models\OrdemServico;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OSApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public OrdemServico $ordemServico;

    public function __construct(OrdemServico $ordemServico)
    {
        $this->ordemServico = $ordemServico;
    }
}
