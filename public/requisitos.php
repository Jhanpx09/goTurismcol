<?php
require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/core/db.php';
start_session();

$pdo = db();
$destinos = $pdo->query("SELECT id_destino, pais, ciudad FROM destino WHERE estado='activo' ORDER BY pais, ciudad")->fetchAll();

$destino_id = isset($_GET['destino']) ? (int)$_GET['destino'] : 0;
$destino = null;
$requisitos = [];
$avisos = [];

if ($destino_id) {
  $stmt = $pdo->prepare("SELECT * FROM destino WHERE id_destino=? AND estado='activo'");
  $stmt->execute([$destino_id]);
  $destino = $stmt->fetch();

  if ($destino) {
    $stmt = $pdo->prepare("
      SELECT titulo_requisito, descripcion_requisito, tipo_requisito, fuente_oficial, fecha_ultima_actualizacion
      FROM requisito_viaje
      WHERE id_destino=? AND estado='vigente'
      ORDER BY tipo_requisito, titulo_requisito
    ");
    $stmt->execute([$destino_id]);
    $requisitos = $stmt->fetchAll();

    $stmt = $pdo->prepare("
      SELECT titulo_aviso, detalle_aviso, fecha_publicacion
      FROM aviso_actualizacion
      WHERE id_destino=? AND estado='activo'
      ORDER BY fecha_publicacion DESC
      LIMIT 3
    ");
    $stmt->execute([$destino_id]);
    $avisos = $stmt->fetchAll();
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Requisitos | <?= e(config('app.app_name')) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e(base_url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../app/views/partials/nav.php'; ?>
<main class="container py-4">
  <h1 class="h4 mb-3">Requisitos por destino</h1>

  <form class="row g-2 mb-4" method="get">
    <div class="col-md-8">
      <select class="form-select" name="destino" required>
        <option value="" disabled <?= $destino_id ? '' : 'selected' ?>>Seleccione un destino</option>
        <?php foreach ($destinos as $d): ?>
          <option value="<?= (int)$d['id_destino'] ?>" <?= $destino_id===(int)$d['id_destino'] ? 'selected' : '' ?>>
            <?= e($d['pais'] . ($d['ciudad'] ? ' - ' . $d['ciudad'] : '')) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4 d-grid"><button class="btn btn-primary">Consultar</button></div>
  </form>

  <?php if ($destino): ?>
    <div class="mb-3 text-secondary">
      <strong>Destino:</strong> <?= e($destino['pais'] . ($destino['ciudad'] ? ' - ' . $destino['ciudad'] : '')) ?>
    </div>

    <?php if ($avisos): ?>
      <div class="alert alert-warning">
        <strong>Avisos recientes:</strong>
        <ul class="mb-0">
          <?php foreach ($avisos as $a): ?>
            <li><?= e($a['titulo_aviso']) ?> (<?= e(date('Y-m-d', strtotime($a['fecha_publicacion']))) ?>)</li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if (!$requisitos): ?>
      <div class="alert alert-info">No hay requisitos vigentes registrados para este destino.</div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($requisitos as $r): ?>
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap gap-2">
                  <h2 class="h6 mb-0"><?= e($r['titulo_requisito']) ?></h2>
                  <span class="badge text-bg-secondary"><?= e($r['tipo_requisito']) ?></span>
                </div>
                <div class="text-secondary small mt-1">Última actualización: <?= e($r['fecha_ultima_actualizacion']) ?></div>
                <p class="mt-3 mb-0"><?= nl2br(e($r['descripcion_requisito'])) ?></p>
                <?php if (!empty($r['fuente_oficial'])): ?>
                  <p class="mt-3 mb-0 small text-secondary"><strong>Fuente oficial:</strong> <?= nl2br(e($r['fuente_oficial'])) ?></p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php else: ?>
    <p class="text-secondary">Seleccione un destino para ver los requisitos disponibles.</p>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/../app/views/partials/footer.php'; ?>
</body>
</html>
