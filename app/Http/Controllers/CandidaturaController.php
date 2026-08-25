<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessarAnaliseCurriculo;
use App\Models\Candidatura;
use App\Models\Vaga;
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

    public function store(Request $request)
    {
        // 1. Trata a string JSON das respostas do questionário
        if ($request->has('respostas') && is_string($request->input('respostas'))) {
            $request->merge([
                'respostas_questionario' => json_decode($request->input('respostas'), true) ?? [],
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
            $caminhoAbsoluto = storage_path('app/public/'.$caminhoPdf);

            try {
                if (file_exists($caminhoAbsoluto)) {
                    $parser = new Parser;
                    $pdf = $parser->parseFile($caminhoAbsoluto);
                    $textoCurriculo = $pdf->getText();
                }
            } catch (\Exception $e) {
                Log::error('Erro ao ler PDF: '.$e->getMessage());
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

        // 5. Busca Vaga para contextualizar a IA
        $vagaId = $request->input('vaga_id');
        $vaga = Vaga::find($vagaId);
        $requisitosVaga = $vaga->descricao_requisitos ?? 'Conhecimento em CLP, Automação Industrial, Inversores de Frequência, Redes Industriais e Elétrica.';

        $contextoAnalise = $requisitosVaga."\n\n[Resultado do Teste Técnico do Candidato: {$acertos}/6 acertos]";

        // Define a Área de Interesse
        $areaDirecionada = $vaga->titulo ?? $vaga->area ?? 'Automação Industrial / Eletroeletrônica';

        // 6. Salva a Candidatura IMEDIATAMENTE no banco (com status de Análise Pendente/Processando)
        $candidatura = Candidatura::create([
            'user_id' => Auth::id(),
            'vaga_id' => $vagaId,
            'area_interesse' => $areaDirecionada,
            'caminho_pdf' => $caminhoPdf,
            'texto_extraido' => $textoCurriculo,
            'respostas_questionario' => $respostas,
            'nota_match' => 0, // Será preenchido pela Fila
            'nivel_sugerido_ia' => 'basico',
            'resumo_ia' => "PARECER TÉCNICO EXCLUSIVO AO RH: O candidato obteve {$acertos}/6 acertos no teste técnico. A IA está analisando o currículo...",
            'trilha_links' => json_encode([
                'orientacao' => 'Sua candidatura foi recebida e a análise detalhada por IA está em andamento. Atualize a página em instantes.',
                'cursos' => '',
                'portais' => '',
            ]),
            'status' => 'aguardando_retorno', // Status para o frontend saber que está rodando
        ]);

        // 7. Dispara a Fila em Segundo Plano (Background)
        ProcessarAnaliseCurriculo::dispatch($candidatura, $textoCurriculo, $contextoAnalise);

        // 8. Redirecionamento INSTANTÂNEO (mata a tela preta e o duplo envio por F5)
        return redirect()->route('candidaturas.index')
            ->with('sucesso', 'Seu currículo e teste técnico foram enviados! Estamos processando as informações.');
    }
}
