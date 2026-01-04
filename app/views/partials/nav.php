<?php
require_once __DIR__ . '/../../core/helpers.php';
start_session();
$u = current_user();
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="<?= e(base_url('index.php')) ?>"><?= e(config('app.app_name')) ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('requisitos.php')) ?>">Requisitos</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('experiencias.php')) ?>">Experiencias</a></li>
      </ul>

      <ul class="navbar-nav ms-auto">
        <?php if ($u): ?>
          <?php if (has_role('Administrador')): ?>
            <li class="nav-item"><a class="nav-link" href="<?= e(base_url('admin/index.php')) ?>">Panel admin</a></li>
          <?php endif; ?>
          <li class="nav-item"><span class="navbar-text me-2"><?= e($u['correo']) ?></span></li>
          <li class="nav-item"><a class="nav-link" href="<?= e(base_url('logout.php')) ?>">Salir</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= e(base_url('login.php')) ?>">Iniciar sesión</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= e(base_url('register.php')) ?>">Registrarse</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
