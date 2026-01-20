<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function load_user_with_roles(int $id): array {
  $pdo = db();
  $stmt = $pdo->prepare("
    SELECT id_usuario, correo, nombre, apellido, telefono, id_destino, foto_path, fecha_registro, estado
    FROM usuario
    WHERE id_usuario = ?
  ");
  $stmt->execute([$id]);
  $u = $stmt->fetch();
  if (!$u) return [];

  $stmt = $pdo->prepare("
    SELECT r.nombre_rol
    FROM usuario_rol ur
    JOIN rol r ON r.id_rol = ur.id_rol
    WHERE ur.id_usuario = ?
  ");
  $stmt->execute([$id]);
  $u['roles'] = array_map(fn($row) => $row['nombre_rol'], $stmt->fetchAll());
  return $u;
}

function login_user(int $id): void {
  start_session();
  $_SESSION['user'] = load_user_with_roles($id);
}

function logout_user(): void {
  start_session();
  $_SESSION = [];
  session_destroy();
}
