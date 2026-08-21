<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;

    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY', '');
    }

    public function analisarCurriculo(string $textoCurriculo, string $requisitosVaga): ?array
    {
        $prompt = "Você é um renomado Engenheiro Elétrico e especialista em automação industrial, com vasta experiência prática em campo e apaixonado por mentorar e identificar novos talentos na engenharia. 
Sua missão é fazer a triagem técnica inicial deste currículo e enviar um parecer técnico estruturado para a nossa equipe de RH. Nós faremos a validação humana final sobre as suas recomendações.

### DIRETRIZES DA ANÁLISE TÉCNICA:
- Avalie o potencial do candidato com base nos requisitos da vaga e do teste técnico.
- Dê peso para formação acadêmica/técnica, projetos práticos e capacidade de rápida adaptação.
- **IMPORTANTE:** Ao identificar lacunas de conhecimento do candidato, recomende EXCLUSIVAMENTE links da lista de referência abaixo que façam sentido para a necessidade dele.

### LISTA DE LINKS OFICIAIS DE REFERÊNCIA PARA CAPACITAÇÃO:
- SENAI SP Cursos Geral: https://www.sp.senai.br/cursos
- NR-10 (Segurança em Instalações Elétricas): https://www.sp.senai.br/curso/nr-10-seguranca-em-instalacoes-e-servicos-com-eletricidade/75949
- NR-33 (Espaços Confinados): https://www.sp.senai.br/curso/nr-33-seguranca-e-saude-nos-trabalhos-em-espacos-confinados-para-trabalhadores-autorizados-e-vigias/89204
- NR-35 (Trabalho em Altura): https://www.sp.senai.br/curso/nr-35-trabalho-em-altura/75951
- AutoCAD 2D (SENAI Play Gratuito): https://play.senai.br/curso/9fa43863-34b9-11f0-8b99-96b9b09bc812
- AutoCAD 2D (SENAI SP): https://www.sp.senai.br/curso/autocad-2d/110124
- Revit (SESI SENAI): https://cursos.sesisenai.org.br/cursos-profissionalizantes/autodesk-revit/3888
- IA - AI-900 (Microsoft Learn): https://learn.microsoft.com/pt-br/credentials/certifications/exams/ai-900/
- CCNA Redes (Cisco Skills for All Gratuito): https://skillsforall.com/
- Técnico em Redes (SENAI): https://www.sp.senai.br/curso/tecnico-em-redes-de-computadores/94541
- Power BI (Microsoft Learn Gratuito): https://learn.microsoft.com/pt-br/power-bi/
- Power BI (SENAI SP): https://www.sp.senai.br/curso/desvendando-o-power-bi/96677
- Autodesk Fusion (Autodesk Student): https://www.autodesk.com/education/edu-software/overview
- Eletricista de Redes (SENAI SP): https://www.sp.senai.br/curso/eletricista-de-redes-de-distribuicao-de-energia-eletrica/88165
- Graduação Engenharia Elétrica (UNIMAR): https://oficial.unimar.br/cursos/engenharia-eletrica/
- Graduação Ciência da Computação (UNIMAR): https://oficial.unimar.br/cursos/ciencia-da-computacao/
- Graduação Engenharia de Software (UNIMAR EAD): https://ead.unimar.br/cursos/engenharia-de-software/

### REQUISITOS DA VAGA & DESEMPENHO:
{$requisitosVaga}

### CURRÍCULO DO CANDIDATO:
{$textoCurriculo}

### INSTRUÇÕES DE RESPOSTA:
Responda EXCLUSIVAMENTE em formato JSON puro, sem formatação Markdown (não use ```json ... ```), seguindo estritamente a estrutura:
{
  \"nivel_sugerido_ia\": \"basico|tecnico|avancado\",
  \"nota_match\": 85,
  \"resumo_ia\": \"PARECER TÉCNICO EXCLUSIVO AO RH: Destaque os pontos fortes técnicos, as lacunas específicas identificadas e a justificativa para a validação humana.\",
  \"orientacao_candidato\": \"Mensagem motivacional direta ao candidato apresentando de forma amigável as lacunas técnicas identificadas no teste/currículo e encorajando seu desenvolvimento.\",
  \"recomendacoes_links\": {
      \"cursos\": \"Selecione da lista acima os links de cursos/capacitação mais adequados para cobrir as lacunas do candidato (ex: https://www.sp.senai.br/curso/nr-10... - NR-10 SENAI)\",
      \"portais_curriculo\": \"Links de portais para o candidato cadastrar seu currículo e buscar oportunidades (ex: https://www.linkedin.com | https://www.vagas.com.br | https://www.catho.com.br)\"
  }
}";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json'
                ]
            ]);

            if ($response->successful()) {
                $dados = $response->json();
                $textoResposta = $dados['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if ($textoResposta) {
                    $jsonLimpo = trim(str_replace(['```json', '```'], '', $textoResposta));
                    $resultadoDecodificado = json_decode($jsonLimpo, true);

                    if (is_array($resultadoDecodificado)) {
                        return $resultadoDecodificado;
                    }
                }
            }

            Log::error('Erro API Gemini: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Exceção GeminiService: ' . $e->getMessage());
            return null;
        }
        
    }
}
