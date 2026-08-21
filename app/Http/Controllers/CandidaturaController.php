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
        // 1. Trata a string JSON das respostas
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

        // 3. Upload do arquivo PDF do Currículo e Leitura de Texto
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

        // 4. Busca os Requisitos da Vaga (ou usa uma padrão se não houver ID)
        $vagaId = $request->input('vaga_id');
        $vaga = Vaga::find($vagaId);
        $requisitosVaga = $vaga->descricao_requisitos ?? 'Conhecimento em CLP, Automação Industrial, Inversores de Frequência, Redes Industriais e Elétrica.';

        // 5. Chamada real à Inteligência Artificial (Gemini)
        $analiseIa = $geminiService->analisarCurriculo($textoCurriculo, $requisitosVaga);

        // Fallbacks padrão caso a API do Gemini apresente oscilação
        $nivelSugerido = $analiseIa['nivel_sugerido_ia'] ?? 'tecnico';
        $notaMatch     = $analiseIa['nota_match'] ?? 70;
        $resumoIa      = $analiseIa['resumo_ia'] ?? 'Análise concluída com base nos requisitos da vaga.';
        $linksEstudo   = $analiseIa['recomendacoes_links'] ?? 'Recomendamos manter os estudos atualizados em plataformas como https://www.sp.senai.br';

        // 6. Salvar a Candidatura preenchendo a coluna trilha_links existente na sua migration
        $areaInteresse = $request->input('area_interesse', 'Automação Industrial / Eletroeletrônica');

        Candidatura::create([
            'user_id'                => Auth::id(),
            'vaga_id'                => $vagaId,
            'area_interesse'         => $areaInteresse,
            'caminho_pdf'            => $caminhoPdf,
            'texto_extraido'         => $textoCurriculo,
            'respostas_questionario' => $request->input('respostas_questionario', []),
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
