//introdução

    "Ceci" é o nome provisório deste sistema, cuja função é ser um chatbot especializado na área de acessiblidade. 

//Tecnologias e preparo do ambiente

    Este sistema utiliza de: Laravel, MongoDB, javascript e pinecone, além de ter como mecanismo principal as chamadas ao Ollama local.

    **Os comandos necessários para o uso do sistema são:**
    npm i
    composer install

    O sistema conta, inicialmente, com a ABNT da acessibilidade e diretrizes de acessbilidade no banco de dados do pinecone, que é o que alimenta a inteligência artificial. Para acrescentar outros, ponha o pdf na raiz do projeto e então rode o comando "php artisan app:importar-pdfs {nomedoarquivo.pdf}"


//melhorias necessárias/sugestões:

    O projeto, apesar de bem encaminhado, precisa de pequenos detalhes para ser considerado finalizado, sendo eles principalmente:


        1- Versão mobile funcional, corrigindo os erros de responsitividade (style.css e script.js). 

        2- Troca do modelo de IA para algum de alta capacidade, aprimorando as respostas. Atualmente é utilizado o llama3 apenas para desenvolvimento pela sua velocidade. Para trocar a requisição e recebimento de dados, as alterações são feitas nos arquivos script.js, conversaService.php e MensagemController.php. A linha 6 do arquivo Ollama-laravel.php é quem define que modelo será utilizado, então precisa mudar lá também;
       
        3- Extinção do arquivo ConversaService.php, migrando as funções para conversaController.php, pois este arquivo só existe por boas práticas de desenvolvimento no livewire, biblioteca essa que não é mais utilizada no código.
        
        4- Correção de bugs de fluxo e otimização, especialmente após interromper a resposta, o que causa um micro-travamento nas requisições. Talvez um servidor com suporte à multi-request possa resolver, mas não há garantia de que aconteça. 
        
        5- Credenciais do Google oAuth: Para editar as URLs de redirecionamento e incluir o código no .env, é necessário acessar o cloud console da google(https://console.cloud.google.com/). Caso não for desejado trocar as credenciais, é necessário entrar em contato comigo(Vicenzo) para permitir o acesso e edição do projeto.
        
        6- O sistema de histórico da conversa enviado ao modelo de IA não é o recomendado e pode causar lentidão e erros em conversas longas. Seria interessante trocar a metodologia com que é feito para evitar problemas maiores de funcionamento no futuro.
        
        7- Padronização da UI para o predefinido no design service dos sites do CTA.

        8- O botão de Enviar é escondido com Opacity 0, então ainda é clicável enquanto escondido. Adicionar um display:none seria bom. 

        9- Camadas extra de segurança nas requisições(Diretório Controller). Já existem algumas barreiras, mas ainda há brechas.

        10- Depois que o usuário se loga, o /chat já é aberto conectado em sua conta. Porém, quando acessa a rota inicial(welcome.blade.php), ele não é redirecionado imediatamente após o acesso se já tivesse se logado. Talvez fosse bom incrementar isso.

        11- Memória avançada para a IA, fazendo ela se lembrar do usuário e ter comportamento personalizado.

        12- confirmação de deletar conta


//revisão da IA sobre o código:


Alta: edição de conversa não verifica usuario_id.
Alta: parâmetro {id} da rota de edição é ignorado.
Alta: rotas apontam para storebtn(), show() e destroy(), métodos inexistentes.
Alta: OAuth usa stateless(), sem proteção de estado OAuth.
Alta: migrations SQL são incompatíveis com os models MongoDB.
Média: /perguntar é público, caro e sem rate limiting ou limites de entrada.
Média: ausência de validação Laravel nas entradas.
Média: configuração do Ollama está inconsistente.
Média: edição de mensagem pode apagar histórico antes de uma regeneração falhar.
Média: título de conversa é inserido via innerHTML, com risco de XSS.
Média: mensagens internas de exceção são expostas ao cliente.
Baixa: script.js é carregado na página de perfil e acessa elementos inexistentes.
Baixa: mensagem de sucesso do perfil usa chave incorreta na sessão.
Laravel

Os pontos mais relevantes são:

autorização implementada manualmente e incompleta;
ausência de Form Requests;
handlers de rotas inexistentes;
incompatibilidade entre models MongoDB e migrations relacionais;
cache e fila configurados com drivers SQL enquanto a conexão efetiva é MongoDB;
autenticação Google com stateless() sem estado OAuth;
ausência de testes para os fluxos protegidos.