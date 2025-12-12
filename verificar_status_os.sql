-- ================================================
-- VERIFICAR STATUS DAS ORDENS DE SERVIÇO
-- ================================================

-- 1. Contar quantos registros existem por status
SELECT
    status,
    COUNT(*) as total,
    CASE
        WHEN status = 0 THEN 'Em Aberto'
        WHEN status = 1 THEN 'Aguardando Aprovação'
        WHEN status = 2 THEN 'Aprovado'
        WHEN status = 3 THEN 'Contestada'
        WHEN status = 4 THEN 'Aguardando Faturamento'
        WHEN status = 5 THEN 'Faturada'
        WHEN status = 6 THEN 'Aguardando RPS'
        WHEN status = 7 THEN 'RPS Emitida'
        ELSE 'Desconhecido'
    END as nome_status
FROM ordem_servico
GROUP BY status
ORDER BY status;

-- 2. Listar todas as OS com status 7 (RPS Emitida)
SELECT
    id,
    status,
    cliente_id,
    consultor_id,
    valor_total,
    created_at
FROM ordem_servico
WHERE status = 7
ORDER BY created_at DESC
LIMIT 10;

-- 3. Verificar se há algum registro com status NULL
SELECT COUNT(*) as total_null
FROM ordem_servico
WHERE status IS NULL;

-- 4. Listar últimas 20 OS criadas com seus status
SELECT
    id,
    status,
    CASE
        WHEN status = 0 THEN 'Em Aberto'
        WHEN status = 1 THEN 'Aguardando Aprovação'
        WHEN status = 2 THEN 'Aprovado'
        WHEN status = 3 THEN 'Contestada'
        WHEN status = 4 THEN 'Aguardando Faturamento'
        WHEN status = 5 THEN 'Faturada'
        WHEN status = 6 THEN 'Aguardando RPS'
        WHEN status = 7 THEN 'RPS Emitida'
        ELSE 'Desconhecido'
    END as nome_status,
    created_at
FROM ordem_servico
ORDER BY created_at DESC
LIMIT 20;
