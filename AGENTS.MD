# AGENTS.md

## Objetivo

A documentação deve representar o comportamento real do sistema, tendo o código-fonte como fonte primária da verdade.

## Fontes da verdade

As fontes oficiais são:

- Backend: `App/`
- Frontend: `public/`

A documentação deve ser armazenada exclusivamente em:

- `documentations/`

Arquivos fora dessas áreas não devem ser considerados fonte primária para descrever comportamento da aplicação, exceto quando necessário para complementar uma informação explicitamente.

## Regra fundamental

Nunca invente comportamento.

Se uma informação não puder ser comprovada pelo código, questione-me para revisar.


Não transforme suposições em fatos, questione-me para revisar.

## Prioridade das fontes

Em caso de conflito:

1. Código em `App/`
2. Código em `public/`
3. Configurações e rotas do projeto
4. Testes automatizados
5. Documentação existente

A documentação existente nunca deve ser considerada mais confiável que o código.

## Backend

Ao documentar o backend, analisar quando aplicável:

- Controllers
- Models
- Services
- Requests
- Middleware
- Policies
- Jobs
- Events
- Listeners
- Notifications
- Commands
- Providers
- Exceptions
- Autenticação
- Autorização
- Fluxos entre classes
- Persistência de dados
- Integrações
- APIs
- Validações
- Tratamento de erros

## Frontend

Ao documentar o frontend, analisar:

- JavaScript
- CSS
- HTML quando disponível
- Manipulação do DOM
- Eventos
- Formulários
- AJAX/fetch
- Comunicação com backend
- Componentes visuais
- Estados da interface
- Sidebar
- Modais
- Menus
- Interações
- Responsividade
- Dependências entre scripts

## Documentações

Toda documentação produzida deve ser salva dentro de:

`documentations/`

Não criar documentação definitiva em:

- `/tmp`
- `/docs`
- `/notes`
- `/analysis`
- arquivos soltos na raiz

## Estilo

A documentação deve ser:

- objetiva
- técnica
- verificável
- organizada
- escrita em Markdown
- adequada para desenvolvedores que não conhecem previamente o projeto

Evite textos genéricos.

Sempre que possível, explique:

- O que existe
- Onde existe
- Como funciona
- Quem chama
- O que chama
- Quais dados entram
- Quais dados saem
- Quais dependências existem
- Quais efeitos colaterais existem

## Referências

Sempre informe os caminhos dos arquivos relevantes.

Exemplo:

`App/Http/Controllers/AuthController.php`

Quando possível, informe também classe e método:

`AuthController@login()`

## Atualização

A documentação deve ser incremental.

Não reescrever documentos inteiros sem necessidade.

Ao encontrar uma alteração:

1. Identifique o documento afetado.
2. Compare com o código atual.
3. Questione-me caso forem necessárias atualizações.
4. Preserve informações válidas.
5. Remova informações comprovadamente obsoletas da documentação.

## Segurança

Não documentar:

- senhas
- tokens
- secrets
- chaves privadas
- credenciais
- valores sensíveis de `.env`

Se forem encontrados, apenas registrar que existe uma configuração sensível, sem expor seu valor.

## Incerteza

Quando o código não permitir determinar algo:

`Status: não determinado`

Nunca preencher lacunas com suposições.
Questionar-me para complementar a informação.

## Critério de conclusão

Uma tarefa de documentação só está concluída quando:

- os arquivos relevantes foram analisados;
- a documentação foi atualizada;
- os caminhos das fontes foram registrados;
- contradições foram verificadas;
- informações não comprovadas foram marcadas;
- nenhuma alteração no código da aplicação foi realizada.

## Restrição principal

Os agentes de documentação NÃO devem modificar o comportamento da aplicação.

Eles podem criar ou modificar arquivos dentro de:

`documentations/`

e arquivos explicitamente pertencentes à infraestrutura de documentação.

Nunca modificar `App/` ou `public/` durante uma tarefa de documentação.