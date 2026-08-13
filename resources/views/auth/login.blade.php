<x-guest-layout>
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-6">
        <!-- Logo Oficial do SENAI (PNG Oficial) -->
    <div class="flex justify-center mb-4">
        <img src="{{ asset('images/logo-senai.png') }}" alt="SENAI" class="h-12 w-auto object-contain">
    </div>

        <h2 class="text-2xl font-bold text-gray-800">Acesse o Portal</h2>
        <p class="text-sm text-gray-600">Plataforma de Triagem de Currículos por IA</p>
    </div>

    <!-- Status da Sessão -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- E-mail -->
        <div>
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Senha -->
        <div>
            <x-input-label for="password" :value="__('Senha')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Lembrar de mim -->
        <div class="block">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Lembrar de mim') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between pt-2">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none" href="{{ route('password.request') }}">
                    {{ __('Esqueceu a senha?') }}
                </a>
            @endif

            <button type="submit" class="px-5 py-2.5 text-white font-bold rounded shadow hover:opacity-90 transition" style="background-color: #0054A6;">
                {{ __('Entrar') }}
            </button>
        </div>

        <!-- Divisor -->
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200"></div>
            </div>
            <div class="relative flex justify-center text-xs uppercase">
                <span class="bg-white px-2 text-gray-500 font-semibold">Novo por aqui?</span>
            </div>
        </div>

        <!-- Botão para Cadastre-se -->
        <div class="text-center">
            <a href="{{ route('register') }}" class="inline-block w-full py-2.5 px-4 border border-blue-800 text-blue-800 font-bold rounded-md hover:bg-blue-50 transition text-sm">
                Criar Nova Conta / Cadastre-se
            </a>
        </div>
    </form>
</x-guest-layout>