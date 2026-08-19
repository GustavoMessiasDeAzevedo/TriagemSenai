<?php

namespace App\Http\Controllers;

use App\Models\Candidatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                    $textoCurriculo = mb_strtolower($pdf->getText());
                }
            } catch (\Exception $e) {
                \Log::error('Erro ao ler PDF: ' . $e->getMessage());
            }
        }

        // 4. Avaliação do Questionário Técnico (Peso: 50% da nota)
        $respostas = $request->input('respostas_questionario', []);

        $gabaritoLetras = [
            1 => 'B',
            2 => 'D',
            3 => 'B',
            4 => 'A',
            5 => 'B',
            6 => 'B',
        ];

        $acertos = 0;
        $totalQuestoes = count($gabaritoLetras);

        foreach ($gabaritoLetras as $idQuestao => $letraCorreta) {
            if (isset($respostas[$idQuestao])) {
                $respostaEnviada = strtoupper(trim($respostas[$idQuestao]));
                if (str_starts_with($respostaEnviada, $letraCorreta)) {
                    $acertos++;
                }
            }
        }

        $notaQuestionario = ($totalQuestoes > 0) ? ($acertos / $totalQuestoes) * 100 : 0;

        // 5. Avaliação Dinâmica do Currículo por Vaga (Peso: 50% da nota)
        $vagaId = $request->input('vaga_id');
        $vaga = \App\Models\Vaga::find($vagaId);

        $notaCurriculo = 0;

        if ($vaga && !empty($textoCurriculo)) {
            $requisitosVaga = mb_strtolower($vaga->descricao_requisitos ?? '');
            
            preg_match_all('/\b[a-z0-9\-]{3,}\b/u', $requisitosVaga, $matches);
            $palavrasRequisitos = array_unique($matches[0]);

            $encontradas = 0;
            foreach ($palavrasRequisitos as $palavra) {
                if (str_contains($textoCurriculo, $palavra)) {
                    $encontradas++;
                }
            }

            $totalTermos = max(1, count($palavrasRequisitos));
            $notaCurriculo = min(100, round(($encontradas / $totalTermos) * 100));
        } else {
            $notaCurriculo = $notaQuestionario;
        }


        $notaMatch = round(($notaQuestionario * 0.5) + ($notaCurriculo * 0.5));

        // 6. Definição do Nível Sugerido e Parecer da IA
        if ($notaMatch >= 80) {
            $nivelSugerido = 'avancado';
            $parecerIa = "Excelente alinhamento técnico ({$notaMatch}% de match). O candidato obteve {$acertos}/{$totalQuestoes} acertos no teste e o currículo apresentou forte domínio nas tecnologias exigidas.";
        } elseif ($notaMatch >= 50) {
            $nivelSugerido = 'tecnico';
            $parecerIa = "Desempenho mediano ({$notaMatch}% de match). Apresenta base conceitual sólida, com {$acertos}/{$totalQuestoes} acertos no teste, atendo parcialmente aos requisitos no currículo.";
        } else {
            $nivelSugerido = 'basico';
            $parecerIa = "Desempenho inicial ({$notaMatch}% de match). O candidato obteve {$acertos}/{$totalQuestoes} acertos e pouca correspondência com as palavras-chave exigidas na vaga.";
        }

        // 7. Salvar a Candidatura
        $areaInteresse = $request->input('area_interesse', 'Automação Industrial / Eletroeletrônica');

        Candidatura::create([
            'user_id' => Auth::id(),
            'area_interesse' => $areaInteresse,
            'caminho_pdf' => $caminhoPdf,
            'respostas_questionario' => $respostas,
            'nota_match' => $notaMatch,
            'nivel_sugerido_ia' => $nivelSugerido,
            'resumo_ia' => $parecerIa,
            'status' => 'aguardando_retorno',
        ]);

        return redirect()->route('candidaturas.index')
            ->with('sucesso', 'Sua candidatura e teste técnico foram enviados com sucesso! Acompanhe o status do processo abaixo.');
    }
}
