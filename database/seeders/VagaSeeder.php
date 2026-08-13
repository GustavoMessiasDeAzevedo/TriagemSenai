<?php

namespace Database\Seeders;

use App\Models\Vaga;
use Illuminate\Database\Seeder;

class VagaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. VAGA BÁSICO (Auxiliar de Eletricista)
        Vaga::create([
            'titulo' => 'Auxiliar de Eletricista',
            'nivel' => 'basico',
            'descricao_requisitos' => '
OBJETIVO DO CARGO:
Apoiar o eletricista responsável na execução de serviços elétricos, seguindo normas de segurança, organizando materiais e ferramentas, e realizando tarefas básicas de instalação e manutenção.

PRINCIPAIS ATIVIDADES:
- Auxiliar na instalação de fiação, cabos por eletrodutos, tomadas, interruptores, disjuntores e quadros de distribuição.
- Realizar medições simples com multímetro sob orientação.
- Organizar, transportar e preparar ferramentas, materiais e EPIs para os serviços.
- Auxiliar em manutenções preventivas e corretivas de baixa complexidade.
- Seguir rigorosamente as normas de segurança (NR-10, NR-35).
- Realizar limpeza e organização do local de trabalho (canteiro/obra).

REQUISITOS OBRIGATÓRIOS:
- Ensino Fundamental completo.
- Curso profissionalizante básico na área elétrica (ex: SENAI).
- Conhecimento e aplicação de normas de segurança do trabalho (NR-10).
- Idade mínima de 18 anos.
- Domínio de ferramentas básicas (alicate, chave de fenda/phillips, teste de tensão).
- Identificação de componentes elétricos básicos e noções de instalações residenciais/prediais.
- Leitura básica de plantas e diagramas elétricos.
            ',
            'ativa' => true,
        ]);

        // 2. VAGA TÉCNICO (Eletrotécnico / Téc. de Manutenção Elétrica)
        Vaga::create([
            'titulo' => 'Técnico em Eletrotécnica / Manutenção Elétrica',
            'nivel' => 'tecnico',
            'descricao_requisitos' => '
OBJETIVO DO CARGO:
Executar e supervisionar manutenções elétricas preventivas, preditivas e corretivas em sistemas industriais e prediais, atendendo ordens de serviço (OS) e garantindo a eficiência dos equipamentos.

PRINCIPAIS ATIVIDADES:
- Execução e controle de Ordens de Serviço (OS) de manutenção preventiva e corretiva.
- Diagnóstico e reparo em comandos elétricos, quadros de força e sistemas automatizados.
- Atuação em rotinas de manutenção incluindo trabalho em altura (NR-35), espaços confinados (NR-33) e escalas de plantão/trabalho noturno conforme demanda.
- Leitura e interpretação avançada de esquemas e diagramas elétricos industriais.

REQUISITOS OBRIGATÓRIOS:
- Curso Técnico completo em Eletrotécnica, Eletromecânica, Automação Industrial ou áreas correlatas.
- Certificações de Segurança OBRIGATÓRIAS e atualizadas: NR-10, NR-35 (Trabalho em Altura) e NR-33 (Espaço Confinado).
- Experiência prática comprovada na execução e priorização de Ordens de Serviço.
- Conhecimento em comandos elétricos, painéis e noções de sistemas de eficiência energética e CLPs.
            ',
            'ativa' => true,
        ]);

        // 3. VAGA AVANÇADO (Engenheiro / Especialista de Automação)
        Vaga::create([
            'titulo' => 'Especialista / Programador de Automação Industrial',
            'nivel' => 'avancado',
            'descricao_requisitos' => '
REQUISITOS OBRIGATÓRIOS (HARD SKILLS):
- Linguagens de Programação: Domínio avançado das linguagens IEC 61131-3, com ênfase em Texto Estruturado (ST), Ladder (LAD) e Diagrama de Blocos Funcionais (FBD).
- Plataformas de CLP: Siemens (S7-1200 / S7-1500 / TIA Portal / SCL) e Rockwell/Allen-Bradley (ControlLogix / CompactLogix / Studio 5000).
- Redes e Protocolos Industriais: Profinet, Profibus DP/PA, Modbus TCP/RTU, EtherNet/IP e OPC UA.
- Supervisórios e IHMs: Desenvolvimentos em WinCC, FactoryTalk View ou Ignition.
- Normas de Segurança: Conhecimento prático/teórico em NR-12 e ISO 13849 (paradas de emergência, cortinas de luz, relés e CLPs de segurança).

DIFERENCIAIS (REQUISITOS DESEJÁVEIS):
- Robótica (KUKA, ABB, FANUC ou Yaskawa).
- Indústria 4.0 e IIoT (MQTT, REST APIs, Node-RED, SQL).
- Comissionamento de acionamentos (SINAMICS, PowerFlex) e controle de processos (malhas PID).

FORMAÇÃO E EXPERIÊNCIA:
- Ensino Superior completo em Eng. de Automação, Elétrica, Mecatrônica (ou Técnico completo com forte bagagem).
- Mínimo de 3 a 5 anos de experiência em programação de CLPs em ambiente industrial.
- Inglês intermediário/avançado.
- Disponibilidade para viagens de comissionamento de campo.
            ',
            'ativa' => true,
        ]);
    }
}
