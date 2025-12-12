-- ============================================================================
-- Script: 01_alter_consultor_id_nullable.sql
-- Descrição: Torna a coluna consultor_id NULLABLE para permitir fechamentos
--            de cliente sem consultor específico
-- Data: 11/12/2025
-- ============================================================================

-- Tornar consultor_id nullable
ALTER TABLE relatorio_fechamento
ALTER COLUMN consultor_id DROP NOT NULL;

-- Verificar alteração
SELECT
    column_name,
    data_type,
    is_nullable
FROM information_schema.columns
WHERE table_name = 'relatorio_fechamento'
  AND column_name = 'consultor_id';

-- Resultado esperado: is_nullable = 'YES'
