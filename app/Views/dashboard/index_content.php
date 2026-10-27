<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h1 class="h3 mb-1">Dashboard</h1><p class="text-muted mb-0">Bem-vindo, <?= htmlspecialchars($user['nome'], ENT_QUOTES, 'UTF-8') ?>.</p></div>
    <form method="post" action="/logout"><input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>"><button class="btn btn-outline-secondary" type="submit">Sair</button></form>
</div>
<div class="alert alert-info">A estrutura inicial está pronta. Os cadastros serão implementados após a validação desta etapa.</div>
