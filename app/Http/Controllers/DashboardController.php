<?php

namespace App\Http\Controllers;

use App\Models\Candidatura;
use App\Models\Vaga;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Lista candidatos para o Recrutador analisar.
     */
    public function index(Request $request)
    {
        $query = Candidatura::with(['user', 'vaga']);

        if ($request->filled('nivel')) {
            $query->where('nivel_sugerido_ia', $request->nivel);
        }

        if ($request->filled('area')) {
            $query->where('area_interesse', $request->area);
        }

        $candidaturas = $query->orderBy('nota_match', 'desc')->paginate(10);
        $vagas = Vaga::all();

        return view('recrutador.dashboard', compact('candidaturas', 'vagas'));
    }

    /**
     * Atualiza o Status, Agendamento, Local da Entrevista e Feedback pro Aluno.
     */
    public function update(Request $request, Candidatura $candidatura)
    {
        $request->validate([
            'status' => 'required|in:aguardando_retorno,entrevista_agendada,finalizado',
            'data_entrevista' => 'nullable|date',
            'local_entrevista' => 'nullable|string|max:255',
            'feedback_recrutador' => 'nullable|string',
        ]);

        $candidatura->status = $request->input('status');
        $candidatura->data_entrevista = $request->input('data_entrevista');
        $candidatura->local_entrevista = $request->input('local_entrevista');
        $candidatura->feedback_recrutador = $request->input('feedback_recrutador');

        $candidatura->saveOrFail();

        return redirect()->back()->with('sucesso', 'Acompanhamento atualizado!');
    }
}
