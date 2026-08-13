<x-guest-layout>
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-6">
        <!-- Logo Oficial SENAI (SVG Inline) -->
    <div class="flex justify-center mb-4">
        <img src="{{ asset('images/logo-senai.png') }}" alt="SENAI" class="h-12 w-auto object-contain">
    </div>

        <h2 class="text-2xl font-bold text-gray-800">Criar Nova Conta</h2>
        <p class="text-sm text-gray-600">Sistema de Triagem Inteligente de Talentos</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Seleção de Perfil -->
        <div>
            <x-input-label for="role" :value="__('Tipo de Conta / Perfil')" class="font-bold text-gray-700" />
            <select id="role" name="role" onchange="toggleChaveRecrutador(this.value)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-600 focus:ring-blue-600 text-sm" required>
                <option value="candidato" {{ old('role') == 'candidato' ? 'selected' : '' }}>Aluno / Candidato</option>
                <option value="recrutador" {{ old('role') == 'recrutador' ? 'selected' : '' }}>Docente / Recrutador (RH)</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <!-- Campo Chave Corporativa (Aparece apenas para Recrutadores) -->
        <div id="campo_chave" class="{{ old('role') == 'recrutador' ? '' : 'hidden' }} p-3 bg-yellow-50 border border-yellow-200 rounded-md">
            <x-input-label for="chave_acesso" :value="__('Chave de Acesso Corporativa')" class="font-bold text-yellow-800" />
            <x-text-input id="chave_acesso" class="block mt-1 w-full border-yellow-400 text-sm" type="password" name="chave_acesso" placeholder="Chave fornecida pela coordenação" />
            <x-input-error :messages="$errors->get('chave_acesso')" class="mt-2" />
        </div>

        <!-- Nome Completo -->
        <div>
            <x-input-label for="name" :value="__('Nome Completo')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- E-mail -->
        <div>
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Senha -->
        <div>
            <x-input-label for="password" :value="__('Senha')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirmar Senha -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirmar Senha')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between pt-2">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none" href="{{ route('login') }}">
                {{ __('Já tem uma conta?') }}
            </a>

            <button type="submit" class="px-5 py-2.5 text-white font-bold rounded shadow hover:opacity-90 transition" style="background-color: #0054A6;">
                {{ __('Cadastrar') }}
            </button>
        </div>
    </form>

    <script>
        function toggleChaveRecrutador(role) {
            const campoChave = document.getElementById('campo_chave');
            if (role === 'recrutador') {
                campoChave.classList.remove('hidden');
            } else {
                campoChave.classList.add('hidden');
            }
        }
    </script>
</x-guest-layout>