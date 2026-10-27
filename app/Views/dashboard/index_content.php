<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h1 class="h3 mb-1">Dashboard</h1><p class="text-muted mb-0">Bem-vindo, <?= htmlspecialchars($user['nome'], ENT_QUOTES, 'UTF-8') ?>.</p></div>
    <form method="post" action="/logout"><input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>"><button class="btn btn-outline-secondary" type="submit">Sair</button></form>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-6"><a class="btn btn-primary w-100 py-3" href="/configuracoes">Configurações de geração</a></div>
    <div class="col-md-6"><a class="btn btn-primary w-100 py-3" href="/assuntos">Assuntos</a></div>
    <div class="col-md-6"><a class="btn btn-primary w-100 py-3" href="/canais">Canais</a></div>
    <div class="col-md-6"><a class="btn btn-primary w-100 py-3" href="/historico">Histórico</a></div>
</div>
<div class="alert alert-info">Os indicadores do dashboard serão conectados aos históricos na etapa correspondente.</div>
