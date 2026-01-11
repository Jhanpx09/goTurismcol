<?php
require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/core/db.php';
require_once __DIR__ . '/../app/core/auth.php';
start_session();

$pdo = db();
$destinos = $pdo->query("SELECT id_destino, pais, ciudad FROM destino WHERE estado='activo' ORDER BY pais, ciudad")->fetchAll();
$avisos = $pdo->query("
  SELECT a.titulo_aviso, a.detalle_aviso, a.fecha_publicacion, d.pais, d.ciudad
  FROM aviso_actualizacion a
  JOIN destino d ON d.id_destino = a.id_destino
  WHERE a.estado='activo'
  ORDER BY a.fecha_publicacion DESC
  LIMIT 5
")->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(config('app.app_name')) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e(base_url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../app/views/partials/nav.php'; ?>

<main class="container py-4">
  <section class="hero-banner mb-4">
    <div class="hero-content">
      <div class="hero-text">
        <h1>Planificación de viajes con información organizada</h1>
        <p>
          Consulta requisitos y trámites por destino, revisa experiencias de otros viajeros y publica la tuya (con moderación).
        </p>
      </div>
      <div class="hero-art">
        <!-- TODO: Reemplaza este bloque por tu imagen principal.
             Ejemplo:
             <img src="<?= e(base_url('assets/img/hero.jpg')) ?>" alt="Hero" class="img-fluid rounded-4">
             Tamaño sugerido: 460 × 260 px. -->
        <div class="image-placeholder image-placeholder--hero">Imagen 460 × 260</div>
      </div>
    </div>
  </section>

  <div class="row g-4">
    <div class="col-lg-7">
      <div class="card shadow-sm">
        <div class="card-body">
          <h2 class="h5">Consultar requisitos por destino</h2>
          <form class="row g-2" method="get" action="<?= e(base_url('requisitos.php')) ?>">
            <div class="col-md-8">
              <select class="form-select" name="destino" required>
                <option value="" selected disabled>Seleccione un destino</option>
                <?php foreach ($destinos as $d): ?>
                  <option value="<?= (int)$d['id_destino'] ?>">
                    <?= e($d['pais'] . ($d['ciudad'] ? ' - ' . $d['ciudad'] : '')) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4 d-grid">
              <button class="btn btn-primary">Consultar</button>
            </div>
          </form>
          <p class="text-secondary mt-3 mb-0 small">
            La información se administra internamente e incluye la fecha de última actualización.
          </p>
        </div>
      </div>

      <div class="mt-4 card shadow-sm experience-card">
        <div class="card-body">
          <h2 class="h5">Experiencias de viajeros</h2>
          <p class="text-secondary mb-3">Solo se muestra contenido aprobado por moderación.</p>
          <a class="btn btn-outline-secondary" href="<?= e(base_url('experiencias.php')) ?>">Ver experiencias</a>
          <?php if (is_logged_in()): ?>
            <a class="btn btn-primary ms-2" href="<?= e(base_url('publicar_experiencia.php')) ?>">Publicar experiencia</a>
          <?php else: ?>
            <a class="btn btn-primary ms-2" href="<?= e(base_url('login.php')) ?>">Iniciar sesión para publicar</a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card shadow-sm notice-card">
        <div class="card-body">
          <div class="d-flex align-items-center gap-3">
            <span class="notice-icon" aria-hidden="true">🔔</span>
            <h2 class="h5 mb-0">Avisos de actualización</h2>
          </div>
          <?php if (!$avisos): ?>
            <p class="text-secondary mb-0">No hay avisos recientes.</p>
          <?php else: ?>
            <div class="list-group">
              <?php foreach ($avisos as $a): ?>
                <div class="list-group-item">
                  <div class="d-flex justify-content-between">
                    <strong><?= e($a['titulo_aviso']) ?></strong>
                    <span class="text-secondary small"><?= e(date('Y-m-d', strtotime($a['fecha_publicacion']))) ?></span>
                  </div>
                  <div class="text-secondary small mb-1">
                    <?= e($a['pais'] . ($a['ciudad'] ? ' - ' . $a['ciudad'] : '')) ?>
                  </div>
                  <div><?= nl2br(e($a['detalle_aviso'])) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card shadow-sm mt-4">
        <div class="card-body">
          <!-- TODO: Reemplaza este bloque por tu imagen secundaria.
               Ejemplo:
               <img src="<?= e(base_url('assets/img/preview.jpg')) ?>" alt="Vista previa" class="img-fluid rounded-4">
               Tamaño sugerido: 420 × 220 px. -->
          <div class="image-placeholder image-placeholder--wide">Imagen 420 × 220</div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/../app/views/partials/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
