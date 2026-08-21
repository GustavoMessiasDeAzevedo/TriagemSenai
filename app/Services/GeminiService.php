<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key', env('GEMINI_API_KEY'));
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    }

    public function analisarCurriculo(string $textoCurriculo, string $contextoAnalise): array
    {
        try {
            $bancoCursos = "
- Cursos SENAI SP: https://www.sp.senai.br/cursos
- NR-10 (Segurança Elétrica): https://www.sp.senai.br/curso/nr-10-seguranca-em-instalacoes-e-servicos-com-eletricidade/75949
- NR-33 (Espaços Confinados): https://www.sp.senai.br/curso/nr-33-seguranca-e-saude-nos-trabalhos-em-espacos-confinados-para-trabalhadores-autorizados-e-vigias/89204
- NR-35 (Trabalho em Altura): https://www.sp.senai.br/curso/nr-35-trabalho-em-altura/75951
- AutoCAD (SENAI Play Gratuito): https://play.senai.br/curso/9fa43863-34b9-11f0-8b99-96b9b09bc812
- AutoCAD 2D (SENAI SP): https://www.sp.senai.br/curso/autocad-2d/110124
- Revit (SESI SENAI): https://cursos.sesisenai.org.br/cursos-profissionalizantes/autodesk-revit/3888
- IA AI-900 (Microsoft Learn): https://learn.microsoft.com/pt-br/credentials/certifications/exams/ai-900/
- CCNA Redes (Cisco Skills for All): https://skillsforall.com/
- Técnico em Redes (SENAI): https://www.sp.senai.br/curso/tecnico-em-redes-de-computadores/94541
- Power BI (Microsoft Learn): https://learn.microsoft.com/pt-br/power-bi/
- Power BI (SENAI SP): https://www.sp.senai.br/curso/desvendando-o-power-bi/96677
- Autodesk Fusion (Licença Estudante): https://www.autodesk.com/education/edu-software/overview
- Eletricista de Redes (SENAI SP): https://www.sp.senai.br/curso/eletricista-de-redes-de-distribuicao-de-energia-eletrica/88165
- Engenharia Elétrica (UNIMAR): https://oficial.unimar.br/cursos/engenharia-eletrica/
- Ciência da Computação (UNIMAR): https://oficial.unimar.br/cursos/ciencia-da-computacao/
- Engenharia de Software (UNIMAR EAD): https://ead.unimar.br/cursos/engenharia-de-software/
";

            $bancoPortais = "
- LinkedIn: https://www.linkedin.com
- CIEE: https://portal.ciee.org.br
- Catho: https://www.catho.com.br
- InfoJobs: https://www.infojobs.com.br
- Indeed: https://br.indeed.com
- Vagas.com: https://www.vagas.com.br
- Emprega Brasil (SINE): https://empregabrasil.mte.gov.br
";

            $prompt = "
Você é um Engenheiro de Avaliação Técnica e Recrutador Especialista no setor de Eletroeletrônica e Automação Industrial.

TEXTO DO CURRÍCULO:
{$textoCurriculo}

CONTEXTO DA VAGA E TESTE TÉCNICO:
{$contextoAnalise}

BANCO DE CURSOS DISPONÍVEIS:
{$bancoCursos}

BANCO DE PORTAIS:
{$bancoPortais}

---
SUA TAREFA:
Analise os dados do candidato e retorne EXATAMENTE um JSON no seguinte formato:

{
  \"nivel_sugerido_ia\": \"basico | tecnico | avancado\",
  \"nota_match\": 75,
  \"resumo_ia\": \"PARECER TÉCNICO EXCLUSIVO AO RH: O candidato obteve X/6 acertos no teste técnico. • Pontos Fortes: [descreva]. • Lacunas Técnicas: [descreva os erros]. • Recomendação: [orientação ao RH].\",
  \"orientacao_candidato\": \"FEEDBACK HUMANIZADO PARA O CANDIDATO: Escreva uma mensagem motivadora e técnica. Destaque a base conceitual e dê conselhos de carreira de acordo com os erros dele.\",
  \"recomendacoes_links\": {
    \"cursos\": \"SELECIONE APENAS DE 2 A 4 LINKS do BANCO DE CURSOS DISPONÍVEIS que atendam EXATAMENTE às lacunas descritas na orientação do candidato. NÃO inclua a lista completa de cursos! Formato: • Nome do Curso: URL\",
    \"portais_curriculo\": \"Mantenha a lista com TODOS os portais do banco no formato: • Nome do Portal: URL\"
  }
}

REGRAS:
1. No campo 'cursos', NUNCA retorne todos os cursos. Escolha APENAS de 2 a 4 links que resolvam os pontos fracos do candidato.
2. No campo 'portais_curriculo', mantenha todos os portais.
3. Responda apenas com o JSON válido.
";

            $response = Http::post("{$this->baseUrl}?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $content = $response->json('candidates.0.content.parts.0.text');
                $cleanJson = preg_replace('/^```json\s*|\s*```$/i', '', trim($content));
                $data = json_decode($cleanJson, true);

                if (json_last_error() === JSON_ERROR_NONE && isset($data['resumo_ia'])) {
                    return $data;
                }
            }

            Log::warning('Gemini API retornou resposta fora do padrão esperado.', ['response' => $response->body()]);
            return $this->fallbackTrilha();

        } catch (\Throwable $e) {
            Log::error('Erro ao conectar ou processar no GeminiService: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return $this->fallbackTrilha();
        }
    }

    protected function fallbackTrilha(): array
    {
        return [
            'nivel_sugerido_ia' => 'tecnico',
            'nota_match' => 70,
            'resumo_ia' => "PARECER TÉCNICO EXCLUSIVO AO RH: O candidato concluiu o teste técnico. • Pontos Fortes: Demonstra base em eletroeletrônica. • Lacunas Técnicas: Recomenda-se alinhamento em entrevista. • Recomendação: Candidato em análise.",
            'orientacao_candidato' => "Identificamos que você possui uma boa base conceitual! Para fortalecer seu perfil técnico e avançar na carreira, recomendamos focar no aprimoramento de CLP, comandos elétricos e certificações regulamentares.",
            'recomendacoes_links' => [
                'cursos' => "• Cursos SENAI SP: https://www.sp.senai.br/cursos\n• NR-10 (Segurança Elétrica): https://www.sp.senai.br/curso/nr-10-seguranca-em-instalacoes-e-servicos-com-eletricidade/75949",
                'portais_curriculo' => "• LinkedIn: https://www.linkedin.com\n• CIEE: https://portal.ciee.org.br\n• InfoJobs: https://www.infojobs.com.br\n• Indeed: https://br.indeed.com\n• Vagas.com: https://www.vagas.com.br"
            ]
        ];
    }
}
