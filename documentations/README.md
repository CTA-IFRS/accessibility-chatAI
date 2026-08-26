# Documentação do sistema

Documentação baseada no código atual do projeto. As fontes primárias são `app/`, `public/`, `resources/views/`, `routes/`, `config/` e `database/`.

## Documentos

- [Arquitetura](architecture.md): visão geral dos componentes e fluxos.
- [Backend](backend.md): rotas, controllers, serviços, modelos, persistência e integrações.
- [Frontend](frontend.md): views Blade, JavaScript, CSS e comunicação HTTP.

## Critério de leitura

A documentação descreve somente comportamentos verificáveis no código. Configurações fornecidas por ambiente, como credenciais, URLs efetivas e conexões em uso, estão marcadas como **Status: não determinado**. Divergências entre arquivos são registradas como inconsistências observáveis, sem assumir qual comportamento seria pretendido.

> Observação: `AGENTS.MD` define `documentations/` como destino oficial. O repositório também possui `documentation/README.md`, que está vazio; os documentos definitivos desta tarefa foram criados em `documentations/`.
