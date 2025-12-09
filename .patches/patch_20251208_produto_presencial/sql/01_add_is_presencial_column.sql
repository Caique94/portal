-- Script para adicionar coluna is_presencial na tabela produto
-- Database: portal
-- Data: 2025-12-08

-- Verificar se a coluna já existe antes de criar
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_name = 'produto'
        AND column_name = 'is_presencial'
    ) THEN
        ALTER TABLE produto ADD COLUMN is_presencial BOOLEAN DEFAULT FALSE;
        RAISE NOTICE 'Coluna is_presencial adicionada com sucesso';
    ELSE
        RAISE NOTICE 'Coluna is_presencial já existe';
    END IF;
END $$;

-- Verificar a criação
SELECT column_name, data_type, column_default
FROM information_schema.columns
WHERE table_name = 'produto'
AND column_name = 'is_presencial';
