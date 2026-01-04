<?php
function e(string $s) : string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function config(string $key, $default=null) {
  static $cfg = null;
  if (!$cfg) $cfg = require __DIR__ . '/config.php';
  $parts = explode('.', $key);
  $val = $cfg;
  foreach ($parts as $p) {
    if (!is_array($val) || !array_key_exists($p, $val)) return $default;
    $val = $val[$p];
  }
  return $val;
}

function base_url(string $path='') : string {
  $base = rtrim(config('app.base_url', ''), '/');
  $path = ltrim($path, '/');
  return $path ? $base . '/' . $path : $base;
}

function redirect(string $path) : void {
  header("Location: " . base_url($path));
  exit;
}

function start_session(): void {
  $name = config('security.session_name', 'PHPSESSID');
  if (session_status() === PHP_SESSION_NONE) {
    session_name($name);
    session_start();
  }
}

function is_logged_in(): bool {
  start_session();
  return !empty($_SESSION['user']);
}

function current_user() {
  start_session();
  return $_SESSION['user'] ?? null;
}

function has_role(string $role): bool {
  $u = current_user();
  if (!$u) return false;
  return in_array($role, $u['roles'] ?? [], true);
}

function require_login(): void {
  if (!is_logged_in()) redirect('login.php');
}

function require_admin(): void {
  require_login();
  if (!has_role('Administrador')) {
    http_response_code(403);
    echo "<h1>403 - Acceso denegado</h1><p>No tiene permisos para acceder a esta sección.</p>";
    exit;
  }
}

function csrf_token(): string {
  start_session();
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
  return $_SESSION['csrf'];
}

function csrf_check(): void {
  start_session();
  $t = $_POST['csrf'] ?? '';
  if (!$t || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $t)) {
    http_response_code(400);
    echo "<h1>Solicitud inválida</h1><p>Token CSRF inválido.</p>";
    exit;
  }
}
