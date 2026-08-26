<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Conversa;
use Illuminate\Support\Facades\Http;
use EchoLabs\Prism\Ollama\OllamaServer;
use Livewire\Volt\Component;
use App\Services\ConversaService;
use App\Models\Mensagem;

class MensagemController extends Controller
{
    public function store(Request $request)
{
    if (!$request->conversa_id) {
        return response()->json(['erro' => 'sem conversa'], 400);
    }

    $conversa = Conversa::find($request->conversa_id);

    if (!$conversa) {
        return response()->json(['erro' => 'conversa nao encontrada'], 404);
    }

    if ($conversa->usuario_id !== auth()->id()) {
        return response()->json(['erro' => 'nao autorizado'], 403);
    }

    $conversa->mensagens()->create([
        'usuario_id' => auth()->id(),
        'resposta' => $request->bot,
        'msgUser' => $request->user,
    ]);

     $conversa->touch();
    return response()->json(['sucesso' => true]);
}
    public function getAll($id)
    {
        $conversa = Conversa::find($id);

        if (!$conversa) {
            return response()->json(['erro' => 'conversa nao encontrada'], 404);
        }

        if ($conversa->usuario_id !== auth()->id()) {
            return response()->json(['erro' => 'nao autorizado'], 403);
        }

        return response()->json([
            "msg" => $conversa->mensagens,
            "title" => $conversa->titulo
        ]);
    }

    public function perguntar(Request $request)
    {

        set_time_limit(0);
        $pergunta = $request->input('pergunta');
        $historico = $request->input('historico', []);

        $ollamaUrl = config('services.ollama.host', 'http://localhost:11434');

        try {
            $embedResponse = Http::timeout(60)->post($ollamaUrl . '/api/embed', [
                'model' => 'mxbai-embed-large',
                'input' => $pergunta
            ]);

            if ($embedResponse->failed()) {
                return response()->json(['error' => ['message' => 'Erro ao gerar embedding no Ollama.']], 500);
            }

            $embeddingsArray = $embedResponse->json('embeddings');
            $vetor = $embeddingsArray[0] ?? null;

            if (!$vetor) {
                return response()->json(['error' => ['message' => 'Nenhum vetor foi retornado pelo Ollama.']], 500);
            }

            $pineconeResponse = Http::withHeaders([
                'Api-Key' => config('services.pinecone.key'),
                'Content-Type' => 'application/json',
            ])->post(config('services.pinecone.host') . '/query', [
                        'vector' => $vetor,
                        'topK' => 10,
                        'includeMetadata' => true,
                    ]);

            if ($pineconeResponse->failed()) {
                return response()->json(['error' => ['message' => 'Erro ao consultar o banco Pinecone.']], 500);
            }

            $matches = $pineconeResponse->json('matches') ?? [];

            $contexto = collect($matches)
                ->pluck('metadata.text')
                ->filter()
                ->implode("\n---\n");

            $nome = auth()->check() ? auth()->user()->nome : 'Usuário';
            $user = explode(' ', $nome)[0];

            $mensagens = [
                [
                    'role' => 'system',
                    'content' => "Você é um assistente virtual atencioso. Seu interlocutor se chama {$user}.\n\n" .
                        "DIRETRIZES DE RESPOSTA:\n" .
                        "1. Responda exclusivamente com base no conteúdo presente em <contexto>.\n" .
                        "2. Não utilize nenhum conhecimento prévio ou externo.\n" .
                        "3. Se a pergunta do usuário não puder ser respondida com as informações do <contexto>, informe de maneira cortês e natural que não possui essa informação no momento, sem mencionar 'contexto', 'regras' ou 'sistema'.\n\n" .
                        "<contexto>\n" .
                        "{$contexto}\n" .
                        "</contexto>" .
                        "4. Responda sempre em português Brasileiro."
                ],
                ...$historico,
                ['role' => 'user', 'content' => $pergunta],
            ];

            $ollamaChatResponse = Http::timeout(180)->post($ollamaUrl . '/v1/chat/completions', [
                'model' => config('services.ollama.model', 'llama3'),
                'messages' => $mensagens,
                'temperature' => 0.1,
            ]);

            if ($ollamaChatResponse->failed()) {
                return response()->json(['error' => ['message' => 'Erro ao consultar o modelo no Ollama.']], 500);
            }

            return response()->json($ollamaChatResponse->json());

        } catch (\Exception $e) {
            return response()->json(['error' => ['message' => 'Erro interno no servidor: ' . $e->getMessage()]], 500);
        }
    }

    public function edit(Request $request)
    {
        $conversa = Conversa::find($request->conversa);

        if (!$conversa) {
            return response()->json(['erro' => 'conversa nao encontrada'], 404);
        }

        if ($conversa->usuario_id !== Auth::id()) {
            return response()->json(['erro' => 'nao autorizado'], 403);
        }

        $msg = $conversa->mensagens()->find($request->msg_id);

        if (!$msg) {
            return response()->json(['erro' => 'mensagem nao encontrada'], 404);
        }

        $new = $request->input('novo');

        $remover = $conversa->mensagens()
            ->where('created_at', '>=', $msg->created_at);

        foreach ($remover as $mensagem) {
            $mensagem->delete();
        }

        $request->merge(['pergunta' => $new]);

        return $this->perguntar($request);
    }

}