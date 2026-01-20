<?php require_once __DIR__ . '/_admin_guard.php'; ?>
<nav class="navbar navbar-expand-lg top-nav">
  <div class="container">
    <a class="navbar-brand" href="<?= e(base_url('admin/index.php')) ?>">
      <span class="brand-logo" aria-hidden="true">AD</span>
      <span class="brand-text">Panel admin</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navAdmin">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navAdmin">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('admin/destinos.php')) ?>">Destinos</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('admin/destinos_destacados.php')) ?>">Destinos destacados</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('admin/hero_slider.php')) ?>">Slider portada</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('admin/requisitos.php')) ?>">Requisitos</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('admin/experiencias.php')) ?>">Experiencias</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('admin/usuarios.php')) ?>">Usuarios y roles</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('admin/avisos.php')) ?>">Avisos</a></li>
      </ul>
      <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
        <li class="nav-item"><a class="btn btn-outline-secondary btn-sm nav-btn" href="<?= e(base_url('index.php')) ?>">Ver sitio</a></li>
        <li class="nav-item"><a class="btn btn-primary btn-sm nav-btn" href="<?= e(base_url('logout.php')) ?>">Salir</a></li>
      </ul>
    </div>
  </div>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
