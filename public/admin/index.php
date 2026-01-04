<?php require_once __DIR__ . '/_admin_guard.php'; ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel administrativo | <?= e(config('app.app_name')) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e(base_url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/_nav.php'; ?>
<main class="container py-4">
  <h1 class="h4 mb-3">Panel administrativo</h1>
  <div class="row g-3">
    <div class="col-md-6">
      <a class="card shadow-sm text-decoration-none" href="<?= e(base_url('admin/destinos.php')) ?>">
        <div class="card-body"><h2 class="h6 mb-1">Destinos</h2><p class="text-secondary mb-0">Crear y activar/desactivar destinos.</p></div>
      </a>
    </div>
    <div class="col-md-6">
      <a class="card shadow-sm text-decoration-none" href="<?= e(base_url('admin/requisitos.php')) ?>">
        <div class="card-body"><h2 class="h6 mb-1">Requisitos</h2><p class="text-secondary mb-0">Gestionar requisitos y registrar actualizaciones.</p></div>
      </a>
    </div>
    <div class="col-md-6">
      <a class="card shadow-sm text-decoration-none" href="<?= e(base_url('admin/experiencias.php')) ?>">
        <div class="card-body"><h2 class="h6 mb-1">Moderación</h2><p class="text-secondary mb-0">Aprobar o rechazar experiencias pendientes.</p></div>
      </a>
    </div>
    <div class="col-md-6">
      <a class="card shadow-sm text-decoration-none" href="<?= e(base_url('admin/avisos.php')) ?>">
        <div class="card-body"><h2 class="h6 mb-1">Avisos</h2><p class="text-secondary mb-0">Publicar avisos por destino.</p></div>
      </a>
    </div>
  </div>
</main>
<?php include __DIR__ . '/../../app/views/partials/footer.php'; ?>
</body>
</html>
