<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Ceci</title>
    <link rel="stylesheet" href="/css/style.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="/img/logo_vertical.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/js-loading-overlay@1.2.0/dist/js-loading-overlay.min.js"></script>
    <link rel="stylesheet" rel="preconnect"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>

<body>

    <div class="mountain-wrapper">
        <div class="mountain mtn-1"></div>
        <div class="mountain mtn-2"></div>
        <div class="mountain mtn-3"></div>
    </div>

    <div id="sidebar">
        <div class="head">
            <div class="close"><i class="fa-solid fa-x"></i></div>
            <a href="/"><img src="img/logo2.png" class="logo" alt=""></a>
        </div>

        <div id="conversas">
            @if(Auth::check())
                <a id="new">
                    <i class="bi bi-plus-circle-fill"></i>
                    <p class="novo">Novo chat</p>
                </a>
            @else
                <a id="new" href="/conversas/refresh">
                    <i class="bi bi-plus-circle-fill"></i>
                    <p class="novo">Novo chat</p>
                </a>
            @endif

            <div class="container-conversa" id="lista">
            </div>
        </div>

        @if(Auth::check())
                <div id="user-profile">
                    <div class="config">
                        <a href="/gear" class="profile"><i class="bi bi-gear-fill opcao"></i></a>
                        <form method="POST" action="/logout">
                            @csrf
                            <button type="submit" id="logout">
                                <i class="bi bi-door-open-fill"></i>
                            </button>
                        </form>
                    </div>

                    <div id="user-info">
                        @if(strlen(Auth::user()->nome) > 18)
                            <span class="user-name">{{ Str::limit(Auth::user()->nome, 15, '...') }}</span>
                        @else
                            <span class="user-name">{{ Auth::user()->nome }}</span>
                        @endif
                    </div>



                </div>
            </div>
        @else
        <div id="user-profile"> <a class="btn-log" href="/auth/redirect">Fazer login</a></div>
    @endif
    </div>
    </div>

    <div id="chat">
        <button id="menu-mobile" class="menu-mobile" type="button">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="chattitle"></div>
        <div class="initText"></div>
        <div id="botroot"></div>
        <form id="form">
            @csrf
            <textarea name="pergunta" id="pergunta" placeholder="Pergunte à Ceci" rows="1"></textarea>
            <button type="submit" class="enviar"><i class="bi bi-cursor-fill"></i>
            </button>
        </form>
    </div>



    <script src="/js/script.js"></script>
    <script>
        document.getElementById('pergunta').addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('form').requestSubmit();
            }
        });
    </script>
</body>

</html>