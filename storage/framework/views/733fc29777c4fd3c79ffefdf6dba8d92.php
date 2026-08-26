<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="shortcut icon" href="/img/logo_vertical.png" type="image/x-icon">
    <script src="/js/load.js"></script>
</head>

<body>

    <div class="mountain-wrapper">
        <div class="mountain mtn-1"></div>
        <div class="mountain mtn-2"></div>
        <div class="mountain mtn-3"></div>
    </div>

    <div class="profile-container">

        <div class="profile-header">
            <a href="/chat" class="btn-back" title="Voltar ao Chat">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1>Editar Perfil</h1>
        </div>

        <p>Atualize seus dados de exibição na plataforma.</p>

        <form class="profile-form" action="/profile/edit" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <div class="input-group">
                <label for="username">Nome de Usuário</label>
                <input type="text" id="username" name="username" value="<?php echo e(Auth::user()->nome); ?>"
                    placeholder="Digite seu novo nome" required>
            </div>

            <button type="submit" class="btn-save">Salvar Alterações</button>
        </form>

        <div class="danger-zone">
            <h3>Zona de Perigo</h3>
            <p>A exclusão da conta é permanente e removerá todo o seu histórico de conversas.</p>
            <button type="submit" class="btn-delete" commandFor="delete-account" command="show-modal">Deletar
                Conta</button>
        </div>

    </div>

    <dialog id="delete-account">
        <h1>Deletar conta</h1>
        <p>Tem certeza de que deseja deletar sua conta? Esta ação não pode ser desfeita.</p>
        <form action="/delete" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <button type="submit" class="btn-delete">Deletar Conta</button>
        </form>
        <button class="btn-cancel" commandFor="delete-account" command="close">Cancelar</button>
    </dialog>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('update')): ?>
        <div id="toast-success" class="toast">
            <div class="toast-content">
                <div class="toast-icon">✓</div>
                <div class="toast-message">
                    <span class="toast-title">Sucesso!</span>
                    <span class="toast-desc"><?php echo e(session('create')); ?></span>
                </div>
            </div>
            <button class="toast-close" onclick="closeToast()">&times;</button>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                showToast();
            });
        </script>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <script src="/js/script.js"></script>
</body>

</html><?php /**PATH C:\xampp\htdocs\backuplrv\resources\views/profile.blade.php ENDPATH**/ ?>