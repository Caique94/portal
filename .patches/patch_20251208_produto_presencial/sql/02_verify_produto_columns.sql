-- Script para verificar todas as colunas da tabela produto
-- Database: portal
-- Data: 2025-12-08

-- Listar todas as colunas da tabela produto
SELECT
    column_name,
    data_type,
    is_nullable,
    column_default
FROM information_schema.columns
WHERE table_name = 'produto'
ORDER BY ordinal_position;

-- Verificar especificamente a coluna is_presencial
SELECT
    CASE
        WHEN EXISTS (
            SELECT 1
            FROM information_schema.columns
            WHERE table_name = 'produto'
            AND column_name = 'is_presencial'
        ) THEN 'Coluna is_presencial EXISTE'
        ELSE 'Coluna is_presencial NÃO EXISTE'
    END AS status;

-- Listar produtos com status presencial
SELECT
    id,
    codigo,
    COALESCE(nome, descricao) as descricao,
    is_presencial,
    ativo
FROM produto
ORDER BY codigo
LIMIT 10;
