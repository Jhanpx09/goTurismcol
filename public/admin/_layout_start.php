<?php
if (!function_exists('admin_nav_active')) {
  function admin_nav_active(string $file, string $current): string {
    return $current === $file ? 'is-active' : '';
  }
}

$page_title = $page_title ?? 'Panel administrativo';
$page_subtitle = $page_subtitle ?? 'Gestiona la plataforma';
$current = $current ?? basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$admin_name = $admin['correo'] ?? 'Administrador';
$avatar = strtoupper(substr($admin_name, 0, 2));
$pending_experiencias = $pending_experiencias ?? (int)$pdo->query("SELECT COUNT(*) FROM experiencia_viajero WHERE estado_moderacion='pendiente'")->fetchColumn();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($page_title) ?> | <?= e(config('app.app_name')) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
  <link href="<?= e(asset_url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body class="admin-body">
<canvas class="admin-particles" id="admin-particles" aria-hidden="true"></canvas>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="admin-sidebar-header">
      <div class="admin-brand">
        <img class="admin-logo" src="<?= e(asset_url('assets/img/logo.webp')) ?>" alt="<?= e(config('app.app_name')) ?>">
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
        <a class="admin-nav-link <?= admin_nav_active('hero_slider.php', $current) ?>" href="<?= e(base_url('admin/hero_slider.php')) ?>">
          <span class="material-icons-round">slideshow</span>
          Slider portada
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
        <a class="admin-nav-link <?= admin_nav_active('usuarios.php', $current) ?>" href="<?= e(base_url('admin/usuarios.php')) ?>">
          <span class="material-icons-round">group</span>
          Usuarios y roles
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
        <h1><?= e($page_title) ?></h1>
        <p><?= e($page_subtitle) ?></p>
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
