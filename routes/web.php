<?php

use App\Http\Controllers\CandidaturaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {

    // Redirecionador inteligente baseado no perfil do usuário logado
    Route::get('/dashboard', function () {
        if (Auth::user()->role === 'recrutador') {
            return redirect()->route('recrutador.dashboard');
        }

        return redirect()->route('candidaturas.index');
    })->name('dashboard');

    // Rotas do Candidato (Aluno)
    Route::resource('candidaturas', CandidaturaController::class)->only(['index', 'store']);

    // Rotas do Recrutador (RH)
    Route::get('/recrutador/dashboard', [DashboardController::class, 'index'])->name('recrutador.dashboard');
    Route::put('/dashboard/candidaturas/{candidatura}', [DashboardController::class, 'update'])->name('recrutador.candidatura.update');

    // Rotas de Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
