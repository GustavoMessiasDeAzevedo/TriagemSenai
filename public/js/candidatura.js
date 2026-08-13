function candidaturaFlow() {
    return {
        etapa: 0,
        abrirModalCurriculo: false,
        respostas: {},
        questaoList: [
            {
                id: 1,
                bloco: "Práticas de Instalações e Dimensionamento Básico",
                pergunta: "Em uma bancada de instalações prediais, é necessário dimensionar a proteção para um circuito monofásico de 220 V que alimentará uma carga com potência nominal de 4400 W. Qual é a corrente do circuito e o disjuntor termomagnético (padrão DIN) comercial imediatamente superior adequado?",
                opcoes: [
                    "A) 15 A | Disjuntor de 16 A",
                    "B) 20 A | Disjuntor de 25 A",
                    "C) 20 A | Disjuntor de 20 A",
                    "D) 22 A | Disjuntor de 32 A"
                ]
            },
            {
                id: 2,
                bloco: "Normas e Identificação de Circuitos (NBR 5410)",
                pergunta: "Ao alimentar uma tomada de uso específico em 127 V com carga de 1270 W (10 A), o multímetro indicou apenas 110 V devido à queda de tensão na fiação. De acordo com a ABNT NBR 5410, qual deve ser a cor do condutor neutro e qual foi o valor da queda de tensão verificada?",
                opcoes: [
                    "A) Condutor Neutro: Preto | Queda de Tensão: 10 V",
                    "B) Condutor Neutro: Azul Claro | Queda de Tensão: 17 V",
                    "C) Condutor Neutro: Verde | Queda de Tensão: 17 V",
                    "D) Condutor Neutro: Azul Claro | Queda de Tensão: 10 V"
                ]
            },
            {
                id: 3,
                bloco: "Eletrônica Analógica e Divisores de Tensão",
                pergunta: "Em um circuito CC alimentado por uma fonte de 24 V, há três resistores em série: R1 = 100 Ω, R2 = 300 Ω e R3 = 400 Ω. Um sensor necessita de exatamente 9 V para operar. Em qual resistor a medição com o multímetro fornecerá exatamente 9 V?",
                opcoes: [
                    "A) Sobre o resistor R1",
                    "B) Sobre o resistor R2",
                    "C) Sobre o resistor R3",
                    "D) Na associação em série de R1 + R2"
                ]
            },
            {
                id: 4,
                bloco: "Semicondutores de Potência e Disrupção Térmica",
                pergunta: "Em um teste de bancada de um conversor chaveado, um transistor BJT operando em saturação conduz uma corrente de coletor de 5 A com tensão de saturação VCE(sat) = 0,8 V. Se o dissipador do componente suporta no máximo 3,0 W de dissipação, qual é a potência dissipada (Pd) e o efeito no circuito?",
                opcoes: [
                    "A) Pd = 4,0 W — O dissipador é insuficiente e haverá colapso por sobretemperatura.",
                    "B) Pd = 2,5 W — O dissipador operará dentro do limite de segurança.",
                    "C) Pd = 1,6 W — O transistor operará com folga térmica."
                ]
            },
            {
                id: 5,
                bloco: "Gestão da Manutenção e Indicadores de Confiabilidade (MTBF)",
                pergunta: "Em uma célula automatizada de produção operando em um turno de 8 horas (480 minutos), ocorreram 4 paradas não planejadas por falha em sensores, totalizando 60 minutos de manutenção corretiva. Quais são, respectivamente, o MTBF (Tempo Médio Entre Falhas) e a Disponibilidade Operacional da célula?",
                opcoes: [
                    "A) MTBF = 120 min | Disponibilidade = 87,5%",
                    "B) MTBF = 105 min | Disponibilidade = 87,5%",
                    "C) MTBF = 105 min | Disponibilidade = 75,0%",
                    "D) MTBF = 90 min | Disponibilidade = 80,0%"
                ]
            },
            {
                id: 6,
                bloco: "Eficiência Energética e Qualidade de Energia",
                pergunta: "Um motor trifásico operando com baixo Fator de Potência (0,70 indutivo) gera alto consumo reativo. Ao instalar um banco de capacitores adequado, o Fator de Potência é elevado para 0,95. Qual é o principal impacto técnico e operacional dessa intervenção no sistema alimentador do motor?",
                opcoes: [
                    "A) Aumento do consumo de potência ativa (kW) e sobrecarga na rede elétrica.",
                    "B) Redução da corrente total no circuito de alimentação, aliviando condutores e transformadores.",
                    "C) Elevação do nível de harmônicos de corrente e queima imediata dos fusíveis de proteção.",
                    "D) Nenhuma alteração na corrente elétrica, apenas eliminação de taxas na fatura."
                ]
            }
        ],
        iniciarQuiz() {
            this.etapa = 1;
        },
        proximaEtapa() {
            if (this.etapa < 6) {
                this.etapa++;
            } else {
                this.abrirModalCurriculo = true;
            }
        }
    };
}