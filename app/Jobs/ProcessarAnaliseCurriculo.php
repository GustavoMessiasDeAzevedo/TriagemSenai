<?php

namespace App\Jobs;

use App\Models\Candidatura;
use App\Services\GeminiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessarAnaliseCurriculo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Permite que a IA demore até 2 minutos processando sem estourar o tempo
    public $timeout = 120;

    /**
     * Declaração explícita do tipo para o editor de código reconhecer o Eloquent
     * @var Candidatura
     */

    protected Candidatura $candidatura;
    protected string $textoCurriculo;
    protected string $contextoAnalise;

    public function __construct(Candidatura $candidatura, string $textoCurriculo, string $contextoAnalise)
    {
        $this->candidatura = $candidatura;
        $this->textoCurriculo = $textoCurriculo;
        $this->contextoAnalise = $contextoAnalise;
    }

    public function handle(GeminiService $geminiService): void
    {
        try {
            $resultado = $geminiService->analisarCurriculo($this->textoCurriculo, $this->contextoAnalise);

            $orientacao = $resultado['orientacao_candidato'] ?? 'Análise concluída com sucesso!';
            $cursos     = $resultado['recomendacoes_links']['cursos'] ?? '';
            $portais    = $resultado['recomendacoes_links']['portais_curriculo'] ?? '';

            $this->candidatura->update([
                'nivel_sugerido_ia' => $resultado['nivel_sugerido_ia'] ?? 'tecnico',
                'nota_match'        => $resultado['nota_match'] ?? 70,
                'resumo_ia'         => $resultado['resumo_ia'] ?? 'Análise concluída.',
                'trilha_links'      => json_encode([
                    'orientacao' => $orientacao,
                    'cursos'     => $cursos,
                    'portais'    => $portais,
                ]),
                'status'            => 'aguardando_retorno', // Status finalizado
            ]);
        } catch (\Throwable $e) {
            Log::error("Erro na Fila Gemini (Candidatura #{$this->candidatura->id}): " . $e->getMessage());
            $this->candidatura->update([
                'status' => 'erro_analise'
            ]);
        }
    }
}   