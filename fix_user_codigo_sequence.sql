-- =====================================================
-- SCRIPT: Corrigir geração automática de código para usuários
-- =====================================================

-- 1. Criar ou atualizar a sequência na tabela sequences
INSERT INTO sequences (entity_type, current_number, prefix, min_digits, created_at, updated_at)
VALUES ('App\\Models\\User', 0, '', 4, NOW(), NOW())
ON CONFLICT (entity_type)
DO UPDATE SET updated_at = NOW();

-- 2. Atualizar a sequência com base no maior código existente
UPDATE sequences
SET current_number = (
    SELECT COALESCE(MAX(CAST(codigo AS INTEGER)), 0)
    FROM users
    WHERE codigo ~ '^[0-9]+$'
)
WHERE entity_type = 'App\\Models\\User';

-- 3. Atualizar usuários que não têm código (NULL)
DO $$
DECLARE
    next_code INTEGER;
    user_record RECORD;
BEGIN
    FOR user_record IN
        SELECT id FROM users WHERE codigo IS NULL ORDER BY id
    LOOP
        -- Buscar próximo número da sequência
        SELECT current_number + 1 INTO next_code
        FROM sequences
        WHERE entity_type = 'App\\Models\\User';

        -- Atualizar usuário com código formatado (ex: 0001, 0002, etc)
        UPDATE users
        SET codigo = LPAD(next_code::TEXT, 4, '0')
        WHERE id = user_record.id;

        -- Atualizar a sequência
        UPDATE sequences
        SET current_number = next_code,
            updated_at = NOW()
        WHERE entity_type = 'App\\Models\\User';
    END LOOP;

    RAISE NOTICE 'Códigos de usuários atualizados com sucesso!';
END $$;

-- 4. Criar função para gerar código automaticamente
CREATE OR REPLACE FUNCTION generate_user_codigo()
RETURNS TRIGGER AS $$
DECLARE
    next_code INTEGER;
BEGIN
    -- Se o código já foi fornecido, não fazer nada
    IF NEW.codigo IS NOT NULL AND NEW.codigo != '' THEN
        RETURN NEW;
    END IF;

    -- Bloquear a linha da sequência para evitar duplicatas
    SELECT current_number + 1 INTO next_code
    FROM sequences
    WHERE entity_type = 'App\\Models\\User'
    FOR UPDATE;

    -- Gerar código com 4 dígitos (ex: 0001, 0002)
    NEW.codigo := LPAD(next_code::TEXT, 4, '0');

    -- Atualizar a sequência
    UPDATE sequences
    SET current_number = next_code,
        updated_at = NOW()
    WHERE entity_type = 'App\\Models\\User';

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- 5. Criar trigger para executar a função antes de inserir novo usuário
DROP TRIGGER IF EXISTS trigger_generate_user_codigo ON users;
CREATE TRIGGER trigger_generate_user_codigo
    BEFORE INSERT ON users
    FOR EACH ROW
    EXECUTE FUNCTION generate_user_codigo();

-- =====================================================
-- VERIFICAÇÃO
-- =====================================================

-- Verificar sequência atual
SELECT entity_type, current_number, prefix, min_digits
FROM sequences
WHERE entity_type = 'App\\Models\\User';

-- Verificar usuários e seus códigos
SELECT id, name, email, codigo
FROM users
ORDER BY CAST(codigo AS INTEGER);

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================

DO $$
BEGIN
    RAISE NOTICE '========================================';
    RAISE NOTICE '✓ CONFIGURAÇÃO DE CÓDIGO AUTOMÁTICO CONCLUÍDA!';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Agora novos usuários receberão código automaticamente.';
    RAISE NOTICE 'Trigger criado: trigger_generate_user_codigo';
    RAISE NOTICE 'Função criada: generate_user_codigo()';
    RAISE NOTICE '========================================';
END $$;
