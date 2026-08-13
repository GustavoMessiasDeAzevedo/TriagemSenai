<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidatura extends Model
{
    protected $fillable = [
        'user_id',
        'vaga_id',
        'caminho_pdf',
        'texto_extraido',
        'nivel_sugerido_ia',
        'nota_match',
        'resumo_ia',
        'status',
        'data_entrevista',
        'feedback_recrutador',
        'trilha_links',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vaga()
    {
        return $this->belongsTo(Vaga::class);
    }
}
