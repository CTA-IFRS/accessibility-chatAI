<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser;
use EchoLabs\Prism\Ollama\Facades\Ollama;

class ImportarPdfsCommand extends Command
{
    protected $signature = 'app:importar-pdfs {abnt.pdf}';
    protected $description = 'Extrai texto de um PDF gera embeddings de 1024 e envia para o Pinecone';

    public function handle()
    {
        $caminho = $this->argument('abnt.pdf');

        if (!file_exists($caminho)) {
            $this->error("Arquivo não encontrado em: $caminho");
            return 1;
        }

        $this->info("Lendo o arquivo PDF...");
        $parser = new Parser();
        $pdf = $parser->parseFile($caminho);
        $textoCompleto = $pdf->getText();

        $chunks = str_split($textoCompleto, 1000); 
        $this->info("PDF dividido em " . count($chunks) . " pedaços. Gerando vetores...");

        $vectorsToSend = [];
        $nomeArquivo = basename($caminho);

        $ollamaUrl = config('services.ollama.host', 'http://localhost:11434');

        foreach ($chunks as $index => $chunk) {
            $chunk = mb_convert_encoding($chunk, 'UTF-8', 'UTF-8');
            $chunk = trim($chunk);
            if (empty($chunk)) continue;

            $embedResponse = Http::post($ollamaUrl . '/api/embed', [
                'model' => 'mxbai-embed-large',
                'input' => $chunk
            ]);

            if ($embedResponse->failed()) {
                $this->error("Falha ao gerar embedding para o bloco {$index}");
                return 1;
            }

            $embeddingsArray = $embedResponse->json('embeddings');
            $embedding = $embeddingsArray[0] ?? null;

            if (!$embedding) {
                $this->error("Ollma não retornou o vetor para o bloco {$index}");
                return 1;
            }

            $vectorsToSend[] = [
                'id' => md5($nomeArquivo . '_' . $index), 
                'values' => $embedding, 
                'metadata' => [
                    'text' => $chunk, 
                    'source' => $nomeArquivo,
                    'chunk_index' => $index
                ]
            ];
        }
        $this->info("Enviando dados para o Pinecone...");
        
        $pineconeResponse = Http::withHeaders([
            'Api-Key' => config('services.pinecone.key'),
            'Content-Type' => 'application/json',
        ])->post(config('services.pinecone.host') . '/vectors/upsert', [
            'vectors' => $vectorsToSend
        ]);

        if ($pineconeResponse->successful()) {
            $this->info("Sucesso");
            return 0;
        } else {
            $this->error("Erro ao enviar pro Pinecone: " . $pineconeResponse->body());
            return 1;
        }
    }
}