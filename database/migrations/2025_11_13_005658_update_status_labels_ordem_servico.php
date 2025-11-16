<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Esta migration apenas documenta a atualização dos labels dos status.
     * Os valores numéricos nos registros NÃO são alterados.
     *
     * Mapeamento de status (os números permanecem os mesmos):
     * 0 => 'Aberta' (mesmo)
     * 1 => 'Enviada para Aprovação' (era 'Retrabalho')
     * 2 => 'Finalizada' (mesmo)
     * 3 => 'Contestada' (era 'Cancelada')
     * 4 => 'Aguardando RPS' (mesmo)
     * 5 => 'Faturada' (mesmo)
     * 6 => 'Aguardando RPS' (mesmo)
     */
    public function up(): void
    {
        // Nenhuma alteração de dados necessária
        // Os labels são atualizados apenas no código da aplicação
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nenhuma ação necessária no rollback
    }
};
