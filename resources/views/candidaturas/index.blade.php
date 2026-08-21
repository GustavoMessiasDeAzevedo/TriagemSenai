<x-app-layout>

    <x-slot name="title">
        Minhas Candidaturas
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight flex items-center gap-2">
            <span>📋</span> {{ __('Minhas Candidaturas') }}
        </h2>
    </x-slot>

    <div class="py-10 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            @if (session('sucesso'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm flex items-center justify-between transition-all duration-300">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">✅</span>
                        <p class="text-sm font-semibold">{{ session('sucesso') }}</p>
                    </div>
                </div>
            @endif

            @php
                $candidatura = $candidaturas ? $candidaturas->sortByDesc('updated_at')->first() : null;
            @endphp

            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200/80 space-y-8 transition-all hover:shadow-md">

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-3.5 py-1.5 bg-purple-50 text-purple-700 border border-purple-200/60 text-xs font-bold rounded-full uppercase tracking-wider shadow-2xs">
                                {{ $candidatura->area_interesse ?? 'Eletroeletrônica SENAI' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-2.5 flex items-center gap-1 font-medium">
                            <span>🕒</span>
                            @if($candidatura)
                                Enviado em {{ \Carbon\Carbon::parse($candidatura->created_at)->format('d/m/Y \à\s H:i') }}
                            @else
                                Pendente de envio
                            @endif
                        </p>
                    </div>

                    <div>
                        @if (!$candidatura)
                            <span class="px-3.5 py-1.5 bg-slate-100 text-slate-700 text-xs font-bold rounded-full flex items-center gap-2 w-fit border border-slate-200/60">
                                <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                Aguardando Teste/Envio
                            </span>
                        @elseif ($candidatura->status == 'aguardando_retorno')
                            <span class="px-3.5 py-1.5 bg-amber-50 text-amber-800 text-xs font-bold rounded-full flex items-center gap-2 w-fit border border-amber-200/80 shadow-2xs">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                Analisando currículo e teste
                            </span>
                        @elseif ($candidatura->status == 'entrevista_agendada')
                            <span class="px-3.5 py-1.5 bg-blue-50 text-blue-700 text-xs font-bold rounded-full flex items-center gap-2 w-fit border border-blue-200/80 shadow-2xs">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                Entrevista Agendada
                            </span>
                        @elseif ($candidatura->status == 'finalizado')
                            <span class="px-3.5 py-1.5 bg-purple-50 text-purple-700 text-xs font-bold rounded-full flex items-center gap-2 w-fit border border-purple-200/80 shadow-2xs">
                                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                Processo Finalizado
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider">
                        Etapas do Processo
                    </h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                    <div class="p-4 border rounded-2xl transition-all duration-200 {{ $candidatura ? 'bg-emerald-50/50 border-emerald-200/80 shadow-2xs' : 'bg-blue-50/60 border-blue-300 ring-2 ring-blue-100 shadow-xs' }}">
                        <div class="flex items-center gap-3.5">
                            <span class="w-9 h-9 rounded-xl {{ $candidatura ? 'bg-emerald-500' : 'bg-blue-600' }} text-white flex items-center justify-center font-bold text-sm shadow-sm transition-transform hover:scale-105">
                                {{ $candidatura ? '✓' : '1' }}
                            </span>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm">1. Teste & Envio</h4>
                                <p class="text-xs {{ $candidatura ? 'text-emerald-700' : 'text-blue-700' }} font-semibold mt-0.5">
                                    {{ $candidatura ? 'Concluído' : 'Em andamento' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border rounded-2xl transition-all duration-200 {{ $candidatura && $candidatura->status == 'aguardando_retorno' ? 'bg-amber-50/70 border-amber-300 ring-2 ring-amber-100 shadow-xs' : 'bg-slate-50/80 border-slate-200/80 opacity-70' }}">
                        <div class="flex items-center gap-3.5">
                            <span class="w-9 h-9 rounded-xl {{ $candidatura && $candidatura->status == 'aguardando_retorno' ? 'bg-amber-500 text-white' : 'bg-slate-200 text-slate-600' }} flex items-center justify-center font-bold text-sm shadow-sm">2</span>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm">2. Análise Técnica</h4>
                                <p class="text-xs text-amber-700 font-semibold mt-0.5">
                                    @if(!$candidatura) Aguardando etapa 1 @elseif($candidatura->status == 'aguardando_retorno') Em andamento... @else Concluído @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    @php
                        $entrevistaLiberada = $candidatura && in_array($candidatura->status, ['entrevista_agendada', 'finalizado']);
                    @endphp
                    <div class="p-4 border rounded-2xl transition-all duration-200 {{ $entrevistaLiberada ? 'bg-blue-50/70 border-blue-300 cursor-pointer hover:shadow-md hover:-translate-y-0.5 ring-2 ring-blue-100' : 'bg-slate-50/80 border-slate-200/80 opacity-70' }}"
                         @if($entrevistaLiberada) onclick="openModal('modalEntrevista')" @endif>
                        <div class="flex items-center gap-3.5">
                            <span class="w-9 h-9 rounded-xl {{ $entrevistaLiberada ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600' }} flex items-center justify-center font-bold text-sm shadow-sm">3</span>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm">3. Entrevista</h4>
                                <p class="text-xs text-blue-700 font-semibold mt-0.5">
                                    @if($candidatura && $candidatura->status == 'entrevista_agendada')
                                        <span class="underline hover:text-blue-800 transition-colors">Ver Agendamento 📅</span>
                                    @elseif($candidatura && $candidatura->status == 'finalizado')
                                        Realizada
                                    @else
                                        Aguardando etapa 2
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border rounded-2xl transition-all duration-200 {{ $candidatura && $candidatura->status == 'finalizado' ? 'bg-purple-50/70 border-purple-300 cursor-pointer hover:shadow-md hover:-translate-y-0.5 ring-2 ring-purple-100' : 'bg-slate-50/80 border-slate-200/80 opacity-70' }}"
                         @if($candidatura && $candidatura->status == 'finalizado') onclick="openModal('modalFeedback')" @endif>
                        <div class="flex items-center gap-3.5">
                            <span class="w-9 h-9 rounded-xl {{ $candidatura && $candidatura->status == 'finalizado' ? 'bg-purple-600 text-white' : 'bg-slate-200 text-slate-600' }} flex items-center justify-center font-bold text-sm shadow-sm">4</span>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm">4. Feedback</h4>
                                <p class="text-xs text-purple-700 font-semibold mt-0.5">
                                    {{ $candidatura && $candidatura->status == 'finalizado' ? 'Ver Parecer Final 📝' : 'Aguardando etapa 3' }}
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            @if (!$candidatura)
                <div x-data="candidaturaFlow()" class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200/80 transition-all hover:shadow-md">

                    <template x-if="etapa === 0">
                        <div class="text-center py-8 space-y-5 max-w-xl mx-auto">
                            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-3xl mx-auto border border-blue-100 shadow-2xs">
                                📝
                            </div>
                            <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">Teste Técnico de Candidatura</h3>
                            <p class="text-sm text-slate-500 leading-relaxed">
                                Para concluir sua candidatura no setor de Eletroeletrônica, responda às 6 questões técnicas a seguir.
                            </p>
                            <button @click="iniciarQuiz()" class="px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-all duration-200 shadow-md hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0">
                                Iniciar Teste Agora
                            </button>
                        </div>
                    </template>

                    <template x-if="etapa > 0 && etapa <= questaoList.length">
                        <div class="space-y-6 max-w-2xl mx-auto">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                                <span class="px-3 py-1 bg-purple-50 text-purple-700 border border-purple-200/60 rounded-full text-xs font-bold uppercase tracking-wider" x-text="questaoList[etapa - 1].bloco"></span>
                                <span class="text-xs text-slate-400 font-bold tracking-wide" x-text="`Questão ${etapa} de ${questaoList.length}`"></span>
                            </div>

                            <p class="text-slate-800 font-semibold text-base leading-relaxed" x-text="questaoList[etapa - 1].pergunta"></p>

                            <div class="space-y-3">
                                <template x-for="(opcao, index) in questaoList[etapa - 1].opcoes" :key="index">
                                    <label class="flex items-center p-4 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 hover:border-slate-300 transition-all duration-150 group">
                                        <input type="radio"
                                               :name="`questao_${questaoList[etapa - 1].id}`"
                                               :value="opcao"
                                               x-model="respostas[questaoList[etapa - 1].id]"
                                               class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-slate-300">
                                        <span class="ml-3.5 text-sm font-medium text-slate-700 group-hover:text-slate-900" x-text="opcao"></span>
                                    </label>
                                </template>
                            </div>

                            <button @click="proximaEtapa()"
                                    :disabled="!respostas[questaoList[etapa - 1].id]"
                                    class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200 shadow-sm hover:shadow-md">
                                <span x-text="etapa === questaoList.length ? 'Finalizar Teste e Anexar Currículo' : 'Próxima Questão'"></span>
                            </button>
                        </div>
                    </template>

                    <div x-show="abrirModalCurriculo"
                         x-cloak
                         class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-opacity">
                        <div class="bg-white rounded-2xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-5 border border-slate-100 animate-in fade-in zoom-in-95 duration-200">
                            <div>
                                <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                                    <span>📄</span> Anexar Currículo
                                </h3>
                                <p class="text-xs text-slate-500 mt-1">Parabéns por concluir o teste! Agora selecione seu currículo para registrar sua candidatura.</p>
                            </div>

                            <form action="{{ route('candidaturas.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                                @csrf
                                <input type="hidden" name="respostas" :value="JSON.stringify(respostas)">

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Arquivo (PDF)</label>
                                    <input type="file" name="curriculo" required accept=".pdf" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:transition-colors cursor-pointer border border-slate-200 rounded-xl p-1 bg-slate-50/50">
                                </div>

                                <button type="submit" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition-all duration-200 shadow-md hover:shadow-lg">
                                    Enviar Candidatura
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            @endif

            @if($candidatura)

                <div id="modalEntrevista" class="hidden fixed inset-0 bg-slate-950/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-all">
                    <div class="bg-white rounded-2xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-slate-100">
                        <h3 class="text-xl font-bold text-slate-800 mb-1 flex items-center gap-2">
                            <span>📅</span> Entrevista Agendada
                        </h3>
                        <p class="text-xs text-slate-500 mb-5">Informações sobre o seu agendamento:</p>

                        <div class="space-y-3 bg-blue-50/70 p-4 rounded-xl text-sm border border-blue-100 text-blue-950 font-medium">
                            <p class="flex items-start gap-2">
                                <span class="font-bold text-blue-900 min-w-24">Data/Hora:</span>
                                <span>{{ $candidatura->data_entrevista ? \Carbon\Carbon::parse($candidatura->data_entrevista)->format('d/m/Y \à\s H:i') : 'A definir pelo recrutador' }}</span>
                            </p>
                            <p class="flex items-start gap-2">
                                <span class="font-bold text-blue-900 min-w-24">Local/Sala:</span>
                                <span>{{ !empty($candidatura->local_entrevista) ? $candidatura->local_entrevista : 'A definir pelo recrutador' }}</span>
                            </p>
                        </div>

                        <button onclick="closeModal('modalEntrevista')" class="mt-6 w-full py-3 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition-colors shadow-sm">
                            Fechar
                        </button>
                    </div>
                </div>

                <!-- MODAL DE FEEDBACK COM ANALISE IA E RECOMENDAÇÕES/LINKS -->
                <div id="modalFeedback" class="hidden fixed inset-0 bg-slate-950/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-all">
                    <div class="bg-white rounded-2xl p-6 sm:p-8 max-w-xl w-full shadow-2xl border border-slate-100 space-y-5 max-h-[90vh] overflow-y-auto">
                        
                        <div>
                            <h3 class="text-xl font-bold text-slate-800 mb-1 flex items-center gap-2">
                                <span>📝</span> Parecer Final do Recrutador
                            </h3>
                            <p class="text-xs text-slate-500">Feedback e avaliação técnica sobre o seu processo seletivo:</p>
                        </div>

                        <!-- ANÁLISE IDENTIFICADA PELA IA -->
                        @if($candidatura->resumo_ia)
                            <div class="p-4 bg-slate-50 border border-slate-200/80 rounded-xl space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-extrabold text-slate-500 uppercase tracking-wider">🤖 Análise do Perfil (IA)</span>
                                    <span class="px-2.5 py-0.5 bg-purple-100 text-purple-700 text-xs font-bold rounded-full uppercase">
                                        Nível {{ $candidatura->nivel_sugerido_ia ?? 'Técnico' }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-700 leading-relaxed font-medium">
                                    {{ $candidatura->resumo_ia }}
                                </p>
                            </div>
                        @endif

                        <!-- RECOMENDAÇÕES E RECADO COM DETECÇÃO AUTOMÁTICA DE LINKS -->
                        <div class="space-y-2">
                            <span class="text-xs font-extrabold text-slate-500 uppercase tracking-wider">💬 Recado do Recrutador & Recomendações</span>
                            <div class="p-4 bg-purple-50/70 rounded-xl text-purple-950 text-sm border border-purple-100 leading-relaxed whitespace-pre-line font-medium">
                                @php
                                    // Utiliza o feedback do recrutador ou o campo de recomendações/links gerado pelo Gemini
                                    $textoFeedback = $candidatura->feedback_recrutador 
                                        ?? $candidatura->recomendacoes_links_ia 
                                        ?? 'Nenhum parecer cadastrado até o momento.';

                                    // Expressão regular que reconhece URLs com ou sem protocolo (http, https, www, domínios .com, .br, etc)
                                    $pattern = '/\b(?:https?:\/\/|www\.)[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(?:\/[^\s<]*)?/i';

                                    $textoComLinks = preg_replace_callback($pattern, function($matches) {
                                        $url = $matches[0];
                                        $href = preg_match('/^https?:\/\//i', $url) ? $url : 'https://' . $url;
                                        return '<a href="' . $href . '" target="_blank" class="text-purple-700 underline font-bold hover:text-purple-900 transition-colors">' . $url . '</a>';
                                    }, e($textoFeedback));
                                @endphp
                                {!! $textoComLinks !!}
                            </div>
                        </div>

                        <button onclick="closeModal('modalFeedback')" class="w-full py-3 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition-colors shadow-sm">
                            Fechar
                        </button>
                    </div>
                </div>

            @endif

        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
    </script>
    <script src="{{ asset('js/candidatura.js') }}"></script>

</x-app-layout>
