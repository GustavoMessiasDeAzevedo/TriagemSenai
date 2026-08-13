<x-app-layout>

    <x-slot name="title">
        Minhas Candidaturas
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Minhas Candidaturas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- ALERTA DE SUCESSO -->
            @if (session('sucesso'))
                <div class="p-4 bg-green-100 border border-green-300 text-green-800 rounded-2xl shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">✅</span>
                        <p class="text-sm font-medium">{{ session('sucesso') }}</p>
                    </div>
                </div>
            @endif

            @php
                $candidatura = $candidaturas->sortByDesc('updated_at')->first();// Pega a candidatura cadastrada do usuário
            @endphp

            <!-- CARD PRINCIPAL COM A TIMELINE FIXA -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6">

                <!-- CABEÇALHO DA CANDIDATURA -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 bg-purple-100 text-purple-700 text-xs font-bold rounded-full uppercase tracking-wider">
                                {{ $candidatura->area_interesse ?? 'Eletroeletrônica SENAI' }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">
                            @if($candidatura)
                                Enviado em {{ \Carbon\Carbon::parse($candidatura->created_at)->format('d/m/Y \à\s H:i') }}
                            @else
                                Pendente de envio
                            @endif
                        </p>
                    </div>

                    <!-- BADGE DE STATUS GERAL -->
                    <div>
                        @if (!$candidatura)
                            <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-full flex items-center gap-1.5 w-fit">
                                <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                Aguardando Teste/Envio
                            </span>
                        @elseif ($candidatura->status == 'aguardando_retorno')
                            <span class="px-3 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded-full flex items-center gap-1.5 w-fit">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                Analisando currículo e teste
                            </span>
                        @elseif ($candidatura->status == 'entrevista_agendada')
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full flex items-center gap-1.5 w-fit">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                Entrevista Agendada
                            </span>
                        @elseif ($candidatura->status == 'finalizado')
                            <span class="px-3 py-1 bg-purple-100 text-purple-700 text-xs font-bold rounded-full flex items-center gap-1.5 w-fit">
                                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                Processo Finalizado
                            </span>
                        @endif
                    </div>
                </div>

                <!-- TÍTULO DA SEÇÃO -->
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                    Etapas do Processo
                </h3>

                <!-- GRID DA TIMELINE (4 ETAPAS) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                    <!-- ETAPA 1: Teste e Currículo -->
                    <div class="p-4 border rounded-xl transition-all {{ $candidatura ? 'bg-green-50/70 border-green-200' : 'bg-blue-50/70 border-blue-300 ring-2 ring-blue-100' }}">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full {{ $candidatura ? 'bg-green-500' : 'bg-blue-600' }} text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                {{ $candidatura ? '✓' : '1' }}
                            </span>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">1. Teste & Envio</h4>
                                <p class="text-xs {{ $candidatura ? 'text-green-600' : 'text-blue-600' }} font-medium">
                                    {{ $candidatura ? 'Concluído' : 'Em andamento' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ETAPA 2: Análise Técnica -->
                    <div class="p-4 border rounded-xl transition-all {{ $candidatura && $candidatura->status == 'aguardando_retorno' ? 'bg-amber-50/70 border-amber-300 ring-2 ring-amber-100' : 'bg-gray-50 border-gray-200 opacity-60' }}">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full {{ $candidatura && $candidatura->status == 'aguardando_retorno' ? 'bg-amber-400 text-white' : 'bg-gray-300 text-gray-600' }} flex items-center justify-center font-bold text-sm shadow-sm">2</span>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">2. Análise Técnica</h4>
                                <p class="text-xs text-amber-600 font-medium">
                                    @if(!$candidatura) Aguardando etapa 1 @elseif($candidatura->status == 'aguardando_retorno') Em andamento... @else Concluído @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ETAPA 3: Entrevista -->
                    @php
                        $entrevistaLiberada = $candidatura && in_array($candidatura->status, ['entrevista_agendada', 'finalizado']);
                    @endphp
                    <div class="p-4 border rounded-xl transition-all {{ $entrevistaLiberada ? 'bg-blue-50/70 border-blue-300 cursor-pointer hover:shadow-md ring-2 ring-blue-100' : 'bg-gray-50 border-gray-200 opacity-60' }}"
                         @if($entrevistaLiberada) onclick="openModal('modalEntrevista')" @endif>
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full {{ $entrevistaLiberada ? 'bg-blue-500 text-white' : 'bg-gray-300 text-gray-600' }} flex items-center justify-center font-bold text-sm shadow-sm">3</span>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">3. Entrevista</h4>
                                <p class="text-xs text-blue-600 font-medium">
                                    @if($candidatura && $candidatura->status == 'entrevista_agendada')
                                        <span class="underline font-semibold">Ver Agendamento 📅</span>
                                    @elseif($candidatura && $candidatura->status == 'finalizado')
                                        Realizada
                                    @else
                                        Aguardando etapa 2
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ETAPA 4: Feedback -->
                    <div class="p-4 border rounded-xl transition-all {{ $candidatura && $candidatura->status == 'finalizado' ? 'bg-purple-50/70 border-purple-300 cursor-pointer hover:shadow-md ring-2 ring-purple-100' : 'bg-gray-50 border-gray-200 opacity-60' }}"
                         @if($candidatura && $candidatura->status == 'finalizado') onclick="openModal('modalFeedback')" @endif>
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full {{ $candidatura && $candidatura->status == 'finalizado' ? 'bg-purple-500 text-white' : 'bg-gray-300 text-gray-600' }} flex items-center justify-center font-bold text-sm shadow-sm">4</span>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">4. Feedback</h4>
                                <p class="text-xs text-purple-600 font-medium">
                                    {{ $candidatura && $candidatura->status == 'finalizado' ? 'Ver Parecer Final 📝' : 'Aguardando etapa 3' }}
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- ÁREA DO QUIZ (SÓ APARECE SE ELE AINDA NÃO SE CANDIDATOU) -->
            @if (!$candidatura)
                <div x-data="candidaturaFlow()" class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-gray-100">

                    <!-- ETAPA 0: TELA INICIAL DO QUIZ -->
                    <template x-if="etapa === 0">
                        <div class="text-center py-8 space-y-4">
                            <span class="text-5xl">📝</span>
                            <h3 class="text-xl font-bold text-gray-800">Teste Técnico de Candidatura</h3>
                            <p class="text-sm text-gray-500 max-w-lg mx-auto">
                                Para concluir sua candidatura no setor de Eletroeletrônica, responda às 6 questões técnicas a seguir.
                            </p>
                            <button @click="iniciarQuiz()" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-colors shadow-sm">
                                Iniciar Teste Agora
                            </button>
                        </div>
                    </template>

                    <!-- ETAPAS 1 A 6: PERGUNTAS DO QUIZ -->
                    <template x-if="etapa > 0 && etapa <= questaoList.length">
                        <div class="space-y-6">
                            <div class="flex items-center justify-between border-b pb-4">
                                <span class="text-xs font-bold text-purple-600 uppercase tracking-wider" x-text="questaoList[etapa - 1].bloco"></span>
                                <span class="text-xs text-gray-400 font-semibold" x-text="`Questão ${etapa} de ${questaoList.length}`"></span>
                            </div>

                            <p class="text-gray-800 font-medium text-base leading-relaxed" x-text="questaoList[etapa - 1].pergunta"></p>

                            <div class="space-y-3">
                                <template x-for="(opcao, index) in questaoList[etapa - 1].opcoes" :key="index">
                                    <label class="flex items-center p-4 border rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                                        <input type="radio"
                                               :name="`questao_${questaoList[etapa - 1].id}`"
                                               :value="opcao"
                                               x-model="respostas[questaoList[etapa - 1].id]"
                                               class="text-blue-600 focus:ring-blue-500">
                                        <span class="ml-3 text-sm text-gray-700" x-text="opcao"></span>
                                    </label>
                                </template>
                            </div>

                            <button @click="proximaEtapa()"
                                    :disabled="!respostas[questaoList[etapa - 1].id]"
                                    class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl disabled:opacity-50 transition-colors">
                                <span x-text="etapa === questaoList.length ? 'Finalizar Teste e Anexar Currículo' : 'Próxima Questão'"></span>
                            </button>
                        </div>
                    </template>

                    <!-- MODAL DE UPLOAD DO CURRÍCULO -->
                    <div x-show="abrirModalCurriculo"
                         x-cloak
                         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                        <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4">
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                📄 Anexar Currículo
                            </h3>
                            <p class="text-xs text-gray-500">Parabéns por concluir o teste! Agora selecione seu currículo para registrar sua candidatura.</p>

                            <form action="{{ route('candidaturas.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <input type="hidden" name="respostas" :value="JSON.stringify(respostas)">

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Arquivo (PDF)</label>
                                    <input type="file" name="curriculo" required accept=".pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                </div>

                                <button type="submit" class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl transition-colors shadow-sm">
                                    Enviar Candidatura
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            @endif

            <!-- MODAIS DINÂMICOS (SE HOUVER CANDIDATURA) -->
            @if($candidatura)

                <!-- MODAL DA ENTREVISTA -->
                <div id="modalEntrevista" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl">
                        <h3 class="text-lg font-bold text-gray-800 mb-1 flex items-center gap-2">
                            📅 Entrevista Agendada
                        </h3>
                        <p class="text-xs text-gray-500 mb-4">Informações sobre o seu agendamento:</p>

                        <div class="space-y-3 bg-blue-50 p-4 rounded-xl text-sm border border-blue-100 text-blue-950">
                            <p>
                                <strong>Data/Hora:</strong>
                                {{ $candidatura->data_entrevista ? \Carbon\Carbon::parse($candidatura->data_entrevista)->format('d/m/Y \à\s H:i') : 'A definir pelo recrutador' }}
                            </p>
                            <p>
                                    <strong>Local/Sala:</strong>
                                    {{ !empty($candidatura->local_entrevista) ? $candidatura->local_entrevista : 'A definir pelo recrutador' }}
                            </p>
                        </div>

                        <button onclick="closeModal('modalEntrevista')" class="mt-5 w-full py-2.5 bg-gray-900 text-white rounded-xl font-bold hover:bg-gray-800 transition-colors">
                            Fechar
                        </button>
                    </div>
                </div>

                <!-- MODAL DO FEEDBACK -->
                <div id="modalFeedback" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl">
                        <h3 class="text-lg font-bold text-gray-800 mb-1 flex items-center gap-2">
                            📝 Parecer Final do Recrutador
                        </h3>
                        <p class="text-xs text-gray-500 mb-4">Feedback conclusivo sobre o seu processo seletivo:</p>

                        <div class="p-4 bg-purple-50 rounded-xl text-purple-900 text-sm border border-purple-100 leading-relaxed whitespace-pre-line">
                            {{ $candidatura->feedback_recrutador ?? 'Nenhum parecer cadastrado até o momento.' }}
                        </div>

                        <button onclick="closeModal('modalFeedback')" class="mt-5 w-full py-2.5 bg-gray-900 text-white rounded-xl font-bold hover:bg-gray-800 transition-colors">
                            Fechar
                        </button>
                    </div>
                </div>

            @endif

        </div>
    </div>

    <!-- SCRIPTS -->
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
