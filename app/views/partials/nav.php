<?php
require_once __DIR__ . '/../../core/helpers.php';
start_session();
$u = current_user();
?>
<header class="site-header">
  <nav class="site-nav">
    <a class="brand" href="<?= e(base_url('index.php')) ?>">
      <img class="brand-logo" src="<?= e(base_url('assets/img/logo.webp')) ?>" alt="goTurismCol">
      <span class="brand-name">go<span class="brand-accent">Turism</span><span class="brand-accent-secondary">Col</span></span>
    </a>

    <div class="nav-links">
      <a href="<?= e(base_url('requisitos.php')) ?>">Requisitos</a>
      <a href="<?= e(base_url('experiencias.php')) ?>">Experiencias</a>
      <a href="#destinos">Destinos</a>
    </div>

    <div class="nav-actions">
      <?php if ($u): ?>
        <?php if (has_role('Administrador')): ?>
          <a class="btn btn-ghost" href="<?= e(base_url('admin/index.php')) ?>">Panel admin</a>
        <?php endif; ?>
        <span class="user-email"><?= e($u['correo']) ?></span>
        <a class="btn btn-outline" href="<?= e(base_url('logout.php')) ?>">Salir</a>
      <?php else: ?>
        <a class="btn btn-ghost" href="<?= e(base_url('login.php')) ?>">Iniciar sesión</a>
        <a class="btn btn-secondary" href="<?= e(base_url('register.php')) ?>">Registrarse</a>
      <?php endif; ?>
    </div>
  </nav>
</header>
