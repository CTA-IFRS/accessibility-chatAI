<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Conversa extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'conversas';
    protected $keyType = 'string';
    protected $fillable = [
        'usuario_id',
        'titulo',
    ];
   
    public function mensagens()
    {
        return $this->embedsMany(Mensagem::class);
    }

}
