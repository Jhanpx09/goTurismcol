<?php
require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/core/db.php';
start_session();

$pdo = db();
$destinos = $pdo->query("SELECT id_destino, pais, ciudad FROM destino WHERE estado='activo' ORDER BY pais, ciudad")->fetchAll();
$filtro = isset($_GET['destino']) ? (int)$_GET['destino'] : 0;

$sql = "
  SELECT a.titulo_aviso, a.detalle_aviso, a.fecha_publicacion, d.pais, d.ciudad
  FROM aviso_actualizacion a
  JOIN destino d ON d.id_destino = a.id_destino
  WHERE a.estado='activo'
";
$params = [];
if ($filtro) { $sql .= " AND a.id_destino=?"; $params[] = $filtro; }
$sql .= " ORDER BY a.fecha_publicacion DESC LIMIT 50";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Avisos | <?= e(config('app.app_name')) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e(asset_url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../app/views/partials/nav.php'; ?>
<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h1 class="h4 mb-0">Avisos de actualizacion</h1>
    <a class="btn btn-outline btn-sm" href="<?= e(base_url('index.php')) ?>">Volver al inicio</a>
  </div>

  <form class="row g-2 mt-3 mb-4" method="get">
    <div class="col-md-8">
      <select class="form-select" name="destino">
        <option value="0" <?= $filtro ? '' : 'selected' ?>>Todos los destinos</option>
        <?php foreach ($destinos as $d): ?>
          <option value="<?= (int)$d['id_destino'] ?>" <?= $filtro===(int)$d['id_destino'] ? 'selected' : '' ?>>
            <?= e($d['pais'] . ($d['ciudad'] ? ' - ' . $d['ciudad'] : '')) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4 d-grid"><button class="btn btn-outline-secondary">Filtrar</button></div>
  </form>

  <?php if (!$items): ?>
    <div class="alert alert-info">No hay avisos recientes para mostrar.</div>
  <?php else: ?>
    <section class="card notice-card">
      <div class="card-title">
        <span class="card-icon">🔔</span>
        <h2>Historial de avisos</h2>
      </div>
      <div class="notice-list">
        <?php foreach ($items as $a): ?>
          <article class="notice-item">
            <div class="notice-header">
              <strong><?= e($a['titulo_aviso']) ?></strong>
              <span><?= e(date('Y-m-d', strtotime($a['fecha_publicacion']))) ?></span>
            </div>
            <div class="notice-meta">
              <?= e($a['pais'] . ($a['ciudad'] ? ' - ' . $a['ciudad'] : '')) ?>
            </div>
            <div class="notice-body"><?= nl2br(e($a['detalle_aviso'])) ?></div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/../app/views/partials/footer.php'; ?>
</body>
</html>
