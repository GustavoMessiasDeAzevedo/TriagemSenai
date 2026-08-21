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

        // Lista de Fallbacks caso a API não devolva o JSON formatado
        $fallbackCursos = implode("\n", [
            "• Cursos SENAI SP: https://www.sp.senai.br/cursos",
            "• NR-10 (Segurança Elétrica): https://www.sp.senai.br/curso/nr-10-seguranca-em-instalacoes-e-servicos-com-eletricidade/75949",
            "• NR-33 (Espaços Confinados): https://www.sp.senai.br/curso/nr-33-seguranca-e-saude-nos-trabalhos-em-espacos-confinados-para-trabalhadores-autorizados-e-vigias/89204",
            "• NR-35 (Trabalho em Altura): https://www.sp.senai.br/curso/nr-35-trabalho-em-altura/75951",
            "• AutoCAD (SENAI Play Gratuito): https://play.senai.br/curso/9fa43863-34b9-11f0-8b99-96b9b09bc812",
            "• AutoCAD 2D (SENAI SP): https://www.sp.senai.br/curso/autocad-2d/110124",
            "• Revit (SESI SENAI): https://cursos.sesisenai.org.br/cursos-profissionalizantes/autodesk-revit/3888",
            "• IA AI-900 (Microsoft Learn): https://learn.microsoft.com/pt-br/credentials/certifications/exams/ai-900/",
            "• CCNA Redes (Cisco Skills for All): https://skillsforall.com/",
            "• Técnico em Redes (SENAI): https://www.sp.senai.br/curso/tecnico-em-redes-de-computadores/94541",
            "• Power BI (Microsoft Learn): https://learn.microsoft.com/pt-br/power-bi/",
            "• Power BI (SENAI SP): https://www.sp.senai.br/curso/desvendando-o-power-bi/96677",
            "• Autodesk Fusion (Licença Estudante): https://www.autodesk.com/education/edu-software/overview",
            "• Eletricista de Redes (SENAI SP): https://www.sp.senai.br/curso/eletricista-de-redes-de-distribuicao-de-energia-eletrica/88165",
            "• Engenharia Elétrica (UNIMAR): https://oficial.unimar.br/cursos/engenharia-eletrica/",
            "• Ciência da Computação (UNIMAR): https://oficial.unimar.br/cursos/ciencia-da-computacao/",
            "• Engenharia de Software (UNIMAR EAD): https://ead.unimar.br/cursos/engenharia-de-software/"
        ]);

        $fallbackPortais = implode("\n", [
            "• LinkedIn: https://www.linkedin.com",
            "• Vagas.com: https://www.vagas.com.br",
            "• Catho: https://www.catho.com.br"
        ]);

        // Trata os links vindo do Gemini ou usa os fallbacks
        $linksCursos  = $analiseIa['recomendacoes_links']['cursos'] ?? $fallbackCursos;
        $linksPortais = $analiseIa['recomendacoes_links']['portais_curriculo'] ?? $fallbackPortais;

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
