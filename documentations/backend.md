# Backend atual

## Rotas e controllers

As rotas estão em `routes/web.php`.

| Método e caminho | Controller | Proteção |
|---|---|---|
| `GET /` | closure, view `welcome` | pública |
| `GET /chat` | `ConversaController@index()` | pública |
| `GET /guest` | `AuthController@guest()` | pública |
| `GET /gear` | closure, view `profile` | `auth` |
| `GET /auth/redirect` | `AuthController@login()` | pública |
| `GET /auth/callback` | `AuthController@callback()` | pública |
| `POST /logout` | `AuthController@logout()` | `auth` |
| `GET /conversas` | `ConversaController@getAll()` | `auth` |
| `POST /conversas/store` | `ConversaController@store()` | `auth` |
| `POST /conversas/btnstore` | `ConversaController@storebtn()` | `auth` |
| `PUT /conversas/edit/{id}` | `ConversaController@edit()` | `auth` |
| `DELETE /conversas/{id}` | `ConversaController@destroy()` | `auth` |
| `GET /conversas/show/{id}` | `ConversaController@show()` | `auth` |
| `GET /conversas/refresh` | `ConversaController@reload()` | pública |
| `DELETE /delete` | `AuthController@delete()` | `auth` |
| `PUT /profile/edit` | `AuthController@update()` | `auth` |
| `POST /mensagens/store` | `MensagemController@store()` | `auth` |
| `GET /mensagens/{id}/getall` | `MensagemController@getAll()` | `auth` |
| `DELETE /mensagens/{id}` | `MensagemController@destroy()` | `auth` |
| `POST /perguntar` | `MensagemController@perguntar()` | pública |
| `POST /mensagens/edit` | `MensagemController@edit()` | `auth` |

As rotas `storebtn`, `show` e `destroy` de mensagens estão registradas, mas os métodos correspondentes não aparecem nos controllers atuais.

## Autenticação e perfil

`AuthController` usa Socialite com o driver Google em `login()` e `callback()`. O usuário é procurado por `google_id` e criado com `google_id`, `nome` e `email` quando não existe. `logout()` encerra a autenticação; `guest()` também encerra a sessão antes de redirecionar ao chat; `delete()` remove o usuário autenticado; `update()` altera `nome` a partir de `username`.

`app/Models/Usuario.php` estende o usuário autenticável MongoDB, usa a conexão `mongodb`, a coleção `usuarios`, chave primária `_id` e permite preenchimento de `nome`, `email` e `google_id`. `config/auth.php` define esse modelo para o guard web.

## Conversas

`ConversaController@index()` obtém conversas com `ConversaService@getConversas()` e renderiza `chat`. `getAll()` devolve JSON com `conversas`. `store()` recebe `titulo`, delega a criação ao serviço e retorna o `id`. `edit()` recebe nome e identificador no corpo e delega a atualização. `destroy()` delega a exclusão. `reload()` redireciona para `/chat`.

`ConversaService` filtra por `Auth::id()`, ordena por `updated_at` decrescente, cria títulos usando a facade Ollama e cria a conversa com `usuario_id`, `titulo` e `mensagens` vazias. `update()` atualiza `titulo` por `_id`; `delete()` exclui por `_id` e usuário autenticado.

`app/Models/Conversa.php` usa MongoDB, tabela/coleção `conversas`, chave string e relação `embedsMany(Mensagem::class)`.

## Mensagens e IA

`MensagemController@store()` exige `conversa_id`, localiza a conversa, verifica o proprietário e cria uma mensagem embutida com `usuario_id`, `resposta` e `msgUser`; depois atualiza o timestamp da conversa.

`getAll()` aplica a mesma verificação de proprietário e retorna `msg` e `title`. `edit()` localiza a conversa e a mensagem, remove mensagens a partir do timestamp da mensagem editada, substitui a pergunta no request e chama `perguntar()` novamente.

`perguntar()`:

- recebe `pergunta` e `historico`;
- gera embedding `mxbai-embed-large` no Ollama;
- consulta os dez melhores resultados no Pinecone com metadados;
- monta um prompt de sistema em português brasileiro com o contexto recuperado;
- chama `/v1/chat/completions` no Ollama com temperatura `0.1`;
- retorna o JSON do serviço ou erros HTTP `500` para falhas de embedding, consulta, resposta vazia ou exceção.

O endpoint `/perguntar` é público no arquivo de rotas. O acesso a persistência de mensagens exige autenticação.

## Ingestão

`ImportarPdfsCommand@handle()` verifica o caminho, extrai texto com `Smalot\PdfParser`, divide em blocos, gera embeddings e faz upsert no Pinecone. Falhas de arquivo, embedding ou envio retornam código `1`; sucesso retorna `0`.

## Persistência e configurações

`config/database.php` declara SQLite como fallback padrão, além de MySQL, MariaDB, PostgreSQL, SQL Server e MongoDB. As credenciais e valores efetivos vêm do ambiente e não são documentados.

As migrações em `database/migrations/` criam tabelas relacionais para `users`, `mensagens`, cache, jobs e sessões. Não há migração observada para `usuarios` ou `conversas`, que são as coleções usadas pelos modelos principais. `Mensagem` também declara um relacionamento Eloquent com `Usuario`, enquanto `Conversa` o utiliza como documento embutido.

`config/services.php` contém configurações para Google, Pinecone, Groq e Qdrant. O código analisado usa Google e Pinecone; não foi localizada utilização de Groq ou Qdrant em `app/`.

**Status: não determinado:** valores reais de ambiente, serviços disponíveis e conexão efetiva usada fora dos fallbacks de configuração.
