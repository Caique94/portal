<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to ordem_servico for common filter queries
        Schema::table('ordem_servico', function (Blueprint $table) {
            try {
                $table->index('consultor_id');
            } catch (\Exception $e) {
                // Index might already exist
            }

            try {
                $table->index('cliente_id');
            } catch (\Exception $e) {
                // Index might already exist
            }

            try {
                $table->index('status');
            } catch (\Exception $e) {
                // Index might already exist
            }

            try {
                $table->index('created_at');
            } catch (\Exception $e) {
                // Index might already exist
            }
        });

        // Add index to pagamento_parcelas for common queries
        Schema::table('pagamento_parcelas', function (Blueprint $table) {
            try {
                $table->index('recibo_provisorio_id');
            } catch (\Exception $e) {
                // Index might already exist
            }

            try {
                $table->index('status');
            } catch (\Exception $e) {
                // Index might already exist
            }

            try {
                $table->index('data_vencimento');
            } catch (\Exception $e) {
                // Index might already exist
            }
        });

        // Add index to recibo_provisorio for client queries
        Schema::table('recibo_provisorio', function (Blueprint $table) {
            try {
                if (Schema::hasColumn('recibo_provisorio', 'cliente_id')) {
                    $table->index('cliente_id');
                }
            } catch (\Exception $e) {
                // Index might already exist
            }
        });

        // Add index to contato for client relationship queries
        Schema::table('contato', function (Blueprint $table) {
            try {
                if (Schema::hasColumn('contato', 'cliente_id')) {
                    $table->index('cliente_id');
                }
            } catch (\Exception $e) {
                // Index might already exist
            }
        });

        // Add indexes to produto_tabela for price table queries
        Schema::table('produto_tabela', function (Blueprint $table) {
            try {
                $table->index('tabela_preco_id');
            } catch (\Exception $e) {
                // Index might already exist
            }

            try {
                $table->index('produto_id');
            } catch (\Exception $e) {
                // Index might already exist
            }
        });

        // Add index to users for consultant queries
        Schema::table('users', function (Blueprint $table) {
            try {
                if (Schema::hasColumn('users', 'papel')) {
                    $table->index('papel');
                }
            } catch (\Exception $e) {
                // Index might already exist
            }
        });

        // Note: Data type conversions should be done separately
        // to avoid transaction conflicts in some databases
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes (with error handling for non-existent indexes)
        Schema::table('ordem_servico', function (Blueprint $table) {
            try {
                $table->dropIndex(['consultor_id']);
            } catch (\Exception $e) {
            }
            try {
                $table->dropIndex(['cliente_id']);
            } catch (\Exception $e) {
            }
            try {
                $table->dropIndex(['status']);
            } catch (\Exception $e) {
            }
            try {
                $table->dropIndex(['created_at']);
            } catch (\Exception $e) {
            }
        });

        Schema::table('pagamento_parcelas', function (Blueprint $table) {
            try {
                $table->dropIndex(['recibo_provisorio_id']);
            } catch (\Exception $e) {
            }
            try {
                $table->dropIndex(['status']);
            } catch (\Exception $e) {
            }
            try {
                $table->dropIndex(['data_vencimento']);
            } catch (\Exception $e) {
            }
        });

        Schema::table('contato', function (Blueprint $table) {
            try {
                $table->dropIndex(['cliente_id']);
            } catch (\Exception $e) {
            }
        });

        Schema::table('produto_tabela', function (Blueprint $table) {
            try {
                $table->dropIndex(['tabela_preco_id']);
            } catch (\Exception $e) {
            }
            try {
                $table->dropIndex(['produto_id']);
            } catch (\Exception $e) {
            }
        });

        Schema::table('users', function (Blueprint $table) {
            try {
                $table->dropIndex(['papel']);
            } catch (\Exception $e) {
            }
        });
    }
};
