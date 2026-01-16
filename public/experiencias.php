<?php
require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/core/db.php';
start_session();

$pdo = db();
$destinos = $pdo->query("SELECT id_destino, pais, ciudad FROM destino WHERE estado='activo' ORDER BY pais, ciudad")->fetchAll();
$filtro = isset($_GET['destino']) ? (int)$_GET['destino'] : 0;

$sql = "
  SELECT e.titulo, e.contenido, e.fecha_publicacion, d.pais, d.ciudad, d.bandera_path, u.correo
  FROM experiencia_viajero e
  JOIN destino d ON d.id_destino = e.id_destino
  JOIN usuario u ON u.id_usuario = e.id_usuario
  WHERE e.estado_moderacion='aprobada'
";
$params = [];
if ($filtro) { $sql .= " AND e.id_destino=?"; $params[] = $filtro; }
$sql .= " ORDER BY e.fecha_publicacion DESC LIMIT 30";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Experiencias | <?= e(config('app.app_name')) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e(base_url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../app/views/partials/nav.php'; ?>
<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h1 class="h4 mb-0">Experiencias de viajeros (aprobadas)</h1>
    <?php if (is_logged_in()): ?>
      <a class="btn btn-primary" href="<?= e(base_url('publicar_experiencia.php')) ?>">Publicar experiencia</a>
    <?php else: ?>
      <a class="btn btn-primary" href="<?= e(base_url('login.php')) ?>">Iniciar sesión para publicar</a>
    <?php endif; ?>
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
    <div class="alert alert-info">No hay experiencias aprobadas para mostrar.</div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($items as $it): ?>
        <div class="col-12">
          <div class="card shadow-sm">
            <div class="card-body">
              <div class="d-flex justify-content-between flex-wrap gap-2">
                <h2 class="h6 mb-0"><?= e($it['titulo']) ?></h2>
                <div class="d-flex align-items-center gap-2">
                  <span class="text-secondary small"><?= e($it['pais'] . ($it['ciudad'] ? ' - ' . $it['ciudad'] : '')) ?></span>
                  <span class="experience-flag">
                    <span class="flag-sphere flag-sphere--sm">
                      <?php if (!empty($it['bandera_path'])): ?>
                        <img src="<?= e(base_url($it['bandera_path'])) ?>" alt="Bandera de <?= e($it['pais']) ?>">
                      <?php else: ?>
                        <span class="flag-fallback"><?= e(substr($it['pais'], 0, 1)) ?></span>
                      <?php endif; ?>
                    </span>
                  </span>
                </div>
              </div>
              <div class="text-secondary small mt-1">
                Publicado: <?= e(date('Y-m-d', strtotime($it['fecha_publicacion']))) ?> · Autor: <?= e($it['correo']) ?>
              </div>
              <p class="mt-3 mb-0"><?= nl2br(e($it['contenido'])) ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/../app/views/partials/footer.php'; ?>
</body>
</html>
