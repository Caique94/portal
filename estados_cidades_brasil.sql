-- Script para criar e popular tabelas de estados e cidades do Brasil
-- Execute este script no pgAdmin

-- =====================================================
-- CRIAR TABELA ESTADOS
-- =====================================================
CREATE TABLE IF NOT EXISTS estados (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    sigla VARCHAR(2) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- =====================================================
-- CRIAR TABELA CIDADES
-- =====================================================
CREATE TABLE IF NOT EXISTS cidades (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    estado_id INTEGER NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (estado_id) REFERENCES estados(id) ON DELETE CASCADE
);

-- Criar índice para melhorar performance nas buscas
CREATE INDEX IF NOT EXISTS idx_cidades_estado_id ON cidades(estado_id);
CREATE INDEX IF NOT EXISTS idx_cidades_nome ON cidades(nome);

-- =====================================================
-- POPULAR TABELA ESTADOS (27 estados brasileiros)
-- =====================================================
INSERT INTO estados (nome, sigla, created_at, updated_at) VALUES
('Acre', 'AC', NOW(), NOW()),
('Alagoas', 'AL', NOW(), NOW()),
('Amapá', 'AP', NOW(), NOW()),
('Amazonas', 'AM', NOW(), NOW()),
('Bahia', 'BA', NOW(), NOW()),
('Ceará', 'CE', NOW(), NOW()),
('Distrito Federal', 'DF', NOW(), NOW()),
('Espírito Santo', 'ES', NOW(), NOW()),
('Goiás', 'GO', NOW(), NOW()),
('Maranhão', 'MA', NOW(), NOW()),
('Mato Grosso', 'MT', NOW(), NOW()),
('Mato Grosso do Sul', 'MS', NOW(), NOW()),
('Minas Gerais', 'MG', NOW(), NOW()),
('Pará', 'PA', NOW(), NOW()),
('Paraíba', 'PB', NOW(), NOW()),
('Paraná', 'PR', NOW(), NOW()),
('Pernambuco', 'PE', NOW(), NOW()),
('Piauí', 'PI', NOW(), NOW()),
('Rio de Janeiro', 'RJ', NOW(), NOW()),
('Rio Grande do Norte', 'RN', NOW(), NOW()),
('Rio Grande do Sul', 'RS', NOW(), NOW()),
('Rondônia', 'RO', NOW(), NOW()),
('Roraima', 'RR', NOW(), NOW()),
('Santa Catarina', 'SC', NOW(), NOW()),
('São Paulo', 'SP', NOW(), NOW()),
('Sergipe', 'SE', NOW(), NOW()),
('Tocantins', 'TO', NOW(), NOW())
ON CONFLICT (sigla) DO NOTHING;

-- =====================================================
-- POPULAR TABELA CIDADES (principais cidades brasileiras)
-- =====================================================

-- ACRE (AC)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Rio Branco', id, NOW(), NOW() FROM estados WHERE sigla = 'AC'
UNION ALL SELECT 'Cruzeiro do Sul', id, NOW(), NOW() FROM estados WHERE sigla = 'AC'
UNION ALL SELECT 'Sena Madureira', id, NOW(), NOW() FROM estados WHERE sigla = 'AC'
UNION ALL SELECT 'Tarauacá', id, NOW(), NOW() FROM estados WHERE sigla = 'AC'
UNION ALL SELECT 'Feijó', id, NOW(), NOW() FROM estados WHERE sigla = 'AC';

-- ALAGOAS (AL)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Maceió', id, NOW(), NOW() FROM estados WHERE sigla = 'AL'
UNION ALL SELECT 'Arapiraca', id, NOW(), NOW() FROM estados WHERE sigla = 'AL'
UNION ALL SELECT 'Palmeira dos Índios', id, NOW(), NOW() FROM estados WHERE sigla = 'AL'
UNION ALL SELECT 'Rio Largo', id, NOW(), NOW() FROM estados WHERE sigla = 'AL'
UNION ALL SELECT 'Penedo', id, NOW(), NOW() FROM estados WHERE sigla = 'AL'
UNION ALL SELECT 'União dos Palmares', id, NOW(), NOW() FROM estados WHERE sigla = 'AL';

-- AMAPÁ (AP)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Macapá', id, NOW(), NOW() FROM estados WHERE sigla = 'AP'
UNION ALL SELECT 'Santana', id, NOW(), NOW() FROM estados WHERE sigla = 'AP'
UNION ALL SELECT 'Laranjal do Jari', id, NOW(), NOW() FROM estados WHERE sigla = 'AP'
UNION ALL SELECT 'Oiapoque', id, NOW(), NOW() FROM estados WHERE sigla = 'AP';

-- AMAZONAS (AM)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Manaus', id, NOW(), NOW() FROM estados WHERE sigla = 'AM'
UNION ALL SELECT 'Parintins', id, NOW(), NOW() FROM estados WHERE sigla = 'AM'
UNION ALL SELECT 'Itacoatiara', id, NOW(), NOW() FROM estados WHERE sigla = 'AM'
UNION ALL SELECT 'Manacapuru', id, NOW(), NOW() FROM estados WHERE sigla = 'AM'
UNION ALL SELECT 'Coari', id, NOW(), NOW() FROM estados WHERE sigla = 'AM'
UNION ALL SELECT 'Tefé', id, NOW(), NOW() FROM estados WHERE sigla = 'AM';

-- BAHIA (BA)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Salvador', id, NOW(), NOW() FROM estados WHERE sigla = 'BA'
UNION ALL SELECT 'Feira de Santana', id, NOW(), NOW() FROM estados WHERE sigla = 'BA'
UNION ALL SELECT 'Vitória da Conquista', id, NOW(), NOW() FROM estados WHERE sigla = 'BA'
UNION ALL SELECT 'Camaçari', id, NOW(), NOW() FROM estados WHERE sigla = 'BA'
UNION ALL SELECT 'Itabuna', id, NOW(), NOW() FROM estados WHERE sigla = 'BA'
UNION ALL SELECT 'Juazeiro', id, NOW(), NOW() FROM estados WHERE sigla = 'BA'
UNION ALL SELECT 'Lauro de Freitas', id, NOW(), NOW() FROM estados WHERE sigla = 'BA'
UNION ALL SELECT 'Ilhéus', id, NOW(), NOW() FROM estados WHERE sigla = 'BA'
UNION ALL SELECT 'Jequié', id, NOW(), NOW() FROM estados WHERE sigla = 'BA'
UNION ALL SELECT 'Alagoinhas', id, NOW(), NOW() FROM estados WHERE sigla = 'BA'
UNION ALL SELECT 'Porto Seguro', id, NOW(), NOW() FROM estados WHERE sigla = 'BA';

-- CEARÁ (CE)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Fortaleza', id, NOW(), NOW() FROM estados WHERE sigla = 'CE'
UNION ALL SELECT 'Caucaia', id, NOW(), NOW() FROM estados WHERE sigla = 'CE'
UNION ALL SELECT 'Juazeiro do Norte', id, NOW(), NOW() FROM estados WHERE sigla = 'CE'
UNION ALL SELECT 'Maracanaú', id, NOW(), NOW() FROM estados WHERE sigla = 'CE'
UNION ALL SELECT 'Sobral', id, NOW(), NOW() FROM estados WHERE sigla = 'CE'
UNION ALL SELECT 'Crato', id, NOW(), NOW() FROM estados WHERE sigla = 'CE'
UNION ALL SELECT 'Itapipoca', id, NOW(), NOW() FROM estados WHERE sigla = 'CE'
UNION ALL SELECT 'Maranguape', id, NOW(), NOW() FROM estados WHERE sigla = 'CE';

-- DISTRITO FEDERAL (DF)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Brasília', id, NOW(), NOW() FROM estados WHERE sigla = 'DF';

-- ESPÍRITO SANTO (ES)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Vitória', id, NOW(), NOW() FROM estados WHERE sigla = 'ES'
UNION ALL SELECT 'Vila Velha', id, NOW(), NOW() FROM estados WHERE sigla = 'ES'
UNION ALL SELECT 'Serra', id, NOW(), NOW() FROM estados WHERE sigla = 'ES'
UNION ALL SELECT 'Cariacica', id, NOW(), NOW() FROM estados WHERE sigla = 'ES'
UNION ALL SELECT 'Cachoeiro de Itapemirim', id, NOW(), NOW() FROM estados WHERE sigla = 'ES'
UNION ALL SELECT 'Linhares', id, NOW(), NOW() FROM estados WHERE sigla = 'ES'
UNION ALL SELECT 'São Mateus', id, NOW(), NOW() FROM estados WHERE sigla = 'ES'
UNION ALL SELECT 'Colatina', id, NOW(), NOW() FROM estados WHERE sigla = 'ES';

-- GOIÁS (GO)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Goiânia', id, NOW(), NOW() FROM estados WHERE sigla = 'GO'
UNION ALL SELECT 'Aparecida de Goiânia', id, NOW(), NOW() FROM estados WHERE sigla = 'GO'
UNION ALL SELECT 'Anápolis', id, NOW(), NOW() FROM estados WHERE sigla = 'GO'
UNION ALL SELECT 'Rio Verde', id, NOW(), NOW() FROM estados WHERE sigla = 'GO'
UNION ALL SELECT 'Luziânia', id, NOW(), NOW() FROM estados WHERE sigla = 'GO'
UNION ALL SELECT 'Águas Lindas de Goiás', id, NOW(), NOW() FROM estados WHERE sigla = 'GO'
UNION ALL SELECT 'Valparaíso de Goiás', id, NOW(), NOW() FROM estados WHERE sigla = 'GO'
UNION ALL SELECT 'Trindade', id, NOW(), NOW() FROM estados WHERE sigla = 'GO';

-- MARANHÃO (MA)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'São Luís', id, NOW(), NOW() FROM estados WHERE sigla = 'MA'
UNION ALL SELECT 'Imperatriz', id, NOW(), NOW() FROM estados WHERE sigla = 'MA'
UNION ALL SELECT 'São José de Ribamar', id, NOW(), NOW() FROM estados WHERE sigla = 'MA'
UNION ALL SELECT 'Timon', id, NOW(), NOW() FROM estados WHERE sigla = 'MA'
UNION ALL SELECT 'Caxias', id, NOW(), NOW() FROM estados WHERE sigla = 'MA'
UNION ALL SELECT 'Codó', id, NOW(), NOW() FROM estados WHERE sigla = 'MA'
UNION ALL SELECT 'Paço do Lumiar', id, NOW(), NOW() FROM estados WHERE sigla = 'MA';

-- MATO GROSSO (MT)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Cuiabá', id, NOW(), NOW() FROM estados WHERE sigla = 'MT'
UNION ALL SELECT 'Várzea Grande', id, NOW(), NOW() FROM estados WHERE sigla = 'MT'
UNION ALL SELECT 'Rondonópolis', id, NOW(), NOW() FROM estados WHERE sigla = 'MT'
UNION ALL SELECT 'Sinop', id, NOW(), NOW() FROM estados WHERE sigla = 'MT'
UNION ALL SELECT 'Tangará da Serra', id, NOW(), NOW() FROM estados WHERE sigla = 'MT'
UNION ALL SELECT 'Cáceres', id, NOW(), NOW() FROM estados WHERE sigla = 'MT';

-- MATO GROSSO DO SUL (MS)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Campo Grande', id, NOW(), NOW() FROM estados WHERE sigla = 'MS'
UNION ALL SELECT 'Dourados', id, NOW(), NOW() FROM estados WHERE sigla = 'MS'
UNION ALL SELECT 'Três Lagoas', id, NOW(), NOW() FROM estados WHERE sigla = 'MS'
UNION ALL SELECT 'Corumbá', id, NOW(), NOW() FROM estados WHERE sigla = 'MS'
UNION ALL SELECT 'Ponta Porã', id, NOW(), NOW() FROM estados WHERE sigla = 'MS'
UNION ALL SELECT 'Aquidauana', id, NOW(), NOW() FROM estados WHERE sigla = 'MS';

-- MINAS GERAIS (MG)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Belo Horizonte', id, NOW(), NOW() FROM estados WHERE sigla = 'MG'
UNION ALL SELECT 'Uberlândia', id, NOW(), NOW() FROM estados WHERE sigla = 'MG'
UNION ALL SELECT 'Contagem', id, NOW(), NOW() FROM estados WHERE sigla = 'MG'
UNION ALL SELECT 'Juiz de Fora', id, NOW(), NOW() FROM estados WHERE sigla = 'MG'
UNION ALL SELECT 'Betim', id, NOW(), NOW() FROM estados WHERE sigla = 'MG'
UNION ALL SELECT 'Montes Claros', id, NOW(), NOW() FROM estados WHERE sigla = 'MG'
UNION ALL SELECT 'Ribeirão das Neves', id, NOW(), NOW() FROM estados WHERE sigla = 'MG'
UNION ALL SELECT 'Uberaba', id, NOW(), NOW() FROM estados WHERE sigla = 'MG'
UNION ALL SELECT 'Governador Valadares', id, NOW(), NOW() FROM estados WHERE sigla = 'MG'
UNION ALL SELECT 'Ipatinga', id, NOW(), NOW() FROM estados WHERE sigla = 'MG'
UNION ALL SELECT 'Sete Lagoas', id, NOW(), NOW() FROM estados WHERE sigla = 'MG'
UNION ALL SELECT 'Divinópolis', id, NOW(), NOW() FROM estados WHERE sigla = 'MG'
UNION ALL SELECT 'Santa Luzia', id, NOW(), NOW() FROM estados WHERE sigla = 'MG';

-- PARÁ (PA)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Belém', id, NOW(), NOW() FROM estados WHERE sigla = 'PA'
UNION ALL SELECT 'Ananindeua', id, NOW(), NOW() FROM estados WHERE sigla = 'PA'
UNION ALL SELECT 'Santarém', id, NOW(), NOW() FROM estados WHERE sigla = 'PA'
UNION ALL SELECT 'Marabá', id, NOW(), NOW() FROM estados WHERE sigla = 'PA'
UNION ALL SELECT 'Parauapebas', id, NOW(), NOW() FROM estados WHERE sigla = 'PA'
UNION ALL SELECT 'Castanhal', id, NOW(), NOW() FROM estados WHERE sigla = 'PA'
UNION ALL SELECT 'Abaetetuba', id, NOW(), NOW() FROM estados WHERE sigla = 'PA';

-- PARAÍBA (PB)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'João Pessoa', id, NOW(), NOW() FROM estados WHERE sigla = 'PB'
UNION ALL SELECT 'Campina Grande', id, NOW(), NOW() FROM estados WHERE sigla = 'PB'
UNION ALL SELECT 'Santa Rita', id, NOW(), NOW() FROM estados WHERE sigla = 'PB'
UNION ALL SELECT 'Patos', id, NOW(), NOW() FROM estados WHERE sigla = 'PB'
UNION ALL SELECT 'Bayeux', id, NOW(), NOW() FROM estados WHERE sigla = 'PB'
UNION ALL SELECT 'Sousa', id, NOW(), NOW() FROM estados WHERE sigla = 'PB';

-- PARANÁ (PR)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Curitiba', id, NOW(), NOW() FROM estados WHERE sigla = 'PR'
UNION ALL SELECT 'Londrina', id, NOW(), NOW() FROM estados WHERE sigla = 'PR'
UNION ALL SELECT 'Maringá', id, NOW(), NOW() FROM estados WHERE sigla = 'PR'
UNION ALL SELECT 'Ponta Grossa', id, NOW(), NOW() FROM estados WHERE sigla = 'PR'
UNION ALL SELECT 'Cascavel', id, NOW(), NOW() FROM estados WHERE sigla = 'PR'
UNION ALL SELECT 'São José dos Pinhais', id, NOW(), NOW() FROM estados WHERE sigla = 'PR'
UNION ALL SELECT 'Foz do Iguaçu', id, NOW(), NOW() FROM estados WHERE sigla = 'PR'
UNION ALL SELECT 'Colombo', id, NOW(), NOW() FROM estados WHERE sigla = 'PR'
UNION ALL SELECT 'Guarapuava', id, NOW(), NOW() FROM estados WHERE sigla = 'PR'
UNION ALL SELECT 'Paranaguá', id, NOW(), NOW() FROM estados WHERE sigla = 'PR';

-- PERNAMBUCO (PE)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Recife', id, NOW(), NOW() FROM estados WHERE sigla = 'PE'
UNION ALL SELECT 'Jaboatão dos Guararapes', id, NOW(), NOW() FROM estados WHERE sigla = 'PE'
UNION ALL SELECT 'Olinda', id, NOW(), NOW() FROM estados WHERE sigla = 'PE'
UNION ALL SELECT 'Caruaru', id, NOW(), NOW() FROM estados WHERE sigla = 'PE'
UNION ALL SELECT 'Petrolina', id, NOW(), NOW() FROM estados WHERE sigla = 'PE'
UNION ALL SELECT 'Paulista', id, NOW(), NOW() FROM estados WHERE sigla = 'PE'
UNION ALL SELECT 'Cabo de Santo Agostinho', id, NOW(), NOW() FROM estados WHERE sigla = 'PE'
UNION ALL SELECT 'Camaragibe', id, NOW(), NOW() FROM estados WHERE sigla = 'PE'
UNION ALL SELECT 'Garanhuns', id, NOW(), NOW() FROM estados WHERE sigla = 'PE';

-- PIAUÍ (PI)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Teresina', id, NOW(), NOW() FROM estados WHERE sigla = 'PI'
UNION ALL SELECT 'Parnaíba', id, NOW(), NOW() FROM estados WHERE sigla = 'PI'
UNION ALL SELECT 'Picos', id, NOW(), NOW() FROM estados WHERE sigla = 'PI'
UNION ALL SELECT 'Floriano', id, NOW(), NOW() FROM estados WHERE sigla = 'PI'
UNION ALL SELECT 'Piripiri', id, NOW(), NOW() FROM estados WHERE sigla = 'PI';

-- RIO DE JANEIRO (RJ)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Rio de Janeiro', id, NOW(), NOW() FROM estados WHERE sigla = 'RJ'
UNION ALL SELECT 'São Gonçalo', id, NOW(), NOW() FROM estados WHERE sigla = 'RJ'
UNION ALL SELECT 'Duque de Caxias', id, NOW(), NOW() FROM estados WHERE sigla = 'RJ'
UNION ALL SELECT 'Nova Iguaçu', id, NOW(), NOW() FROM estados WHERE sigla = 'RJ'
UNION ALL SELECT 'Niterói', id, NOW(), NOW() FROM estados WHERE sigla = 'RJ'
UNION ALL SELECT 'Belford Roxo', id, NOW(), NOW() FROM estados WHERE sigla = 'RJ'
UNION ALL SELECT 'São João de Meriti', id, NOW(), NOW() FROM estados WHERE sigla = 'RJ'
UNION ALL SELECT 'Campos dos Goytacazes', id, NOW(), NOW() FROM estados WHERE sigla = 'RJ'
UNION ALL SELECT 'Petrópolis', id, NOW(), NOW() FROM estados WHERE sigla = 'RJ'
UNION ALL SELECT 'Volta Redonda', id, NOW(), NOW() FROM estados WHERE sigla = 'RJ'
UNION ALL SELECT 'Magé', id, NOW(), NOW() FROM estados WHERE sigla = 'RJ'
UNION ALL SELECT 'Itaboraí', id, NOW(), NOW() FROM estados WHERE sigla = 'RJ';

-- RIO GRANDE DO NORTE (RN)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Natal', id, NOW(), NOW() FROM estados WHERE sigla = 'RN'
UNION ALL SELECT 'Mossoró', id, NOW(), NOW() FROM estados WHERE sigla = 'RN'
UNION ALL SELECT 'Parnamirim', id, NOW(), NOW() FROM estados WHERE sigla = 'RN'
UNION ALL SELECT 'São Gonçalo do Amarante', id, NOW(), NOW() FROM estados WHERE sigla = 'RN'
UNION ALL SELECT 'Macaíba', id, NOW(), NOW() FROM estados WHERE sigla = 'RN'
UNION ALL SELECT 'Ceará-Mirim', id, NOW(), NOW() FROM estados WHERE sigla = 'RN';

-- RIO GRANDE DO SUL (RS)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Porto Alegre', id, NOW(), NOW() FROM estados WHERE sigla = 'RS'
UNION ALL SELECT 'Caxias do Sul', id, NOW(), NOW() FROM estados WHERE sigla = 'RS'
UNION ALL SELECT 'Pelotas', id, NOW(), NOW() FROM estados WHERE sigla = 'RS'
UNION ALL SELECT 'Canoas', id, NOW(), NOW() FROM estados WHERE sigla = 'RS'
UNION ALL SELECT 'Santa Maria', id, NOW(), NOW() FROM estados WHERE sigla = 'RS'
UNION ALL SELECT 'Gravataí', id, NOW(), NOW() FROM estados WHERE sigla = 'RS'
UNION ALL SELECT 'Viamão', id, NOW(), NOW() FROM estados WHERE sigla = 'RS'
UNION ALL SELECT 'Novo Hamburgo', id, NOW(), NOW() FROM estados WHERE sigla = 'RS'
UNION ALL SELECT 'São Leopoldo', id, NOW(), NOW() FROM estados WHERE sigla = 'RS'
UNION ALL SELECT 'Rio Grande', id, NOW(), NOW() FROM estados WHERE sigla = 'RS'
UNION ALL SELECT 'Alvorada', id, NOW(), NOW() FROM estados WHERE sigla = 'RS'
UNION ALL SELECT 'Passo Fundo', id, NOW(), NOW() FROM estados WHERE sigla = 'RS';

-- RONDÔNIA (RO)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Porto Velho', id, NOW(), NOW() FROM estados WHERE sigla = 'RO'
UNION ALL SELECT 'Ji-Paraná', id, NOW(), NOW() FROM estados WHERE sigla = 'RO'
UNION ALL SELECT 'Ariquemes', id, NOW(), NOW() FROM estados WHERE sigla = 'RO'
UNION ALL SELECT 'Vilhena', id, NOW(), NOW() FROM estados WHERE sigla = 'RO'
UNION ALL SELECT 'Cacoal', id, NOW(), NOW() FROM estados WHERE sigla = 'RO';

-- RORAIMA (RR)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Boa Vista', id, NOW(), NOW() FROM estados WHERE sigla = 'RR'
UNION ALL SELECT 'Rorainópolis', id, NOW(), NOW() FROM estados WHERE sigla = 'RR'
UNION ALL SELECT 'Caracaraí', id, NOW(), NOW() FROM estados WHERE sigla = 'RR';

-- SANTA CATARINA (SC)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Florianópolis', id, NOW(), NOW() FROM estados WHERE sigla = 'SC'
UNION ALL SELECT 'Joinville', id, NOW(), NOW() FROM estados WHERE sigla = 'SC'
UNION ALL SELECT 'Blumenau', id, NOW(), NOW() FROM estados WHERE sigla = 'SC'
UNION ALL SELECT 'São José', id, NOW(), NOW() FROM estados WHERE sigla = 'SC'
UNION ALL SELECT 'Criciúma', id, NOW(), NOW() FROM estados WHERE sigla = 'SC'
UNION ALL SELECT 'Chapecó', id, NOW(), NOW() FROM estados WHERE sigla = 'SC'
UNION ALL SELECT 'Itajaí', id, NOW(), NOW() FROM estados WHERE sigla = 'SC'
UNION ALL SELECT 'Jaraguá do Sul', id, NOW(), NOW() FROM estados WHERE sigla = 'SC'
UNION ALL SELECT 'Lages', id, NOW(), NOW() FROM estados WHERE sigla = 'SC'
UNION ALL SELECT 'Palhoça', id, NOW(), NOW() FROM estados WHERE sigla = 'SC';

-- SÃO PAULO (SP)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'São Paulo', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'Guarulhos', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'Campinas', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'São Bernardo do Campo', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'Santo André', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'Osasco', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'São José dos Campos', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'Ribeirão Preto', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'Sorocaba', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'Santos', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'Mauá', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'São José do Rio Preto', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'Mogi das Cruzes', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'Diadema', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'Jundiaí', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'Carapicuíba', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'Piracicaba', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'Bauru', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'Itaquaquecetuba', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'São Vicente', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'Franca', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'Guarujá', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'Taubaté', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'Limeira', id, NOW(), NOW() FROM estados WHERE sigla = 'SP'
UNION ALL SELECT 'Suzano', id, NOW(), NOW() FROM estados WHERE sigla = 'SP';

-- SERGIPE (SE)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Aracaju', id, NOW(), NOW() FROM estados WHERE sigla = 'SE'
UNION ALL SELECT 'Nossa Senhora do Socorro', id, NOW(), NOW() FROM estados WHERE sigla = 'SE'
UNION ALL SELECT 'Lagarto', id, NOW(), NOW() FROM estados WHERE sigla = 'SE'
UNION ALL SELECT 'Itabaiana', id, NOW(), NOW() FROM estados WHERE sigla = 'SE'
UNION ALL SELECT 'São Cristóvão', id, NOW(), NOW() FROM estados WHERE sigla = 'SE';

-- TOCANTINS (TO)
INSERT INTO cidades (nome, estado_id, created_at, updated_at)
SELECT 'Palmas', id, NOW(), NOW() FROM estados WHERE sigla = 'TO'
UNION ALL SELECT 'Araguaína', id, NOW(), NOW() FROM estados WHERE sigla = 'TO'
UNION ALL SELECT 'Gurupi', id, NOW(), NOW() FROM estados WHERE sigla = 'TO'
UNION ALL SELECT 'Porto Nacional', id, NOW(), NOW() FROM estados WHERE sigla = 'TO'
UNION ALL SELECT 'Paraíso do Tocantins', id, NOW(), NOW() FROM estados WHERE sigla = 'TO';

-- =====================================================
-- VERIFICAÇÃO FINAL
-- =====================================================
SELECT
    'Script executado com sucesso!' as status,
    (SELECT COUNT(*) FROM estados) as total_estados,
    (SELECT COUNT(*) FROM cidades) as total_cidades;
