<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadosCidadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dados dos estados e cidades brasileiros
        $dados = [
            ['sigla' => 'AC', 'nome' => 'Acre', 'cidades' => ['Rio Branco', 'Cruzeiro do Sul', 'Mâncio Lima']],
            ['sigla' => 'AL', 'nome' => 'Alagoas', 'cidades' => ['Maceió', 'Arapiraca', 'Rio Largo', 'Marechal Deodoro']],
            ['sigla' => 'AP', 'nome' => 'Amapá', 'cidades' => ['Macapá', 'Santana', 'Laranjal do Jari']],
            ['sigla' => 'AM', 'nome' => 'Amazonas', 'cidades' => ['Manaus', 'Parintins', 'Itacoatiara', 'Coari']],
            ['sigla' => 'BA', 'nome' => 'Bahia', 'cidades' => ['Salvador', 'Feira de Santana', 'Vitória da Conquista', 'Camaçari', 'Ilhéus', 'Jequié', 'Barreiras', 'Santo Estêvão']],
            ['sigla' => 'CE', 'nome' => 'Ceará', 'cidades' => ['Fortaleza', 'Caucaia', 'Juazeiro do Norte', 'Maracanaú', 'Sobral', 'Iguatu', 'Aquiraz']],
            ['sigla' => 'DF', 'nome' => 'Distrito Federal', 'cidades' => ['Brasília', 'Taguatinga', 'Sobradinho', 'Planaltina']],
            ['sigla' => 'ES', 'nome' => 'Espírito Santo', 'cidades' => ['Vitória', 'Vila Velha', 'Cariacica', 'Serra', 'Linhares', 'São Mateus']],
            ['sigla' => 'GO', 'nome' => 'Goiás', 'cidades' => ['Goiânia', 'Aparecida de Goiânia', 'Anápolis', 'Rio Verde', 'Luziânia', 'Trindade']],
            ['sigla' => 'MA', 'nome' => 'Maranhão', 'cidades' => ['São Luís', 'Imperatriz', 'Timon', 'Caxias', 'Codó']],
            ['sigla' => 'MT', 'nome' => 'Mato Grosso', 'cidades' => ['Cuiabá', 'Várzea Grande', 'Rondonópolis', 'Sinop', 'Cáceres']],
            ['sigla' => 'MS', 'nome' => 'Mato Grosso do Sul', 'cidades' => ['Campo Grande', 'Dourados', 'Três Lagoas', 'Corumbá', 'Ponta Porã']],
            ['sigla' => 'MG', 'nome' => 'Minas Gerais', 'cidades' => ['Belo Horizonte', 'Uberlândia', 'Contagem', 'Juiz de Fora', 'Betim', 'Montes Claros', 'Ribeirão das Neves', 'Divinópolis', 'Sete Lagoas', 'Governador Valadares']],
            ['sigla' => 'PA', 'nome' => 'Pará', 'cidades' => ['Belém', 'Ananindeua', 'Santarém', 'Marabá', 'Parauapebas', 'Castanhal']],
            ['sigla' => 'PB', 'nome' => 'Paraíba', 'cidades' => ['João Pessoa', 'Campina Grande', 'Patos', 'Sousa', 'Guarabira']],
            ['sigla' => 'PR', 'nome' => 'Paraná', 'cidades' => ['Curitiba', 'Londrina', 'Maringá', 'Ponta Grossa', 'Cascavel', 'Foz do Iguaçu', 'Colônia', 'Apucarana']],
            ['sigla' => 'PE', 'nome' => 'Pernambuco', 'cidades' => ['Recife', 'Jaboatão dos Guararapes', 'Olinda', 'Caruaru', 'Petrolina', 'Paulista']],
            ['sigla' => 'PI', 'nome' => 'Piauí', 'cidades' => ['Teresina', 'Parnaíba', 'Picos', 'Campo Maior', 'Oeiras']],
            ['sigla' => 'RJ', 'nome' => 'Rio de Janeiro', 'cidades' => ['Rio de Janeiro', 'Niterói', 'Duque de Caxias', 'Nova Iguaçu', 'São Gonçalo', 'São João de Meriti', 'Belford Roxo', 'Magé']],
            ['sigla' => 'RN', 'nome' => 'Rio Grande do Norte', 'cidades' => ['Natal', 'Mossoró', 'Parnamirim', 'Macaíba', 'Ceará-Mirim']],
            ['sigla' => 'RS', 'nome' => 'Rio Grande do Sul', 'cidades' => ['Porto Alegre', 'Caxias do Sul', 'Pelotas', 'Santa Maria', 'Santo Ângelo', 'Novo Hamburgo', 'São Leopoldo', 'Gravataí']],
            ['sigla' => 'RO', 'nome' => 'Rondônia', 'cidades' => ['Porto Velho', 'Ariquemes', 'Vilhena', 'Cacoal', 'Jaru']],
            ['sigla' => 'RR', 'nome' => 'Roraima', 'cidades' => ['Boa Vista', 'Rorainópolis', 'Caracaraí']],
            ['sigla' => 'SC', 'nome' => 'Santa Catarina', 'cidades' => ['Florianópolis', 'Joinville', 'Blumenau', 'Itajaí', 'Brusque', 'Chapecó', 'Criciúma']],
            ['sigla' => 'SP', 'nome' => 'São Paulo', 'cidades' => ['São Paulo', 'Guarulhos', 'Campinas', 'São Bernardo do Campo', 'Santo André', 'Osasco', 'Mauá', 'Sorocaba', 'Jundiaí', 'Piracicaba', 'Ribeirão Preto', 'Santos', 'São José do Rio Preto']],
            ['sigla' => 'SE', 'nome' => 'Sergipe', 'cidades' => ['Aracaju', 'Nossa Senhora do Socorro', 'Lagarto', 'São Cristóvão']],
            ['sigla' => 'TO', 'nome' => 'Tocantins', 'cidades' => ['Palmas', 'Araguaína', 'Gurupi', 'Porto Nacional']],
        ];

        // Inserir estados e cidades
        foreach ($dados as $estadoData) {
            $estado = \App\Models\Estado::create([
                'nome' => $estadoData['nome'],
                'sigla' => $estadoData['sigla'],
            ]);

            foreach ($estadoData['cidades'] as $nomeCidade) {
                \App\Models\Cidade::create([
                    'estado_id' => $estado->id,
                    'nome' => $nomeCidade,
                ]);
            }
        }
    }
}
