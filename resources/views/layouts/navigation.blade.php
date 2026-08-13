<nav x-data="{ open: false }" class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo Direto da Pasta Public -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-senai.png') }}" alt="SENAI" class="h-9 w-auto object-contain">
                    <span class="font-bold text-gray-800 text-base tracking-wide border-l pl-3 border-gray-300">
                        Portal IA
                    </span>
                </a>

                <!-- Links de Navegação -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex h-full">
                    @if(Auth::user()->role === 'recrutador')
                        <x-nav-link :href="route('recrutador.dashboard')" :active="request()->routeIs('recrutador.dashboard')" class="text-gray-700 hover:text-red-600 font-medium">
                            {{ __('Painel Recrutador (RH)') }}
                        </x-nav-link>
                    @else
                        <x-nav-link :href="route('candidaturas.index')" :active="request()->routeIs('candidaturas.index')" class="text-gray-700 hover:text-red-600 font-medium">
                            {{ __('Minhas Candidaturas') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Menu de Usuário -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition">
                            <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 border-b border-gray-100 text-xs text-gray-400 font-semibold uppercase">
                            Perfil: <span class="text-red-600">{{ Auth::user()->role }}</span>
                        </div>
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Meu Perfil') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Sair') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>