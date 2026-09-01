<x-app-layout>

    <x-slot name="title">
        Minhas Candidaturas
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Painel do Recrutador — Candidaturas') }}
        </h2>
    </x-slot>

    <div class="py-12" 
         x-data="{ 
            modalAberto: false, 
            candidaturaSelecionada: null,
            abrirModal(item) {
                this.candidaturaSelecionada = item;
                this.modalAberto = true;
            }
         }">
        
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- MENSAGEM DE SUCESSO -->
            @if (session('sucesso'))
                <div class="p-4 bg-green-100 border border-green-300 text-green-800 rounded-2xl shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">✅</span>
                        <p class="text-sm font-medium">{{ session('sucesso') }}</p>
                    </div>
                </div>
            @endif

            <!-- FILTROS DE PESQUISA -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('dashboard') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nível Sugerido IA</label>
                        <select name="nivel" class="w-full rounded-xl border-gray-300 text-sm focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Todos os Níveis</option>
                            <option value="avancado" {{ request('nivel') == 'avancado' ? 'selected' : '' }}>Avançado (80%+)</option>
                            <option value="tecnico" {{ request('nivel') == 'tecnico' ? 'selected' : '' }}>Técnico (50% a 79%)</option>
                            <option value="basico" {{ request('nivel') == 'basico' ? 'selected' : '' }}>Básico (&lt; 50%)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Área de Interesse</label>
                        <select name="area" class="w-full rounded-xl border-slate-200 text-sm text-slate-700 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Todas as Áreas</option>
                            @foreach($vagas as $vaga)
                                <option value="{{ $vaga->titulo }}" {{ request('area') == $vaga->titulo ? 'selected' : '' }}>
                                    {{ $vaga->titulo }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="w-full py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl transition-colors shadow-sm text-sm">
                            Filtrar
                        </button>
                        <a href="{{ route('dashboard') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold rounded-xl transition-colors text-sm flex items-center justify-center">
                            Limpar
                        </a>
                    </div>
                </form>
            </div>

            <!-- TABELA DE CANDIDATOS -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="py-4 px-6">Candidato</th>
                                <th class="py-4 px-6">Área</th>
                                <th class="py-4 px-6">Nota Match IA</th>
                                <th class="py-4 px-6">Nível Sugerido</th>
                                <th class="py-4 px-6">Status</th>
                                <th class="py-4 px-6 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($candidaturas as $item)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-4 px-6 font-semibold text-gray-800 whitespace-nowrap">
                                        {{ $item->user->name ?? 'Usuário Removido' }}
                                        <span class="block text-xs font-normal text-gray-400">{{ $item->user->email ?? '' }}</span>
                                    </td>

                                    <td class="py-4 px-6 text-gray-600 max-w-xs truncate">
                                        @php
                                            $vagaCorrespondente = $vagas->firstWhere('nivel', $item->nivel_sugerido_ia);
                                        @endphp
                                        {{ $vagaCorrespondente->titulo ?? $item->area_interesse ?? 'Vaga Não Definida' }}
                                    </td>

                                    <td class="py-4 px-6 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $item->nota_match >= 80 ? 'bg-green-100 text-green-700' : ($item->nota_match >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                            {{ $item->nota_match }}% Match
                                        </span>
                                    </td>

                                    <td class="py-4 px-6 capitalize text-gray-700 font-medium whitespace-nowrap">
                                        {{ $item->nivel_sugerido_ia ?? 'Não avaliado' }}
                                    </td>

                                    <td class="py-4 px-6 whitespace-nowrap">
                                        @if ($item->status == 'aguardando_retorno')
                                            <span class="px-2.5 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded-full">Análise Pendente</span>
                                        @elseif ($item->status == 'entrevista_agendada')
                                            <span class="px-2.5 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">Entrevista Agendada</span>
                                        @elseif ($item->status == 'finalizado')
                                            <span class="px-2.5 py-1 bg-purple-100 text-purple-700 text-xs font-bold rounded-full">Finalizado</span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-6 text-right space-x-2 whitespace-nowrap">
                                        @if($item->caminho_pdf)
                                            <a href="{{ asset('storage/' . $item->caminho_pdf) }}" target="_blank" class="inline-flex items-center text-xs font-bold text-gray-500 hover:text-gray-700">
                                                📄 PDF
                                            </a>
                                        @endif

                                        <button type="button" 
                                                @click="abrirModal({{ $item->toJson() }})" 
                                                class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-lg text-xs transition-colors shadow-sm">
                                            Avaliar / Agendar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-400">
                                        Nenhuma candidatura encontrada com os filtros aplicados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MODAL DE ANÁLISE -->
            <div x-show="modalAberto" 
                 x-cloak
                 style="display: none;"
                 class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">

                <div @click.away="modalAberto = false" class="bg-white rounded-2xl p-6 max-w-2xl w-full shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto">

                    <!-- CABEÇALHO DO MODAL -->
                    <div class="flex items-center justify-between border-b pb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Análise de Candidatura</h3>
                            <p class="text-xs text-gray-500">
                                Candidato: <span class="font-bold text-gray-700" x-text="candidaturaSelecionada?.user?.name"></span>
                            </p>
                        </div>
                        <button type="button" @click="modalAberto = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
                    </div>

                    <!-- BLOCO IA / RESUMO -->
                    <div class="p-4 bg-purple-50 rounded-xl border border-purple-100 text-sm space-y-2">
                        <div class="flex items-center gap-2 font-bold text-purple-900">
                            <span>📝</span> Parecer Técnico IA:
                        </div>
                        <p class="text-xs text-purple-800 leading-relaxed italic" x-text="candidaturaSelecionada?.resumo_ia"></p>
                    </div>

                    <!-- FORMULÁRIO DE ATUALIZAÇÃO -->
                    <template x-if="candidaturaSelecionada">
                        <form :action="`/dashboard/candidaturas/${candidaturaSelecionada.id}`" method="POST" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Status da Candidatura</label>
                                    <select name="status" x-model="candidaturaSelecionada.status" class="w-full rounded-xl border-gray-300 text-sm focus:ring-purple-500">
                                        <option value="aguardando_retorno">Aguardando Retorno (Em Análise)</option>
                                        <option value="entrevista_agendada">Entrevista Agendada</option>
                                        <option value="finalizado">Processo Finalizado</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Data/Hora da Entrevista</label>
                                    <input type="datetime-local" 
                                           name="data_entrevista" 
                                           x-model="candidaturaSelecionada.data_entrevista" 
                                           class="w-full rounded-xl border-gray-300 text-sm focus:ring-purple-500">
                                </div>
                            </div>

                            <div>
                                <label for="local_entrevista_input" class="block text-xs font-bold text-gray-700 uppercase mb-2">Local / Sala da Entrevista</label>
                                <input type="text" 
                                    id="local_entrevista_input" 
                                    name="local_entrevista" 
                                    x-model="candidaturaSelecionada.local_entrevista" 
                                    placeholder="Ex: Sala de Reuniões 02 / Presencial no Bloco 01" 
                                    class="w-full rounded-xl border-gray-300 text-sm focus:ring-purple-500">
                            </div>

                            <div>
                                <label for="feedback_recrutador_input" class="block text-xs font-bold text-gray-700 uppercase mb-2">Feedback / Recado Visível para o Aluno</label>
                                <textarea id="feedback_recrutador_input" 
                                        name="feedback_recrutador" 
                                        rows="3" 
                                        x-model="candidaturaSelecionada.feedback_recrutador" 
                                        placeholder="Escreva uma mensagem ou parecer conclusivo que o aluno verá no modal de feedback..." 
                                        class="w-full rounded-xl border-gray-300 text-sm focus:ring-purple-500"></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t">
                                <button type="button" @click="modalAberto = false" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-sm transition-colors">
                                    Cancelar
                                </button>
                                <button type="submit" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl text-sm transition-colors shadow-sm flex items-center gap-2">
                                    <span>Salvar e Enviar para o Aluno</span> 💾
                                </button>
                            </div>
                        </form>
                    </template>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>