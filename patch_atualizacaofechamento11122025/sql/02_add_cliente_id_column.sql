-- ============================================================================
-- Script: 02_add_cliente_id_column.sql
-- Descrição: Adiciona coluna cliente_id à tabela relatorio_fechamento
--            com foreign key para a tabela cliente
-- Data: 11/12/2025
-- ============================================================================

-- Verificar se a coluna já existe
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_name = 'relatorio_fechamento'
          AND column_name = 'cliente_id'
    ) THEN
        -- Adicionar coluna cliente_id
        ALTER TABLE relatorio_fechamento
        ADD COLUMN cliente_id BIGINT NULL;

        -- Criar foreign key
        ALTER TABLE relatorio_fechamento
        ADD CONSTRAINT relatorio_fechamento_cliente_id_foreign
        FOREIGN KEY (cliente_id)
        REFERENCES cliente(id)
        ON DELETE CASCADE;

        -- Criar índice para melhor performance
        CREATE INDEX relatorio_fechamento_cliente_id_index
        ON relatorio_fechamento(cliente_id);

        RAISE NOTICE 'Coluna cliente_id adicionada com sucesso!';
    ELSE
        RAISE NOTICE 'Coluna cliente_id já existe. Nenhuma alteração necessária.';
    END IF;
END $$;

-- Verificar alteração
SELECT
    column_name,
    data_type,
    is_nullable
FROM information_schema.columns
WHERE table_name = 'relatorio_fechamento'
  AND column_name = 'cliente_id';

-- Verificar foreign key
SELECT
    constraint_name,
    table_name,
    column_name
FROM information_schema.key_column_usage
WHERE constraint_name = 'relatorio_fechamento_cliente_id_foreign';

-- Resultado esperado:
-- - Coluna cliente_id existe e é NULLABLE
-- - Foreign key criada
