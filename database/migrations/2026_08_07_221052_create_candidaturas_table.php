<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidaturas', function (Blueprint $table) {
            $table->id();

            // Chaves Estrangeiras
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('vaga_id')->nullable()->constrained('vagas')->nullOnDelete();

            // Dados do Candidato & Formulário
            $table->string('area_interesse')->nullable();
            $table->json('respostas_questionario')->nullable();
            $table->string('caminho_pdf');
            $table->longText('texto_extraido')->nullable();

            // Análise da IA (Visível para o Recrutador)
            $table->enum('nivel_sugerido_ia', ['basico', 'tecnico', 'avancado'])->nullable();
            $table->integer('nota_match')->nullable();
            $table->text('resumo_ia')->nullable();

            // Status e Acompanhamento
            $table->enum('status', [
                'aguardando_retorno',
                'entrevista_agendada',
                'finalizado',
            ])->default('aguardando_retorno');

            // Agendamento e Feedback do Recrutador
            $table->dateTime('data_entrevista')->nullable();
            $table->text('feedback_recrutador')->nullable();
            $table->string('local_entrevista')->nullable()->after('data_entrevista');
            $table->json('trilha_links')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidaturas');
    }
};
