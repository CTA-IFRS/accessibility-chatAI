// ==========================================
const botroot = document.getElementById('botroot');
const input = document.getElementById('pergunta');
const form = document.getElementById('form');
const sidebar = document.getElementById('sidebar');
const close = document.querySelector('.close');
const conversas = document.getElementById('lista');
const logo = document.querySelector('.logo');
const novo = document.getElementById('novo');
const newchat = document.getElementById('new');
const opcoes = document.getElementById('user-profile');
const name = document.querySelector('.user-name');
const warn = document.querySelector('.warn');
const send = document.querySelector('.enviar');
let initText = document.querySelector('.initText');
let chatTitle = document.querySelector('.chattitle');

const url = "http://127.0.0.1:8000/";
const historico = [];
let conversaAtiva = null;
let controller = null;
let isGenerating = false;
let load = false;

const frases = [
    "Por onde começamos hoje?",
    "O que vamos resolver agora?",
    "Estou pronta. Qual é o plano de hoje?",
    "Em que posso te ajudar a avançar neste momento?",
    "Qual é a grande ideia que vamos explorar hoje?",
    "Olá! O que está na sua mente hoje?",
    "Tudo pronto por aqui. O que temos para hoje?",
    "Oi! Como posso facilitar o seu dia agora?",
    "Espaço aberto! O que você quer conversar?",
    "Selecione um tópico ou digite sua dúvida para começarmos.",
];

// ==========================================
// 2. FUNÇÕES UTILITÁRIAS E INTERFACE
// ==========================================
function msg() {
    if (initText) {
        initText.innerText = frases[Math.floor(Math.random() * frases.length)];
    }
}

function stop() {
    if (controller) {
        controller.abort();
    }
}

function limparchat() {
    if (botroot) {
        botroot.innerHTML = '';
    }
}

function animacao() {
    const loadElement = document.createElement('div');
    loadElement.innerText = "Carregando...";
    loadElement.classList.add('load');
    botroot.appendChild(loadElement);
}

function fecharAnimacao() {
    const loadElement = document.querySelector('.load');
    if (loadElement) {
        loadElement.remove();
    }
}


function marcarConversaAtiva(conversaId) {
    const chat = document.querySelector(`.conversa[data-id="${conversaId}"]`);
    if (!chat) return;

    document.querySelectorAll('.conversa').forEach(c => c.classList.remove('active'));
    chat.classList.add('active');
    setTimeout(() => chat.classList.add('loaded'), 10);

    conversaAtiva = conversaId;
}

function navigate(conversaId) {
    const chat = document.querySelector(`.conversa[data-id="${conversaId}"]`);
    if (!chat) return;

    stop();

    document.querySelectorAll('.conversa').forEach(c => c.classList.remove('active'));
    chat.classList.add('active');
    setTimeout(() => chat.classList.add('loaded'), 10);

    if (initText) initText.style.display = 'none';
    limparchat();
    historico.length = 0;

    conversaAtiva = conversaId;
    carregarMsg(conversaId);
}

function criarMensagem(texto, tipo, id = null) {
    const container = document.createElement('div');
    container.classList.add('msg-container', tipo);

    const msgDiv = document.createElement('div');
    msgDiv.classList.add(tipo === 'user' ? 'usermsg' : 'botmsg');
    msgDiv.textContent = texto;
    if (id) msgDiv.dataset.id = id;

    const iconsWrapper = document.createElement('div');
    iconsWrapper.classList.add('icons-wrapper');

    const copy = document.createElement('div');
    copy.classList.add('copy');
    copy.innerHTML = '<i class="fa-regular fa-copy"></i>';
    copy.addEventListener('click', function () {
        navigator.clipboard.writeText(texto);
        copy.innerHTML = '<i class="fa-solid fa-check"></i>';
        setTimeout(() => copy.innerHTML = '<i class="fa-regular fa-copy"></i>', 2000);
    });
    iconsWrapper.appendChild(copy);

    if (tipo === 'bot') {
        const speaker = document.createElement('div');
        speaker.classList.add('speaker');
        speaker.innerHTML = '<i class="fa-solid fa-volume-high"></i>';
        speaker.addEventListener('click', function () {
            speechSynthesis.cancel();
            const utterance = new SpeechSynthesisUtterance(texto);
            utterance.lang = 'pt-BR';
            speechSynthesis.speak(utterance);
        });
        iconsWrapper.appendChild(speaker);
    }

    if (tipo === 'user') {
        const edit = document.createElement('div');
        edit.classList.add('editmsg');
        edit.innerHTML = '<i class="fa-regular fa-pen-to-square"></i>';
        edit.addEventListener('click', () => editMsg(msgDiv));
        iconsWrapper.appendChild(edit);
    }

    container.appendChild(msgDiv);
    container.appendChild(iconsWrapper);

    return container;
}


function adicionarMensagem(texto, tipo, id = null) {
    const div = criarMensagem(texto, tipo, id);
    botroot.appendChild(div);
    botroot.scrollTop = botroot.scrollHeight;
    return div;
}

// ==========================================
// 3. REQUISIÇÕES HTTP / API
// ==========================================
async function enviar() {
    if (initText) initText.style.display = 'none';
    const mensagem = input.value.trim();
    if (!mensagem) return;

    adicionarMensagem(mensagem, 'user');
    historico.push({ role: 'user', content: mensagem });
    const conversaIdAtual = conversaAtiva;
    input.value = '';

    isGenerating = true;
    send.innerHTML = '<i class="fa-regular fa-square"></i>';
    animacao();

    controller = new AbortController();

    try {
        const response = await fetch('/perguntar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ pergunta: mensagem, historico }),
            signal: controller.signal
        });

        const data = await response.json();
        if (data.error) throw new Error(data.error.message);

        const resposta = data.choices[0].message.content;

        if (!resposta || !resposta.trim()) {
            historico.pop();
            adicionarMensagem('A IA não conseguiu gerar uma resposta. Tente novamente.', 'bot');
            return;
        }

        historico.push({ role: 'assistant', content: resposta });
        adicionarMensagem(resposta, 'bot');

        if (conversaIdAtual) {
            await salvarMsg(mensagem, resposta, conversaIdAtual);
        } else {
            const novaCvs = await novaConversa(mensagem);
            conversaAtiva = novaCvs.id;
            await salvarMsg(mensagem, resposta, conversaAtiva);
            await carregar();
            marcarConversaAtiva(conversaAtiva);
        }

    } catch (error) {
        if (error.name === 'AbortError') {

            const cvs = conversaAtiva === conversaIdAtual

            if (cvs) {
                adicionarMensagem('Geração interrompida.', 'bot');
                historico.pop();
            }
            console.log('Requisição cancelada pelo usuário.');
            return;
        }
        console.error('Erro:', error);
        adicionarMensagem('Erro ao conectar com a IA.', 'bot');
    } finally {
        fecharAnimacao();
        controller = null;
        isGenerating = false;
        send.innerHTML = '<i class="fa-regular fa-paper-plane"></i>';
        send.style.opacity = input.value.trim() ? 1 : 0;
    }
}

function editMsg(div) {
    const containerPai = div.closest('.msg-container') || div;

    const wrapper = document.createElement('div');
    wrapper.classList.add('edit-msg-wrapper');
    const id = div.dataset.id;

    const mensagensDom = Array.from(botroot.children).filter(
        el => el.classList.contains('msg-container')
    );
    const indice = mensagensDom.indexOf(containerPai);

    const textarea = document.createElement('textarea');
    textarea.classList.add('edit-msg');
    const valor = div.textContent;
    textarea.value = valor;

    const voltar = document.createElement('div');
    voltar.innerHTML = '<i class="fa-solid fa-left-long"></i> Cancelar';
    voltar.classList.add('back');

    const confirm = document.createElement('div');
    confirm.innerHTML = 'Salvar';
    confirm.classList.add('confirm');

    const options = document.createElement('div');
    options.classList.add('options');
    options.appendChild(voltar);
    options.appendChild(confirm);

    wrapper.appendChild(textarea);
    wrapper.appendChild(options);

    containerPai.replaceWith(wrapper);

    voltar.addEventListener('click', function () {
        const msg = criarMensagem(valor, 'user', id);
        wrapper.replaceWith(msg);
    });

    confirm.addEventListener('click', async function () {
        const novoTexto = textarea.value.trim();
        if (!novoTexto) return;

        const msgAtualizada = criarMensagem(novoTexto, 'user', id);
        wrapper.replaceWith(msgAtualizada);

        let proximo = msgAtualizada.nextElementSibling;
        while (proximo) {
            const remover = proximo;
            proximo = proximo.nextElementSibling;
            remover.remove();
        }

        historico.length = indice;
        historico.push({ role: 'user', content: novoTexto });

        botroot.scrollTop = botroot.scrollHeight;
        animacao();

        try {
            const resposta = await fetch('/mensagens/edit', {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ msg_id: id, novo: novoTexto, conversa: conversaAtiva }),
            });

            if (!resposta.ok) throw new Error('Falha ao editar mensagem');

            const data = await resposta.json();
            if (data.error) throw new Error(data.error.message);

            const respostaBot = data.choices[0].message.content;

            historico.push({ role: 'assistant', content: respostaBot });
            adicionarMensagem(respostaBot, 'bot', id);

            await salvarMsg(novoTexto, respostaBot, conversaAtiva);

        } catch (error) {
            console.error('Erro ao editar mensagem:', error);
            adicionarMensagem('Erro ao editar a mensagem. Tente novamente.', 'bot');
        } finally {
            fecharAnimacao();
        }
    });
}

async function carregarMsg(conversaId) {
    if (load) return;

    try {
        load = true;
        const resposta = await fetch(`/mensagens/${conversaId}/getall`, {
            method: "GET",
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },

        });

        if (!resposta.ok) throw new Error('Falha ao carregar mensagens da conversa');

        const response = await resposta.json();

        if (!response || !Array.isArray(response.msg)) {
            throw new Error('Resposta inválida ao carregar mensagens.');
        }

        chatTitle.innerText = response.title;
        document.title = response.title;

        response.msg.forEach(mensagem => {
            if (!mensagem.msgUser || !mensagem.msgUser.trim()) return;

            adicionarMensagem(mensagem.msgUser, 'user', mensagem._id || mensagem.id);
            historico.push({ role: 'user', content: mensagem.msgUser });

            if (mensagem.resposta && mensagem.resposta.trim()) {
                adicionarMensagem(mensagem.resposta, 'bot', mensagem._id || mensagem.id);
                historico.push({ role: 'assistant', content: mensagem.resposta });
            }
        });

    } catch (erro) {
        console.error("Erro ao carregar mensagens anteriores:", erro);
    } finally {
        load = false;
    }
}

async function salvarMsg(msg, resp, cvs) {
    try {
        const resposta = await fetch(`/mensagens/store`, {
            method: "POST",
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ user: msg, bot: resp, conversa_id: cvs }),
        });

        if (!resposta.ok) throw new Error('Falha ao salvar mensagem');

        return await resposta.json();

    } catch (error) {
        console.error("Erro ao salvar mensagem no banco:", error);
        return null;
    }
}

async function novaConversa(titulo = null) {
    const resposta = await fetch(`/conversas/store`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ titulo })
    });
    if (!resposta.ok) throw new Error('Falha ao criar conversa');
    return await resposta.json();
}


async function carregar() {
    try {
        const resposta = await fetch('/conversas', {
            method: "GET",
            headers: {
                'Content-Type': 'application/json',
            },
        });

        const data = await resposta.json();
        console.log(data);

        const containerLista = document.getElementById('lista');
        containerLista.innerHTML = '';

        const recentes = document.createElement('div')
        recentes.innerText = "Conversas recentes"
        containerLista.appendChild(recentes)
        recentes.classList.add('recentes')
        data.conversas.forEach(c => {
            const itemConversa = document.createElement('div');
            itemConversa.classList.add('hotbar');

            itemConversa.innerHTML = `
        <div class="conversa" data-id="${c._id || c.id}" data-titulo="${c.titulo.replace(/"/g, '&quot;')}">
            <div class="title">
                ${c.titulo.length <= 13 ? c.titulo : c.titulo.slice(0, 13) + "..."}
            </div>
        </div>
        <div class="opcoes">
            <button class="delete" onclick="deleteConversa('${c.id}')">
                <i class="bi bi-trash-fill"></i>
            </button>
            <button class="edit" onclick="editConversa('${c.id}')">
                <i class="bi bi-pencil-square"></i>
            </button>
        </div>
    `;
            containerLista.appendChild(itemConversa);
        });
    } catch (error) {
        console.error('Falha ao carregar a sidebar:', error);
    }
}

// ==========================================
// 4. MODAIS (DELETAR E EDITAR CONVERSAS)
// ==========================================
function deleteConversa(id) {
    const div = document.createElement('div');
    div.innerHTML = `
        <div class="modal-fundo">
            <div class="modal-caixa">
                <form class="modal-form">
                    <p style="margin:0 0 6px;font-size:16px;font-weight:500;color:rgba(0, 0, 0)">Deletar Conversa</p>
                    <p style="margin:0 0 20px;font-size:13px;color:rgba(0, 0, 0)">Tem certeza que deseja deletar esta conversa?</p>
                    <div class="modal-actions">
                        <button type="button" class="cancelar">Cancelar</button>
                        <button type="button" class="btn-ok">Deletar</button>
                    </div>
                </form>
            </div>
        </div>
    `;

    document.body.appendChild(div);

    const cancelar = div.querySelector('.cancelar');
    const deletar = div.querySelector('.btn-ok');
    const cvs = conversaAtiva;

    deletar.addEventListener('click', async () => {
        try {
            await fetch(`/conversas/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            div.remove();
            await carregar();
            if (cvs && cvs !== id) { marcarConversaAtiva(cvs); }
            chatTitle.innerText = ''
            limparchat()
            msg()

        } catch (error) {
            console.error('Erro ao deletar conversa:', error);
        }
    });



    cancelar.addEventListener('click', () => {
        div.remove();
    });
}

async function editConversa(id) {
    const conversaEl = document.querySelector(`.conversa[data-id="${id}"]`);
    const tituloAtual = conversaEl ? (conversaEl.dataset.titulo || '') : '';

    const div = document.createElement('div');
    div.innerHTML = `
        <div class="modal-fundo">
            <div class="modal-caixa">
                <form class="modal-form">
                    <p style="margin:0 0 6px;font-size:16px;font-weight:500;color:rgba(0, 0, 0)">Renomear conversa</p>
                    <p style="margin:0 0 20px;font-size:13px;color:rgba(0, 0, 0)">Digite um novo título para o chat.</p>
                    <input type="text" id="novotitulo" value="${tituloAtual.replace(/"/g, '&quot;')}" placeholder="Título do chat..." required>
                    <div class="modal-actions">
                        <button type="button" class="cancelar">Cancelar</button>
                        <button type="button" class="btn-ok">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    `;

    document.body.appendChild(div);

    const inputTitulo = div.querySelector('#novotitulo');
    inputTitulo.focus();
    inputTitulo.select();

    const cancelar = div.querySelector('.cancelar');
    const editar = div.querySelector('.btn-ok');
    const cvs = conversaAtiva;

    editar.addEventListener('click', async () => {
        const novoTitulo = inputTitulo.value.trim();
        if (!novoTitulo) return;

        try {
            await fetch(`/conversas/edit/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ nome: novoTitulo, id: id }),
            });

            div.remove();
            await carregar();
            if (cvs && cvs !== id) { marcarConversaAtiva(cvs); }

        } catch (error) {
            console.error('Erro ao editar conversa:', error);
        }
    });

    cancelar.addEventListener('click', () => {
        div.remove();
    });
}

// ==========================================
// 5. EVENT LISTENERS & INICIALIZAÇÃO
// ==========================================
send.addEventListener('click', () => {
    if (isGenerating) {
        stop();
    } else {
        enviar();
    }
});

if (form) {
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        enviar();
    });

    form.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            enviar();
        }
    });
}

close.addEventListener('click', () => {
    if (sidebar.classList.contains('open')) {
        sidebar.classList.remove('open');
        return;
    }

    sidebar.classList.toggle('small');

    if (sidebar.classList.contains('small')) {
        close.innerHTML = '<i class="fa-solid fa-bars"></i>';
    } else {
        close.innerHTML = '<i class="fa-solid fa-x"></i>';
    }
});

newchat.addEventListener('click', () => {
    limparchat();
    stop()
    conversaAtiva = null;
    document.querySelectorAll('.conversa').forEach(c => c.classList.remove('active'));
});

document.addEventListener('click', async (e) => {
    if (e.target.closest('.edit') || e.target.closest('.delete')) return;

    const chat = e.target.closest('.conversa');
    if (!chat) return;

    navigate(chat.dataset.id);
});

const menuBtn = document.getElementById('menu-mobile');

menuBtn?.addEventListener('click', () => {
    sidebar.classList.remove('small');
    sidebar.classList.add('open');
});

input.addEventListener('input', function () {
    if (input.value.trim() !== '') {
        send.style.opacity = 1
    }
    else {
        send.style.opacity = 0
    }
})

// Inicialização da aplicação
msg();
carregar();