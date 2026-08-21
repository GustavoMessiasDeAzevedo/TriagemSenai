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
Sua missão é fazer a triagem técnica inicial deste currículo e enviar um parecer técnico estruturado para a nossa equipe de RH (nós). Nós faremos a validação humana final sobre as suas recomendações.

### DIRETRIZES DA ANÁLISE TÉCNICA:
- Como engenheiro, você sabe que é raríssimo um candidato preencher 100% dos requisitos de uma vaga de nível **Avançado**. 
- Busque por **potencial de crescimento**: se o candidato tem uma base sólida em lógica de programação, elétrica ou dominou plataformas correlatas (ex: Siemens, Rockwell, Schneider), avalie se ele é um talento promissor capaz de evoluir para o nível avançado a curto/médio prazo.
- Dê peso para formação acadêmica/técnica, projetos práticos e capacidade de rápida adaptação.
- **IMPORTANTE:** Caso identifique pontos a melhorar ou para evolução do candidato, inclua links reais de estudo, cursos gratuitos ou documentações (ex: https://www.sp.senai.br, https://www.coursera.org, https://www.automationdirect.com).

### VAGAS DISPONÍVEIS:
{$requisitosVaga}

### CURRÍCULO DO CANDIDATO:
{$textoCurriculo}

### INSTRUÇÕES DE RESPOSTA PARA O RH:
Responda EXCLUSIVAMENTE em formato JSON puro, sem blocos de código Markdown (não use ```json ... ```), seguindo estritamente a estrutura abaixo:
{
  \"nivel_sugerido_ia\": \"basico|tecnico|avancado\",
  \"nota_match\": 85,
  \"resumo_ia\": \"Parecer do Engenheiro ao RH: Destaque detalhadamente os pontos fortes técnicos, as lacunas/gargalos técnicos específicos identificados no currículo e a justificativa se vale a pena apostar neste talento para validação humana.\",
  \"recomendacoes_links\": \"Mensagem motivacional para o candidato detalhando as lacunas técnicas encontradas e indicando links reais de estudo, cursos e plataformas para ele se capacitar e atualizar o currículo (ex: Recomendamos estudar CLP no SENAI em https://www.sp.senai.br ou documentações na https://www.automationdirect.com).\"
}";

        try {
            $response = Http::post("{$this->baseUrl}?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
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

            Log::error('Erro API Gemini: '.$response->body());

            return null;
        } catch (\Exception $e) {
            Log::error('Exceção GeminiService: '.$e->getMessage());
            return null;
        }
    }
}
