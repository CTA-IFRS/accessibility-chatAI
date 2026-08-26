<?php

namespace App\Services;

use App\Models\Conversa;
use Illuminate\Support\Facades\Auth;
use MongoDB\BSON\ObjectId;
use Illuminate\Http\Request;
use Cloudstudio\Ollama\Facades\Ollama;
class ConversaService
{
    public function getConversas()
    {
        $usuarioId = Auth::id();
        $conversas = Conversa::where('usuario_id', $usuarioId)
            ->orderByDesc('updated_at')
            ->get();
        return $conversas;
    }

    public function store($mensagem): Conversa
    {

        $response = Ollama::agent('Você gera títulos curtos e objetivos para conversas.')
            ->prompt("Esta é a primeira mensagem do usuário: \"{$mensagem}\". Gere um título de conversa curto (máx. 4 palavras) com base nela. Responda apenas com o título, sem aspas ou explicações.")
            ->model('llama3')
            ->ask();

        $titulo = trim($response['response']);

        return Conversa::create([
            'usuario_id' => Auth::id(),
            'titulo' => $titulo, 
            'mensagens' => []
        ]);
    }
    public function update(string $id, string $nome): bool
    {
        return (bool) Conversa::where('_id', $id)
            ->where('_id', $id)
            ->update(['titulo' => $nome]);
    }
    public function delete($id): void
    {
        Conversa::where('_id', $id)
            ->where('usuario_id', Auth::id())
            ->delete();

    }
}
