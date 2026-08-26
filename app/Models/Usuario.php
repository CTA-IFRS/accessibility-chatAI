<?php

namespace App\Models;

use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $connection = 'mongodb';
    
    protected $collection = 'usuarios';

    protected $primaryKey = '_id';

    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'nome',
        'email',
        'google_id',
    ];
}