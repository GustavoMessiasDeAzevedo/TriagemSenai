<?php

namespace App\Http\Controllers;

use App\Models\Candidatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidaturaController extends Controller
{
    /**
     * Exibe a tela de candidatura do candidato.
     */
    public function index()
    {
        // Corrigida a consulta para não passar parâmetro excedente
        $candidaturas = Candidatura::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('candidaturas.index', compact('candidaturas'));
    }
    /**
     * Processa o envio do teste técnico e do currículo em PDF.
     */
   public function store(Request $request)
    {
        // 1. Trata a string JSON do respostas para virar array ANTES de validar
        if ($request->has('respostas') && is_string($request->input('respostas'))) {
            $request->merge([
                'respostas_questionario' => json_decode($request->input('respostas'), true) ?? []
            ]);
        }

        // 2. Validação dos campos
        $request->validate([
            'curriculo' => 'required|mimes:pdf|max:10240', // PDF até 10MB
            'respostas_questionario' => 'required|array',
        ]);

        // 3. Upload do arquivo PDF do Currículo
        $caminhoPdf = null;
        if ($request->hasFile('curriculo')) {
            $caminhoPdf = $request->file('curriculo')->store('curriculos', 'public');
        }

        // 4. Processamento das respostas do Questionário
        $respostas = $request->input('respostas_questionario', []);

        // Gabarito oficial das 6 questões
        $gabarito = [
            1 => 'B) 20 A | Disjuntor de 25 A',
            2 => 'D) Condutor Neutro: Azul Claro | Queda de Tensão: 10 V',
            3 => 'B) Sobre o resistor R2',
            4 => 'A) Pd = 4,0 W — O dissipador é insuficiente e haverá colapso por sobretemperatura.',
            5 => 'B) MTBF = 105 min | Disponibilidade = 87,5%',
            6 => 'B) Redução da corrente total no circuito de alimentação, aliviando condutores e transformadores.',
        ];

        $acertos = 0;
        $totalQuestoes = count($gabarito);

        foreach ($gabarito as $idQuestao => $respostaCorreta) {
            if (isset($respostas[$idQuestao]) && $respostas[$idQuestao] === $respostaCorreta) {
                $acertos++;
            }
        }

        // Cálculo da Nota de Match (0 a 100%)
        $notaMatch = round(($acertos / $totalQuestoes) * 100);

        // 5. Definição do Nível Sugerido e Parecer do "Engenheiro Mentor" (IA)
        if ($notaMatch >= 80) {
            $nivelSugerido = 'avancado';
            $parecerIa = "Excelente desempenho técnico ({$acertos}/{$totalQuestoes} acertos - {$notaMatch}%). O candidato demonstrou domínio avançado em dimensionamento elétrico, análise de semicondutores, indicadores de manutenção (MTBF) e qualidade de energia. Recomendado para posições de Analista ou Técnico Especialista.";
        } elseif ($notaMatch >= 50) {
            $nivelSugerido = 'tecnico';
            $parecerIa = "Desempenho técnico mediano ({$acertos}/{$totalQuestoes} acertos - {$notaMatch}%). Possui boa base conceitual em instalações e circuitos, porém apresentou incertezas em tópicos avançados de eletrônica/manutenção. Recomendado para posições Nível Técnico Pleno/Júnior.";
        } else {
            $nivelSugerido = 'basico';
            $parecerIa = "Desempenho técnico inicial ({$acertos}/{$totalQuestoes} acertos - {$notaMatch}%). Apresenta conhecimentos fundamentais, sendo necessário treinamento suplementar nas normas (NBR 5410) e práticas de bancada. Recomendado para posições de Auxiliar / Aprendiz.";
        }

        // 6. Tratamento da Área de Interesse
        $areaInteresse = $request->input('area_interesse');
        if (empty($areaInteresse)) {
            $areaInteresse = 'Eletroeletrônica Geral';
        }

        // 7. Salvar a Candidatura
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
