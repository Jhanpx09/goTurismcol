<?php
require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/core/db.php';
require_once __DIR__ . '/../app/core/auth.php';
require_login();

$pdo = db();
$u = current_user();

$stmt = $pdo->prepare("
  SELECT e.titulo, e.fecha_envio, e.estado_moderacion, d.pais, d.ciudad
  FROM experiencia_viajero e
  JOIN destino d ON d.id_destino = e.id_destino
  WHERE e.id_usuario=?
  ORDER BY e.fecha_envio DESC
");
$stmt->execute([(int)$u['id_usuario']]);
$items = $stmt->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mis experiencias | <?= e(config('app.app_name')) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e(asset_url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../app/views/partials/nav.php'; ?>
<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h1 class="h4 mb-0">Mis experiencias</h1>
    <a class="btn btn-primary" href="<?= e(base_url('publicar_experiencia.php')) ?>">Publicar nueva</a>
  </div>

  <?php if (!$items): ?>
    <div class="alert alert-info mt-3">Aún no has publicado experiencias.</div>
  <?php else: ?>
    <div class="mt-3 list-group">
      <?php foreach ($items as $it): ?>
        <div class="list-group-item">
          <div class="d-flex justify-content-between flex-wrap gap-2">
            <strong><?= e($it['titulo']) ?></strong>
            <span class="badge text-bg-<?= $it['estado_moderacion']==='aprobada'?'success':($it['estado_moderacion']==='rechazada'?'danger':'secondary') ?>">
              <?= e($it['estado_moderacion']) ?>
            </span>
          </div>
          <div class="text-secondary small">
            <?= e($it['pais'] . ($it['ciudad'] ? ' - ' . $it['ciudad'] : '')) ?> · Enviado: <?= e(date('Y-m-d', strtotime($it['fecha_envio']))) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/../app/views/partials/footer.php'; ?>
</body>
</html>
