<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Ceci</title>
    <link rel="shortcut icon" href="/img/logo_vertical.png" type="image/x-icon">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="shortcut icon" href="/img/logo_vertical.png" type="image/x-icon">
    <script src="/js/load.js"></script>
</head>

<body class="welcome">

    <div class="mountain-wrapper">
        <div class="mountain mtn-1"></div>
        <div class="mountain mtn-2"></div>
        <div class="mountain mtn-3"></div>
    </div>

    <div class="login-container">
        <h1>Conheça a Ceci!</h1>
        <p>Faça login para começar nossa conversa ou siga como visitante.</p>

        <div class="buttons">
            <a href="/auth/redirect" class="btn-google">
                <i class="fa-brands fa-google"></i>
                Entrar com o Google
            </a>
            <a href="/guest" class="btn-google">
                <i class="fa-regular fa-user"></i>
                Ou siga como visitante</a>
        </div>
    </div>


   @if(session('create'))
    <div id="toast-success" class="toast">
        <div class="toast-content">
            <div class="toast-icon">✓</div>
            <div class="toast-message">
                <span class="toast-title">Sucesso!</span>
                <span class="toast-desc">{{ session('create') }}</span>
            </div>
        </div>
        <button class="toast-close" onclick="closeToast()">&times;</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            showToast();
        });
    </script>
@endif

 @if(session('delete'))
    <div id="toast-success" class="toast">
        <div class="toast-content">
            <div class="toast-icon">✓</div>
            <div class="toast-message">
                <span class="toast-title">Sucesso!</span>
                <span class="toast-desc">{{ session('create') }}</span>
            </div>
        </div>
        <button class="toast-close" onclick="closeToast()">&times;</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            showToast();
        });
    </script>
@endif

    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>

</html>