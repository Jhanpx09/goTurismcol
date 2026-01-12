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
$destinos_populares = array_slice($destinos, 0, 4);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(config('app.app_name')) ?></title>
  <link href="<?= e(base_url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../app/views/partials/nav.php'; ?>

<main class="page">
  <section class="hero" style="--hero-image: url('<?= e(base_url('assets/img/main.webp')) ?>');">
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <h1>Planificación de viajes con información organizada</h1>
      <p>Consulta requisitos y trámites por destino, revisa experiencias de otros viajeros y publica la tuya de forma segura y moderada.</p>
      <div class="hero-actions">
        <a class="btn btn-primary" href="<?= e(base_url('requisitos.php')) ?>">Empezar ahora</a>
      </div>
    </div>
  </section>

  <div class="content-grid">
    <div class="col-main">
      <section class="card requirements-card">
        <div class="card-title">
          <span class="card-icon">🧭</span>
          <h2>Consultar requisitos por destino</h2>
        </div>
        <form class="requirements-form" method="get" action="<?= e(base_url('requisitos.php')) ?>">
          <div class="select-wrap">
            <select name="destino" required>
              <option value="" selected disabled>Seleccione un destino</option>
              <?php foreach ($destinos as $d): ?>
                <option value="<?= (int)$d['id_destino'] ?>">
                  <?= e($d['pais'] . ($d['ciudad'] ? ' - ' . $d['ciudad'] : '')) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <button class="btn btn-primary" type="submit">Consultar</button>
        </form>
        <p class="helper-text">La información se administra internamente e incluye la fecha de última actualización.</p>
      </section>

      <section class="card experience-card" style="--experience-image: url('<?= e(base_url('assets/img/experience.webp')) ?>');">
        <div class="experience-overlay"></div>
        <div class="experience-content">
          <h2>Experiencias de viajeros</h2>
          <p>Solo se muestra contenido aprobado por moderación.</p>
          <div class="experience-actions">
            <a class="btn btn-light" href="<?= e(base_url('experiencias.php')) ?>">Ver experiencias</a>
            <?php if (is_logged_in()): ?>
              <a class="btn btn-primary" href="<?= e(base_url('publicar_experiencia.php')) ?>">Publicar experiencia</a>
            <?php else: ?>
              <a class="btn btn-primary" href="<?= e(base_url('login.php')) ?>">Iniciar sesión para publicar</a>
            <?php endif; ?>
          </div>
        </div>
      </section>

      <section class="destinations" id="destinos">
        <div class="destinations-head">
          <h2>Destinos populares</h2>
          <a class="link-primary" href="<?= e(base_url('requisitos.php')) ?>">Ver todos</a>
        </div>
        <?php if (!$destinos_populares): ?>
          <p class="helper-text">Aún no hay destinos destacados.</p>
        <?php else: ?>
          <div class="destinations-grid">
            <?php foreach ($destinos_populares as $d): ?>
              <article class="destination-card">
                <img class="destination-image" src="<?= e(base_url('assets/img/main.webp')) ?>" alt="<?= e($d['pais']) ?>">
                <h3><?= e($d['ciudad'] ?: $d['pais']) ?></h3>
                <span><?= e($d['pais']) ?></span>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </div>

    <aside class="col-side">
      <section class="card notice-card">
        <div class="card-title">
          <span class="card-icon">🔔</span>
          <h2>Avisos de actualización</h2>
        </div>
        <?php if (!$avisos): ?>
          <p class="helper-text">No hay avisos recientes.</p>
        <?php else: ?>
          <div class="notice-list">
            <?php foreach ($avisos as $a): ?>
              <div class="notice-item">
                <div class="notice-header">
                  <strong><?= e($a['titulo_aviso']) ?></strong>
                  <span><?= e(date('Y-m-d', strtotime($a['fecha_publicacion']))) ?></span>
                </div>
                <div class="notice-meta">
                  <?= e($a['pais'] . ($a['ciudad'] ? ' - ' . $a['ciudad'] : '')) ?>
                </div>
                <div class="notice-body"><?= nl2br(e($a['detalle_aviso'])) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <a class="btn btn-outline btn-block" href="<?= e(base_url('avisos.php')) ?>">Ver historial de avisos</a>
      </section>

      <section class="card preview-card">
        <h2>Vista previa</h2>
        <div class="preview-box">
          <img src="<?= e(base_url('assets/img/main.webp')) ?>" alt="Vista previa">
          <p>Selecciona un destino para ver el mapa</p>
        </div>
      </section>

      <section class="card community-card">
        <h2>Comunidad</h2>
        <div class="community-grid">
          <div>
            <strong>1.2k+</strong>
            <span>Experiencias</span>
          </div>
          <div>
            <strong>450</strong>
            <span>Destinos</span>
          </div>
        </div>
      </section>
    </aside>
  </div>
</main>

<?php include __DIR__ . '/../app/views/partials/footer.php'; ?>
</body>
</html>
