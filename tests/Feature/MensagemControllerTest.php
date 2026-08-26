<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MensagemControllerTest extends TestCase
{
    public function test_perguntar_retorna_a_resposta_do_modelo_com_contexto_e_historico(): void
    {
        config([
            'services.ollama.host' => 'http://ollama.test',
            'services.ollama.model' => 'modelo-teste',
            'services.pinecone.host' => 'http://pinecone.test',
        ]);

        Http::fake([
            'http://ollama.test/api/embed' => Http::response([
                'embeddings' => [[0.1, 0.2]],
            ]),
            'http://pinecone.test/query' => Http::response([
                'matches' => [
                    ['metadata' => ['text' => 'Informacao recuperada']],
                ],
            ]),
            'http://ollama.test/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => 'Resposta']]],
            ]),
        ]);

        $response = $this->postJson('/perguntar', [
            'pergunta' => 'Qual e a informacao?',
            'historico' => [
                ['role' => 'user', 'content' => 'Pergunta anterior'],
            ],
        ]);

        $response->assertOk()->assertJson([
            'choices' => [['message' => ['content' => 'Resposta']]],
        ]);

        Http::assertSent(function ($request) {
            if ($request->url() !== 'http://ollama.test/v1/chat/completions') {
                return false;
            }

            $messages = $request->data()['messages'];

            return $request->data()['model'] === 'modelo-teste'
                && $request->data()['temperature'] === 0.1
                && $messages[1] === ['role' => 'user', 'content' => 'Pergunta anterior']
                && $messages[2] === ['role' => 'user', 'content' => 'Qual e a informacao?']
                && str_contains($messages[0]['content'], 'Informacao recuperada');
        });
    }

    public function test_perguntar_retorna_erro_quando_embedding_falha(): void
    {
        config(['services.ollama.host' => 'http://ollama.test']);
        Http::fake([
            'http://ollama.test/api/embed' => Http::response([], 503),
        ]);

        $response = $this->postJson('/perguntar', ['pergunta' => 'Pergunta']);

        $response->assertStatus(500)->assertJson([
            'error' => ['message' => 'Erro ao gerar embedding no Ollama.'],
        ]);
    }

    public function test_perguntar_retorna_erro_quando_embedding_nao_tem_vetor(): void
    {
        config(['services.ollama.host' => 'http://ollama.test']);
        Http::fake([
            'http://ollama.test/api/embed' => Http::response(['embeddings' => []]),
        ]);

        $response = $this->postJson('/perguntar', ['pergunta' => 'Pergunta']);

        $response->assertStatus(500)->assertJson([
            'error' => ['message' => 'Nenhum vetor foi retornado pelo Ollama.'],
        ]);
    }

    public function test_perguntar_retorna_erro_quando_pinecone_falha(): void
    {
        config([
            'services.ollama.host' => 'http://ollama.test',
            'services.pinecone.host' => 'http://pinecone.test',
        ]);
        Http::fake([
            'http://ollama.test/api/embed' => Http::response(['embeddings' => [[0.1]]]),
            'http://pinecone.test/query' => Http::response([], 500),
        ]);

        $response = $this->postJson('/perguntar', ['pergunta' => 'Pergunta']);

        $response->assertStatus(500)->assertJson([
            'error' => ['message' => 'Erro ao consultar o banco Pinecone.'],
        ]);
    }

    public function test_perguntar_retorna_erro_quando_modelo_falha(): void
    {
        config([
            'services.ollama.host' => 'http://ollama.test',
            'services.pinecone.host' => 'http://pinecone.test',
        ]);
        Http::fake([
            'http://ollama.test/api/embed' => Http::response(['embeddings' => [[0.1]]]),
            'http://pinecone.test/query' => Http::response(['matches' => []]),
            'http://ollama.test/v1/chat/completions' => Http::response([], 500),
        ]);

        $response = $this->postJson('/perguntar', ['pergunta' => 'Pergunta']);

        $response->assertStatus(500)->assertJson([
            'error' => ['message' => 'Erro ao consultar o modelo no Ollama.'],
        ]);
    }
}