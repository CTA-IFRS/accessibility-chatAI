<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mensagem extends Model
{
protected $fillable = [
    'usuario_id',
    'msgUser',
    'resposta',
];

public function usuario(): BelongsTo
{
    return $this->belongsTo(Usuario::class);
}

}
