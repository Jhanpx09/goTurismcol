<?php require_once __DIR__ . '/_admin_guard.php';
$current = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
function admin_nav_active(string $file, string $current): string {
  return $current === $file ? 'is-active' : '';
}

function destino_label(array $row): string {
  $label = $row['pais'] ?? '';
  if (!empty($row['ciudad'])) {
    $label .= ' - ' . $row['ciudad'];
  }
  return $label;
}

$pending_experiencias = (int)$pdo->query("SELECT COUNT(*) FROM experiencia_viajero WHERE estado_moderacion='pendiente'")->fetchColumn();
$destinos_activos = (int)$pdo->query("SELECT COUNT(*) FROM destino WHERE estado='activo'")->fetchColumn();
$avisos_activos = (int)$pdo->query("SELECT COUNT(*) FROM aviso_actualizacion WHERE estado='activo'")->fetchColumn();
$destinos_destacados = 0;
try {
  $destinos_destacados = (int)$pdo->query("SELECT COUNT(*) FROM destino_destacado WHERE estado='activo'")->fetchColumn();
} catch (PDOException $e) {
  $destinos_destacados = 0;
}

$activity = [];
$latest_req = $pdo->query("
  SELECT ar.fecha_actualizacion, r.titulo_requisito, d.pais, d.ciudad
  FROM actualizacion_requisito ar
  JOIN requisito_viaje r ON r.id_requisito = ar.id_requisito
  JOIN destino d ON d.id_destino = r.id_destino
  ORDER BY ar.fecha_actualizacion DESC
  LIMIT 1
")->fetch();
if ($latest_req) {
  $activity[] = [
    'timestamp' => strtotime($latest_req['fecha_actualizacion']),
    'color' => '#3b82f6',
    'title' => 'Actualizacion de requisito',
    'detail' => 'Se actualizo el requisito ' . $latest_req['titulo_requisito'] . ' para ' . destino_label($latest_req) . '.',
  ];
}

$latest_exp = $pdo->query("
  SELECT e.titulo, e.fecha_envio, d.pais, d.ciudad, u.correo
  FROM experiencia_viajero e
  JOIN destino d ON d.id_destino = e.id_destino
  JOIN usuario u ON u.id_usuario = e.id_usuario
  WHERE e.estado_moderacion='pendiente'
  ORDER BY e.fecha_envio DESC
  LIMIT 1
")->fetch();
if ($latest_exp) {
  $activity[] = [
    'timestamp' => strtotime($latest_exp['fecha_envio']),
    'color' => '#f59e0b',
    'title' => 'Experiencia pendiente',
    'detail' => 'Nuevo envio de ' . $latest_exp['correo'] . ' para ' . destino_label($latest_exp) . '.',
  ];
}

$latest_aviso = $pdo->query("
  SELECT a.titulo_aviso, a.fecha_publicacion, d.pais, d.ciudad
  FROM aviso_actualizacion a
  JOIN destino d ON d.id_destino = a.id_destino
  ORDER BY a.fecha_publicacion DESC
  LIMIT 1
")->fetch();
if ($latest_aviso) {
  $activity[] = [
    'timestamp' => strtotime($latest_aviso['fecha_publicacion']),
    'color' => '#22c55e',
    'title' => 'Aviso publicado',
    'detail' => 'Se publico "' . $latest_aviso['titulo_aviso'] . '" para ' . destino_label($latest_aviso) . '.',
  ];
}

usort($activity, function ($a, $b) {
  return $b['timestamp'] <=> $a['timestamp'];
});
$activity = array_slice($activity, 0, 4);

$admin_name = $admin['correo'] ?? 'Administrador';
$avatar = strtoupper(substr($admin_name, 0, 2));
$destacado_status_label = $destinos_destacados > 0 ? 'ACTIVO' : 'SIN CONFIGURAR';
$destacado_status_class = $destinos_destacados > 0 ? 'admin-status admin-status--success' : 'admin-status admin-status--muted';
$moderacion_label = $pending_experiencias > 0 ? 'Revisar (' . $pending_experiencias . ')' : 'Sin pendientes';
$moderacion_class = $pending_experiencias > 0 ? 'admin-btn admin-btn--primary' : 'admin-btn admin-btn--muted';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel administrativo | <?= e(config('app.app_name')) ?></title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
  <link href="<?= e(base_url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body class="admin-body">
<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="admin-sidebar-header">
      <div class="admin-brand">
        <img class="admin-logo" src="<?= e(base_url('assets/img/logo.webp')) ?>" alt="<?= e(config('app.app_name')) ?>">
        <span>Panel admin</span>
      </div>
    </div>
    <nav class="admin-nav">
      <div class="admin-nav-section">
        <p class="admin-nav-title">General</p>
        <a class="admin-nav-link <?= admin_nav_active('index.php', $current) ?>" href="<?= e(base_url('admin/index.php')) ?>">
          <span class="material-icons-round">dashboard</span>
          Panel administrativo
        </a>
        <a class="admin-nav-link <?= admin_nav_active('destinos.php', $current) ?>" href="<?= e(base_url('admin/destinos.php')) ?>">
          <span class="material-icons-round">flight_takeoff</span>
          Destinos
        </a>
        <a class="admin-nav-link <?= admin_nav_active('destinos_destacados.php', $current) ?>" href="<?= e(base_url('admin/destinos_destacados.php')) ?>">
          <span class="material-icons-round">stars</span>
          Destinos destacados
        </a>
      </div>
      <div class="admin-nav-section">
        <p class="admin-nav-title">Gestion</p>
        <a class="admin-nav-link <?= admin_nav_active('requisitos.php', $current) ?>" href="<?= e(base_url('admin/requisitos.php')) ?>">
          <span class="material-icons-round">assignment</span>
          Requisitos
        </a>
        <a class="admin-nav-link <?= admin_nav_active('experiencias.php', $current) ?>" href="<?= e(base_url('admin/experiencias.php')) ?>">
          <span class="material-icons-round">verified_user</span>
          Moderacion
          <?php if ($pending_experiencias > 0): ?>
            <span class="admin-nav-badge"><?= (int)$pending_experiencias ?></span>
          <?php endif; ?>
        </a>
        <a class="admin-nav-link <?= admin_nav_active('avisos.php', $current) ?>" href="<?= e(base_url('admin/avisos.php')) ?>">
          <span class="material-icons-round">campaign</span>
          Avisos
        </a>
      </div>
    </nav>
    <div class="admin-sidebar-actions">
      <a class="admin-action" href="<?= e(base_url('index.php')) ?>">
        <span class="material-icons-round">open_in_new</span>
        Ver sitio
      </a>
      <a class="admin-action admin-action--danger" href="<?= e(base_url('logout.php')) ?>">
        <span class="material-icons-round">logout</span>
        Salir
      </a>
    </div>
  </aside>

  <div class="admin-main">
    <header class="admin-topbar">
      <div>
        <h1>Panel administrativo</h1>
        <p>Bienvenido de nuevo, <?= e($admin_name) ?></p>
      </div>
      <div class="admin-topbar-actions">
        <label class="admin-search">
          <span class="material-icons-round">search</span>
          <input type="text" placeholder="Buscar..." aria-label="Buscar">
        </label>
        <div class="admin-avatar"><?= e($avatar) ?></div>
      </div>
    </header>

    <div class="admin-content">
      <div class="admin-main-content">
        <div class="admin-container">
          <div class="admin-section-head">
            <h2>Resumen General</h2>
            <p>Gestiona los aspectos clave de tu plataforma de viajes.</p>
          </div>

          <div class="admin-cards">
            <article class="admin-card" style="--accent:#2563eb; --accent-soft:#dbeafe; --accent-border:rgba(37, 99, 235, 0.3);">
              <div class="admin-card-top">
                <div class="admin-card-icon">
                  <span class="material-icons-round">flight_takeoff</span>
                </div>
              </div>
              <h3>Destinos</h3>
              <p>Crear y activar/desactivar destinos disponibles en la plataforma.</p>
              <a class="admin-card-link" href="<?= e(base_url('admin/destinos.php')) ?>">
                Configurar destinos
                <span class="material-icons-round">arrow_forward</span>
              </a>
            </article>

            <article class="admin-card" style="--accent:#f59e0b; --accent-soft:#fef3c7; --accent-border:rgba(245, 158, 11, 0.3);">
              <div class="admin-card-top">
                <div class="admin-card-icon">
                  <span class="material-icons-round">stars</span>
                </div>
                <span class="<?= $destacado_status_class ?>"><?= e($destacado_status_label) ?></span>
              </div>
              <h3>Destinos destacados</h3>
              <p>Gestionar la seccion de publicidad y banners en la portada.</p>
              <a class="admin-card-link" href="<?= e(base_url('admin/destinos_destacados.php')) ?>">
                Gestionar destacados
                <span class="material-icons-round">arrow_forward</span>
              </a>
            </article>

            <article class="admin-card" style="--accent:#10b981; --accent-soft:#d1fae5; --accent-border:rgba(16, 185, 129, 0.3);">
              <div class="admin-card-top">
                <div class="admin-card-icon">
                  <span class="material-icons-round">assignment_turned_in</span>
                </div>
              </div>
              <h3>Requisitos</h3>
              <p>Gestionar requisitos de viaje y registrar actualizaciones legales.</p>
              <a class="admin-card-link" href="<?= e(base_url('admin/requisitos.php')) ?>">
                Ver requisitos
                <span class="material-icons-round">arrow_forward</span>
              </a>
            </article>

            <article class="admin-card admin-card--alert" style="--accent:#8b5cf6; --accent-soft:#ede9fe; --accent-border:rgba(139, 92, 246, 0.3);">
              <div class="admin-card-top">
                <div class="admin-card-icon">
                  <span class="material-icons-round">gavel</span>
                </div>
              </div>
              <h3>Moderacion</h3>
              <p>Aprobar o rechazar experiencias pendientes enviadas por usuarios.</p>
              <div class="admin-card-actions">
                <a class="<?= $moderacion_class ?>" href="<?= e(base_url('admin/experiencias.php')) ?>">
                  <?= e($moderacion_label) ?>
                </a>
              </div>
            </article>

            <article class="admin-card admin-card--wide admin-card--row" style="--accent:#f97316; --accent-soft:#ffedd5; --accent-border:rgba(249, 115, 22, 0.3);">
              <div class="admin-card-icon">
                <span class="material-icons-round">campaign</span>
              </div>
              <div class="admin-card-content">
                <h3>Avisos</h3>
                <p>Publicar avisos importantes por destino para los viajeros.</p>
              </div>
              <a class="admin-card-cta" href="<?= e(base_url('admin/avisos.php')) ?>">Publicar nuevo aviso</a>
            </article>
          </div>
        </div>
      </div>

      <aside class="admin-activity">
        <div class="admin-activity-head">
          <h3>Actividad reciente</h3>
          <button class="admin-icon-button" type="button" aria-label="Actualizar">
            <span class="material-icons-round">refresh</span>
          </button>
        </div>
        <div class="admin-activity-body">
          <?php if (!$activity): ?>
            <p class="admin-empty">No hay actividad reciente disponible.</p>
          <?php else: ?>
            <div class="admin-timeline">
              <?php foreach ($activity as $item): ?>
                <div class="admin-timeline-item" style="--dot-color: <?= e($item['color']) ?>;">
                  <div class="admin-timeline-dot"></div>
                  <p class="admin-timeline-title"><?= e($item['title']) ?></p>
                  <p class="admin-timeline-detail"><?= e($item['detail']) ?></p>
                  <p class="admin-timeline-time"><?= e(date('Y-m-d H:i', $item['timestamp'])) ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <div class="admin-quick-stats">
            <h4>Estadisticas rapidas</h4>
            <div class="admin-quick-grid">
              <div>
                <strong><?= (int)$destinos_activos ?></strong>
                <span>Destinos activos</span>
              </div>
              <div>
                <strong><?= (int)$pending_experiencias ?></strong>
                <span>Pendientes</span>
              </div>
            </div>
          </div>
        </div>
      </aside>
    </div>
  </div>
</div>

<button class="admin-theme-toggle" type="button" id="admin-theme-toggle" aria-label="Cambiar tema">
  <span class="material-icons-round icon-dark">dark_mode</span>
  <span class="material-icons-round icon-light">light_mode</span>
</button>

<script>
  (function () {
    var body = document.body;
    var toggle = document.getElementById('admin-theme-toggle');
    if (!toggle) return;
    var stored = localStorage.getItem('admin-theme');
    if (stored === 'dark') {
      body.classList.add('admin-dark');
    }
    toggle.addEventListener('click', function () {
      body.classList.toggle('admin-dark');
      localStorage.setItem('admin-theme', body.classList.contains('admin-dark') ? 'dark' : 'light');
    });
  })();
</script>
</body>
</html>
