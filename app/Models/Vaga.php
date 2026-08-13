<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vaga extends Model
{
    protected $fillable = [
        'titulo',
        'nivel',
        'descricao_requisitos',
        'ativa',
    ];

    public function candidaturas(){
        return $this->hasMany(Candidatura::class);
    }
}
