<?php require_once __DIR__ . '/_admin_guard.php'; ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="<?= e(base_url('admin/index.php')) ?>">Panel admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navAdmin">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navAdmin">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('admin/destinos.php')) ?>">Destinos</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('admin/requisitos.php')) ?>">Requisitos</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('admin/experiencias.php')) ?>">Experiencias</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('admin/avisos.php')) ?>">Avisos</a></li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('index.php')) ?>">Ver sitio</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('logout.php')) ?>">Salir</a></li>
      </ul>
    </div>
  </div>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
