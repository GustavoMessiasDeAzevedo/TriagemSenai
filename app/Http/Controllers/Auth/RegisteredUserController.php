<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validação básica dos campos do formulário
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:candidato,recrutador'],
        ]);

        // 2. Regras de Segurança para Recrutadores / Docentes
        if ($request->role === 'recrutador') {
            // Lista de domínios institucionais permitidos
            $dominiosAceitos = ['@sp.senai.br', '@senai.br', '@aluno.senai.br', '@docente.senai.br'];

            $emailValido = false;
            foreach ($dominiosAceitos as $dominio) {
                if (str_ends_with($request->email, $dominio)) {
                    $emailValido = true;
                    break;
                }
            }

            if (! $emailValido) {
                return back()->withInput()->withErrors([
                    'email' => 'Cadastros de Recrutadores/Docentes exigem um e-mail institucional do SENAI.',
                ]);
            }

            // Valida a Chave de Acesso Corporativa (padrão: SENAI2026)
            $chaveValida = env('CHAVE_RECRUTADOR_SENAI', 'SENAI2026');

            if ($request->chave_acesso !== $chaveValida) {
                return back()->withInput()->withErrors([
                    'chave_acesso' => 'Chave de acesso corporativa inválida para o perfil de Recrutador.',
                ]);
            }
        }

        // 3. Criação do Usuário
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        event(new Registered($user));

        Auth::login($user);

        // 4. Redirecionamento por Perfil de Acesso
        if ($user->role === 'recrutador') {
            return redirect()->route('recrutador.dashboard');
        }

        return redirect()->route('candidaturas.index');
    }
}
