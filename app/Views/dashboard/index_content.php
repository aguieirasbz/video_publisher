<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h1 class="h3 mb-1">Dashboard</h1><p class="text-muted mb-0">Bem-vindo, <?= htmlspecialchars($user['nome'], ENT_QUOTES, 'UTF-8') ?>.</p></div>
    <form method="post" action="/logout"><input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>"><button class="btn btn-outline-secondary" type="submit">Sair</button></form>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-6"><a class="btn btn-primary w-100 py-3" href="/configuracoes">Configurações de geração</a></div>
    <div class="col-md-6"><a class="btn btn-primary w-100 py-3" href="/assuntos">Assuntos</a></div>
    <div class="col-md-6"><a class="btn btn-primary w-100 py-3" href="/canais">Canais</a></div>
    <div class="col-md-6"><a class="btn btn-primary w-100 py-3" href="/historico">Histórico</a></div>
    <div class="col-md-6"><a class="btn btn-primary w-100 py-3" href="/rotinas">Rotinas de publicação</a></div>
    <div class="col-md-6"><a class="btn btn-primary w-100 py-3" href="/usuarios">Administradores</a></div>
</div>
<div class="row g-3 mb-4">
    <?php $cards = [['Assuntos cadastrados', 'assuntos_total', 'primary'], ['Assuntos pendentes', 'assuntos_pendentes', 'warning'], ['Vídeos gerados', 'videos_gerados', 'success'], ['Publicações agendadas', 'publicacoes_agendadas', 'info'], ['Publicações realizadas', 'publicacoes_realizadas', 'dark'], ['Falhas', 'falhas', 'danger']]; ?>
    <?php foreach ($cards as [$label, $key, $color]): ?><div class="col-sm-6 col-lg-4"><div class="card border-<?= $color ?> h-100"><div class="card-body"><div class="text-muted small"><?= $label ?></div><div class="display-6 fw-semibold"><?= (int) ($stats[$key] ?? 0) ?></div></div></div></div><?php endforeach; ?>
</div>
<div class="alert alert-info">Os indicadores consideram somente os registros pertencentes ao usuário autenticado.</div>
