<?php
require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/core/db.php';
start_session();

$pdo = db();
$destinos = $pdo->query("SELECT id_destino, pais, ciudad, bandera_path FROM destino WHERE estado='activo' ORDER BY pais, ciudad")->fetchAll();

$destino_id = isset($_GET['destino']) ? (int)$_GET['destino'] : 0;
if (!$destino_id && $destinos) {
  $destino_id = (int)$destinos[0]['id_destino'];
}

$destino = null;
$requisitos = [];
$experiencias = [];
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
      SELECT e.titulo, e.contenido, e.fecha_publicacion, u.correo
      FROM experiencia_viajero e
      JOIN usuario u ON u.id_usuario = e.id_usuario
      WHERE e.id_destino=? AND e.estado_moderacion='aprobada'
      ORDER BY e.fecha_publicacion DESC
      LIMIT 20
    ");
    $stmt->execute([$destino_id]);
    $experiencias = $stmt->fetchAll();
  }
}

function map_requisito_tipo(string $tipo): string {
  $key = strtolower(trim($tipo));
  $aliases = [
    'obligatorio' => 'obligatorio',
    'obligatoria' => 'obligatorio',
    'recomendado' => 'recomendado',
    'recomendada' => 'recomendado',
    'informacion' => 'informacion',
    'información' => 'informacion',
    'info' => 'informacion',
    'general' => 'informacion',
    'informacion general' => 'informacion',
    'informacion gral' => 'informacion',
    'migratorio' => 'obligatorio',
    'documental' => 'obligatorio',
    'sanitario' => 'obligatorio',
  ];
  return $aliases[$key] ?? 'informacion';
}

function tipo_label(string $tipo): string {
  $map = [
    'obligatorio' => 'Obligatorio',
    'recomendado' => 'Recomendado',
    'informacion' => 'Información gral.',
  ];
  return $map[$tipo] ?? 'Información gral.';
}

$grouped = [
  'obligatorio' => [],
  'recomendado' => [],
];
foreach ($requisitos as $r) {
  $bucket = map_requisito_tipo($r['tipo_requisito']);
  if (!isset($grouped[$bucket])) {
    $bucket = 'recomendado';
  }
  $grouped[$bucket][] = $r;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Requisitos | <?= e(config('app.app_name')) ?></title>
  <link href="<?= e(base_url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../app/views/partials/nav.php'; ?>

<main class="requirements-page">
  <section class="requirements-hero">
    <h1>Requisitos por destino</h1>
    <p>Selecciona un país del carrusel para visualizar los requisitos de viaje, trámites migratorios y recomendaciones de salud actualizados.</p>
  </section>

  <section class="flag-carousel">
    <?php if (!$destinos): ?>
      <p class="helper-text">No hay destinos activos para mostrar.</p>
    <?php else: ?>
      <div class="flag-track">
        <?php foreach ($destinos as $d): ?>
          <?php $is_active = $destino_id === (int)$d['id_destino']; ?>
          <a class="flag-item <?= $is_active ? 'is-active' : '' ?>" href="<?= e(base_url('requisitos.php?destino=' . (int)$d['id_destino'])) ?>">
            <span class="flag-sphere">
              <?php if (!empty($d['bandera_path'])): ?>
                <img src="<?= e(base_url($d['bandera_path'])) ?>" alt="Bandera de <?= e($d['pais']) ?>">
              <?php else: ?>
                <span class="flag-fallback"><?= e(substr($d['pais'], 0, 1)) ?></span>
              <?php endif; ?>
            </span>
            <span class="flag-label"><?= e($d['pais']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <section class="requirements-card">
    <?php if (!$destino): ?>
      <p class="helper-text">Seleccione un destino para ver los requisitos disponibles.</p>
    <?php else: ?>
      <div class="requirements-tabs">
        <?php foreach (['obligatorio', 'recomendado', 'experiencias'] as $idx => $key): ?>
          <button class="tab-btn <?= $idx === 0 ? 'is-active' : '' ?>" data-tab="<?= e($key) ?>">
            <?= $key === 'experiencias' ? 'Experiencias' : e(tipo_label($key)) ?>
          </button>
        <?php endforeach; ?>
      </div>

      <div class="requirements-content">
        <?php foreach ($grouped as $key => $items): ?>
          <div class="tab-panel <?= $key === 'obligatorio' ? 'is-active' : '' ?>" data-panel="<?= e($key) ?>">
            <div class="panel-head">
              <h2><?= e(tipo_label($key)) ?></h2>
              <?php if ($destino): ?>
                <span><?= e($destino['pais'] . ($destino['ciudad'] ? ' - ' . $destino['ciudad'] : '')) ?></span>
              <?php endif; ?>
            </div>

            <?php if (!$items): ?>
              <p class="helper-text">No hay requisitos registrados para esta categoría.</p>
            <?php else: ?>
              <div class="requirements-list">
                <?php foreach ($items as $r): ?>
                  <article class="requirement-item">
                    <div class="requirement-icon">✔</div>
                    <div class="requirement-body">
                      <h3><?= e($r['titulo_requisito']) ?></h3>
                      <p><?= nl2br(e($r['descripcion_requisito'])) ?></p>
                      <div class="requirement-meta">
                        <span>Actualizado: <?= e($r['fecha_ultima_actualizacion']) ?></span>
                        <?php if (!empty($r['fuente_oficial'])): ?>
                          <span>Fuente: <?= e($r['fuente_oficial']) ?></span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>

        <div class="tab-panel" data-panel="experiencias">
          <div class="panel-head">
            <h2>Experiencias</h2>
            <?php if ($destino): ?>
              <span><?= e($destino['pais'] . ($destino['ciudad'] ? ' - ' . $destino['ciudad'] : '')) ?></span>
            <?php endif; ?>
          </div>

          <?php if (!$experiencias): ?>
            <p class="helper-text">No hay experiencias aprobadas para este destino.</p>
          <?php else: ?>
            <div class="experience-list">
              <?php foreach ($experiencias as $it): ?>
                <article class="experience-item">
                  <div class="experience-header">
                    <h3><?= e($it['titulo']) ?></h3>
                    <span><?= e(date('Y-m-d', strtotime($it['fecha_publicacion']))) ?></span>
                  </div>
                  <p><?= nl2br(e($it['contenido'])) ?></p>
                  <small>Autor: <?= e($it['correo']) ?></small>
                </article>
              <?php endforeach; ?>
            </div>
            <div class="experience-footer">
              <a class="link-primary" href="<?= e(base_url('experiencias.php?destino=' . (int)$destino_id)) ?>">Ver todas las experiencias</a>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="requirements-footer">
        <a class="btn btn-primary" href="<?= e(base_url('requisitos_pdf.php?destino=' . (int)$destino_id)) ?>">Descargar guía completa (PDF)</a>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php include __DIR__ . '/../app/views/partials/footer.php'; ?>
<script>
  document.querySelectorAll('.tab-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var key = btn.getAttribute('data-tab');
      document.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.remove('is-active'); });
      document.querySelectorAll('.tab-panel').forEach(function (p) { p.classList.remove('is-active'); });
      btn.classList.add('is-active');
      var panel = document.querySelector('.tab-panel[data-panel=\"' + key + '\"]');
      if (panel) panel.classList.add('is-active');
    });
  });
</script>
</body>
</html>
