<?php

namespace App\Http\Controllers;

use App\Models\Candidatura;
use App\Models\Vaga;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class CandidaturaController extends Controller
{
    public function index()
    {
        $candidaturas = Candidatura::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('candidaturas.index', compact('candidaturas'));
    }

public function store(Request $request, GeminiService $geminiService)
    {
        // 1. Trata a string JSON das respostas do questionário
        if ($request->has('respostas') && is_string($request->input('respostas'))) {
            $request->merge([
                'respostas_questionario' => json_decode($request->input('respostas'), true) ?? []
            ]);
        }

        // 2. Validação dos campos
        $request->validate([
            'curriculo' => 'required|mimes:pdf|max:10240',
            'respostas_questionario' => 'nullable|array',
        ]);

        // 3. Upload e leitura de texto do PDF
        $caminhoPdf = null;
        $textoCurriculo = '';

        if ($request->hasFile('curriculo')) {
            $caminhoPdf = $request->file('curriculo')->store('curriculos', 'public');
            $caminhoAbsoluto = storage_path('app/public/' . $caminhoPdf);

            try {
                if (file_exists($caminhoAbsoluto)) {
                    $parser = new Parser();
                    $pdf = $parser->parseFile($caminhoAbsoluto);
                    $textoCurriculo = $pdf->getText();
                }
            } catch (\Exception $e) {
                Log::error('Erro ao ler PDF: ' . $e->getMessage());
            }
        }

        // 4. Correção do Questionário Técnico (Gabarito)
        $respostas = $request->input('respostas_questionario', []);
        $gabarito = [1 => 'B', 2 => 'D', 3 => 'B', 4 => 'A', 5 => 'B', 6 => 'B'];
        $acertos = 0;
        foreach ($gabarito as $id => $correta) {
            if (isset($respostas[$id]) && str_starts_with(strtoupper(trim($respostas[$id])), $correta)) {
                $acertos++;
            }
        }

        // 5. Busca Vaga para contextualizar o Gemini
        $vagaId = $request->input('vaga_id');
        $vaga = Vaga::find($vagaId);
        $requisitosVaga = $vaga->descricao_requisitos ?? 'Conhecimento em CLP, Automação Industrial, Inversores de Frequência, Redes Industriais e Elétrica.';
        
        $contextoAnalise = $requisitosVaga . "\n\n[Resultado do Teste Técnico do Candidato: {$acertos}/6 acertos]";

        // 6. Chamada de Análise da IA Gemini
        $analiseIa = $geminiService->analisarCurriculo($textoCurriculo, $contextoAnalise);

        // Extração dos dados processados pela IA
        $nivelSugerido = $analiseIa['nivel_sugerido_ia'] ?? 'tecnico';
        $notaMatch     = $analiseIa['nota_match'] ?? 70;

        // Parecer exclusivo para o RH / Recrutador
        $resumoIa = $analiseIa['resumo_ia'] ?? "PARECER TÉCNICO EXCLUSIVO AO RH: O candidato obteve {$acertos}/6 acertos no teste técnico.\n• Pontos Fortes: Base conceitual em eletroeletrônica e formação inicial.\n• Lacunas Técnicas: Necessita aprofundamento prático em programação de CLP e NR-10.\n• Recomendação: Candidato promissor para validação humana.";

        // Mensagem direta e amigável ao Candidato
        $orientacaoCandidato = $analiseIa['orientacao_candidato'] ?? "Identificamos que você possui uma boa base conceitual! Para fortalecer seu perfil técnico e avançar na carreira, recomendamos focar no aprimoramento de CLP, comandos elétricos e certificações regulamentares.";

        // Trata os links dinâmicos filtrados pelo Gemini (2 a 4 cursos e portais completos)
        $linksCursos  = $analiseIa['recomendacoes_links']['cursos'] ?? "• Cursos SENAI SP: https://www.sp.senai.br/cursos\n• NR-10 (Segurança Elétrica): https://www.sp.senai.br/curso/nr-10-seguranca-em-instalacoes-e-servicos-com-eletricidade/75949";
        $linksPortais = $analiseIa['recomendacoes_links']['portais_curriculo'] ?? "• LinkedIn: https://www.linkedin.com\n• Vagas.com: https://www.vagas.com.br\n• Catho: https://www.catho.com.br";

        // Define a Área de Interesse com base na Vaga vinculada
        $areaDirecionada = $vaga->titulo ?? $vaga->area ?? 'Automação Industrial / Eletroeletrônica';

        // 7. Salva a Candidatura no Banco de Dados
        Candidatura::create([
            'user_id'                => Auth::id(),
            'vaga_id'                => $vagaId,
            'area_interesse'         => $areaDirecionada,
            'caminho_pdf'            => $caminhoPdf,
            'texto_extraido'         => $textoCurriculo,
            'respostas_questionario' => $respostas,
            'nota_match'             => $notaMatch,
            'nivel_sugerido_ia'      => $nivelSugerido,
            'resumo_ia'              => $resumoIa,
            'trilha_links'           => json_encode([
                'orientacao' => $orientacaoCandidato,
                'cursos'     => $linksCursos,
                'portais'    => $linksPortais,
            ]),
            'status'                 => 'aguardando_retorno',
        ]);

        return redirect()->route('candidaturas.index')
            ->with('sucesso', 'Sua candidatura e teste técnico foram enviados com sucesso! Acompanhe o status do processo abaixo.');
    }
}
