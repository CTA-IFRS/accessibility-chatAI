# Frontend atual

## Views

As views Blade estão em `resources/views/`.

- `welcome.blade.php` apresenta a entrada do sistema, com autenticação Google e acesso visitante.
- `chat.blade.php` define a tela principal: sidebar, lista de conversas, área `#botroot`, título, formulário `#form`, campo `#pergunta` e botão de envio.
- `profile.blade.php` contém edição do nome via `PUT /profile/edit` e confirmação de exclusão via `DELETE /delete`.

A sidebar de `chat.blade.php` usa `Auth::check()` para mostrar novo chat, configurações, logout e nome do usuário ou, para visitantes, o link de login. A view inclui o token CSRF em meta tag e carrega diretamente `/css/style.css` e `/js/script.js`.

## JavaScript

`public/js/script.js` mantém os estados `historico`, `conversaAtiva`, `controller`, `isGenerating` e `load`.

### Mensagens

- `msg()` escolhe uma frase inicial.
- `criarMensagem()` cria mensagens de usuário ou bot e adiciona copiar, leitura por voz para bot e edição para usuário.
- `adicionarMensagem()` insere a mensagem em `#botroot` e rola a área.
- `limparchat()`, `animacao()` e `fecharAnimacao()` controlam a área de conversa e o indicador de carregamento.
- `stop()` aborta uma requisição por `AbortController`.

### Requisições

| Função | Requisição | Finalidade |
|---|---|---|
| `enviar()` | `POST /perguntar` | envia pergunta e histórico à IA |
| `salvarMsg()` | `POST /mensagens/store` | persiste pergunta e resposta |
| `novaConversa()` | `POST /conversas/store` | cria conversa |
| `carregar()` | `GET /conversas` | preenche a sidebar |
| `carregarMsg()` | `GET /mensagens/{id}/getall` | carrega título e mensagens |
| `editMsg()` | `POST /mensagens/edit` | regenera resposta após edição |
| `deleteConversa()` | `DELETE /conversas/{id}` | exclui conversa |
| `editConversa()` | `PUT /conversas/edit/{id}` | altera título |

As requisições enviam `X-CSRF-TOKEN` a partir da meta tag quando aplicável. `enviar()` espera `choices[0].message.content` na resposta da IA. `carregarMsg()` espera `title` e `msg`; `carregar()` espera `conversas`.

### Eventos e estados visuais

O script trata submit e Enter sem Shift no formulário, clique no botão de envio/parada, entrada no campo de pergunta, seleção de conversa, novo chat, fechamento da sidebar e menu mobile. Classes como `active`, `loaded`, `small`, `open` e `show` representam estados de seleção, carregamento, sidebar e toast.

Modais de edição e exclusão de conversa são criados dinamicamente com as classes `.modal-fundo` e `.modal-caixa`. O perfil usa o elemento HTML `<dialog id="delete-account">`.

## CSS e responsividade

`public/css/style.css` concentra os estilos do chat, sidebar, mensagens, formulários, modais, toasts, login e perfil. Também define o fundo visual com montanhas usando `clip-path`.

Em telas de até `1000px`, a sidebar fica fora da tela e é exibida com a classe `open`; o botão de menu mobile aparece, o chat ocupa a largura disponível e modais/toasts usam quase toda a largura. Campos e botões recebem dimensões próprias para telas menores.

## Toasts

`public/js/load.js` implementa `showToast()` e `closeToast()`. O toast recebe a classe `show` e é ocultado após cinco segundos. As views renderizam mensagens de sessão condicionalmente.

## Dependências e pontos de atenção

O frontend usa `fetch`, `AbortController`, `navigator.clipboard`, `speechSynthesis`, Font Awesome, Bootstrap Icons e o carregador externo incluído na view. As páginas observadas carregam arquivos de `public/` diretamente; não foi determinado pelo código das views se `resources/js/app.js`, `resources/css/app.css` e o bundle Vite são usados no fluxo principal.

`profile.blade.php` inclui `script.js`, embora a página não contenha os elementos centrais do chat. O efeito exato dessa combinação em execução não foi testado. `welcome.blade.php` e `profile.blade.php` também exibem mensagens de sessão com chaves que não correspondem sempre às chaves definidas pelo controller; a divergência é observável, mas o texto pretendido não é determinado pelo código.
