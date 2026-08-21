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

        // 4. Correção do Questionário Técnico
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

        // 6. Análise da IA
        $analiseIa = $geminiService->analisarCurriculo($textoCurriculo, $contextoAnalise);

        // Extração dos dados processados pela IA
        $nivelSugerido = $analiseIa['nivel_sugerido_ia'] ?? 'tecnico';
        $notaMatch     = $analiseIa['nota_match'] ?? 70;
        $resumoIa      = $analiseIa['resumo_ia'] ?? "Parecer Técnico: O candidato obteve {$acertos}/6 acertos no teste técnico.\n• Pontos Fortes: Base conceitual em eletroeletrônica.\n• Lacunas Técnicas: Necessita aprofundamento prático em programação de CLP.\n• Recomendação: Candidato promissor para validação humana.";
        $linksEstudo   = $analiseIa['recomendacoes_links'] ?? "Aprimore seus conhecimentos em: https://www.sp.senai.br";

        // Define a Área de Interesse automaticamente (Pega o nome da vaga vinculada ou deixa a IA/padrão direcionar)
        $areaDirecionadaIa = $vaga->titulo ?? $vaga->area ?? 'Automação Industrial';

        // 7. Salva no Banco de Dados
        Candidatura::create([
            'user_id'                => Auth::id(),
            'vaga_id'                => $vagaId,
            'area_interesse'         => $areaDirecionadaIa,
            'caminho_pdf'            => $caminhoPdf,
            'texto_extraido'         => $textoCurriculo,
            'respostas_questionario' => $respostas,
            'nota_match'             => $notaMatch,
            'nivel_sugerido_ia'      => $nivelSugerido,
            'resumo_ia'              => $resumoIa,
            'trilha_links'           => json_encode(['recomendacao_ia' => $linksEstudo]),
            'status'                 => 'aguardando_retorno',
        ]);

        return redirect()->route('candidaturas.index')
            ->with('sucesso', 'Sua candidatura e teste técnico foram enviados com sucesso! Acompanhe o status do processo abaixo.');
    }
}
