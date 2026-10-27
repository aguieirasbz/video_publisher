<div class="row justify-content-center"><div class="col-md-5 col-lg-4">
    <div class="card shadow-sm"><div class="card-body p-4">
        <h1 class="h4 mb-4">AluandTech Auto Shorts</h1>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <form method="post" action="/login" novalidate>
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            <label class="form-label" for="email">E-mail</label>
            <input class="form-control mb-3" id="email" name="email" type="email" required autofocus>
            <label class="form-label" for="password">Senha</label>
            <input class="form-control mb-4" id="password" name="password" type="password" required>
            <button class="btn btn-primary w-100" type="submit">Entrar</button>
        </form>
    </div></div>
</div></div>
