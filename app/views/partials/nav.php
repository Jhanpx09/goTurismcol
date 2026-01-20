<?php
require_once __DIR__ . '/../../core/helpers.php';
start_session();
$u = current_user();
$current = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
function nav_active(string $file, string $current): string {
  return $current === $file ? 'is-active' : '';
}
?>
<header class="site-header">
  <nav class="site-nav">
    <a class="brand" href="<?= e(base_url('index.php')) ?>">
      <img class="brand-logo" src="<?= e(asset_url('assets/img/logo.webp')) ?>" alt="goTurismCol">
      <span class="brand-name">go<span class="brand-accent">Turism</span><span class="brand-accent-secondary">Col</span></span>
    </a>

    <div class="nav-links">
      <a class="<?= nav_active('index.php', $current) ?>" href="<?= e(base_url('index.php')) ?>" <?= $current === 'index.php' ? 'aria-current="page"' : '' ?>>Inicio</a>
      <a class="<?= nav_active('requisitos.php', $current) ?>" href="<?= e(base_url('requisitos.php')) ?>" <?= $current === 'requisitos.php' ? 'aria-current="page"' : '' ?>>Requisitos</a>
      <a class="<?= nav_active('experiencias.php', $current) ?>" href="<?= e(base_url('experiencias.php')) ?>" <?= $current === 'experiencias.php' ? 'aria-current="page"' : '' ?>>Experiencias</a>
      <a class="nav-link-anchor" href="<?= e(base_url('index.php')) ?>#destinos">Destinos</a>
    </div>

    <div class="nav-actions">
      <?php if ($u): ?>
        <?php if (has_role('Administrador')): ?>
          <a class="btn btn-secondary" href="<?= e(base_url('admin/index.php')) ?>">Panel admin</a>
        <?php endif; ?>
        <span class="user-email <?= has_role('Administrador') ? 'is-admin' : '' ?>"><?= e($u['correo']) ?></span>
        <a class="btn btn-outline btn-outline-danger" href="<?= e(base_url('logout.php')) ?>">Salir</a>
      <?php else: ?>
        <a class="btn btn-ghost" href="<?= e(base_url('login.php')) ?>">Iniciar sesión</a>
        <a class="btn btn-secondary" href="<?= e(base_url('register.php')) ?>">Registrarse</a>
      <?php endif; ?>
    </div>
  </nav>
</header>
