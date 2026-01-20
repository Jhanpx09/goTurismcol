<?php require_once __DIR__ . '/_admin_guard.php';
$errors = [];
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$new_modal = isset($_GET['new']) ? 1 : 0;
$default_photo = 'assets/img/user-default.svg';

$roles = $pdo->query("SELECT id_rol, nombre_rol FROM rol ORDER BY nombre_rol")->fetchAll();
$role_ids_all = array_map(fn($row) => (int)$row['id_rol'], $roles ?: []);
$admin_role_id = null;
foreach ($roles as $role) {
  if (($role['nombre_rol'] ?? '') === 'Administrador') {
    $admin_role_id = (int)$role['id_rol'];
    break;
  }
}

$destinos = $pdo->query("SELECT id_destino, pais, ciudad FROM destino WHERE estado='activo' ORDER BY pais, ciudad")->fetchAll();
$destino_ids = array_map(fn($row) => (int)$row['id_destino'], $destinos ?: []);

function save_user_photo(?array $file, array &$errors): ?string {
  if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    return null;
  }
  if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
    $errors[] = "Error al subir la foto.";
    return null;
  }
  $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
  $allowed = ['jpg', 'jpeg', 'png', 'webp'];
  if (!$ext || !in_array($ext, $allowed, true)) {
    $errors[] = "Formato de foto no valido. Use jpg, png o webp.";
    return null;
  }
  $dir = __DIR__ . '/../assets/usuarios';
  if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
    $errors[] = "No se pudo crear la carpeta de fotos.";
    return null;
  }
  $unique = str_replace('.', '', uniqid('user_', true));
  $filename = $unique . '.' . $ext;
  $dest_path = $dir . '/' . $filename;
  if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
    $errors[] = "No se pudo guardar la foto.";
    return null;
  }
  return 'assets/usuarios/' . $filename;
}

function delete_user_photo(?string $path, string $default): void {
  if (!$path || $path === $default) return;
  $public_root = realpath(__DIR__ . '/..');
  $images_dir = realpath($public_root . '/assets/usuarios');
  $candidate = realpath($public_root . '/' . ltrim($path, '/'));
  if ($public_root && $images_dir && $candidate && strpos($candidate, $images_dir) === 0) {
    @unlink($candidate);
  }
}

function admin_count(PDO $pdo): int {
  return (int)$pdo->query("
    SELECT COUNT(*)
    FROM usuario u
    JOIN usuario_rol ur ON ur.id_usuario = u.id_usuario
    JOIN rol r ON r.id_rol = ur.id_rol
    WHERE r.nombre_rol='Administrador' AND u.estado='activo'
  ")->fetchColumn();
}

function user_has_admin(PDO $pdo, int $id): bool {
  $stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM usuario_rol ur
    JOIN rol r ON r.id_rol = ur.id_rol
    WHERE ur.id_usuario=? AND r.nombre_rol='Administrador'
  ");
  $stmt->execute([$id]);
  return (int)$stmt->fetchColumn() > 0;
}

function filter_role_ids(array $input, array $allowed): array {
  $ids = array_map('intval', $input);
  $ids = array_values(array_unique(array_intersect($ids, $allowed)));
  return $ids;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $action = $_POST['action'] ?? '';

  if ($action === 'create') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $id_destino = (int)($_POST['id_destino'] ?? 0);
    $correo = trim($_POST['correo'] ?? '');
    $pass = $_POST['contrasena'] ?? '';
    $estado = $_POST['estado'] ?? 'activo';
    $role_ids = filter_role_ids($_POST['roles'] ?? [], $role_ids_all);

    if ($nombre === '') $errors[] = "El nombre es obligatorio.";
    if ($apellido === '') $errors[] = "El apellido es obligatorio.";
    if ($telefono === '') $errors[] = "El telefono es obligatorio.";
    if ($telefono !== '' && strlen(preg_replace('/\\D/', '', $telefono)) < 7) {
      $errors[] = "El telefono debe tener al menos 7 digitos.";
    }
    if ($id_destino <= 0 || !in_array($id_destino, $destino_ids, true)) $errors[] = "Seleccione un pais valido.";
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errors[] = "Ingrese un correo valido.";
    if (strlen($pass) < 8) $errors[] = "La contrasena debe tener al menos 8 caracteres.";
    if (!in_array($estado, ['activo', 'inactivo'], true)) $errors[] = "Estado invalido.";
    if (!$role_ids) $errors[] = "Seleccione al menos un rol.";

    if (!$errors) {
      $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE correo = ?");
      $stmt->execute([$correo]);
      if ($stmt->fetch()) {
        $errors[] = "Este correo ya esta registrado.";
      } else {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $foto_path = save_user_photo($_FILES['foto'] ?? null, $errors);
        if (!$foto_path) $foto_path = $default_photo;
        if (!$errors) {
          $stmt = $pdo->prepare("
            INSERT INTO usuario (correo, nombre, apellido, telefono, id_destino, foto_path, contrasena_hash, estado)
            VALUES (?,?,?,?,?,?,?,?)
          ");
          $stmt->execute([$correo, $nombre, $apellido, $telefono, $id_destino, $foto_path, $hash, $estado]);
          $id = (int)$pdo->lastInsertId();
          $stmt = $pdo->prepare("INSERT INTO usuario_rol (id_usuario, id_rol) VALUES (?,?)");
          foreach ($role_ids as $rid) {
            $stmt->execute([$id, $rid]);
          }
          redirect('admin/usuarios.php?created=1');
        }
      }
    }
  } elseif ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $id_destino = (int)($_POST['id_destino'] ?? 0);
    $correo = trim($_POST['correo'] ?? '');
    $pass = $_POST['contrasena'] ?? '';
    $estado = $_POST['estado'] ?? 'activo';
    $role_ids = filter_role_ids($_POST['roles'] ?? [], $role_ids_all);

    if (!$id) $errors[] = "Usuario invalido.";
    if ($nombre === '') $errors[] = "El nombre es obligatorio.";
    if ($apellido === '') $errors[] = "El apellido es obligatorio.";
    if ($telefono === '') $errors[] = "El telefono es obligatorio.";
    if ($telefono !== '' && strlen(preg_replace('/\\D/', '', $telefono)) < 7) {
      $errors[] = "El telefono debe tener al menos 7 digitos.";
    }
    if ($id_destino <= 0 || !in_array($id_destino, $destino_ids, true)) $errors[] = "Seleccione un pais valido.";
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errors[] = "Ingrese un correo valido.";
    if ($pass !== '' && strlen($pass) < 8) $errors[] = "La contrasena debe tener al menos 8 caracteres.";
    if (!in_array($estado, ['activo', 'inactivo'], true)) $errors[] = "Estado invalido.";
    if (!$role_ids) $errors[] = "Seleccione al menos un rol.";

    $stmt = $pdo->prepare("SELECT id_usuario, correo, foto_path FROM usuario WHERE id_usuario=?");
    $stmt->execute([$id]);
    $current = $stmt->fetch();
    if (!$current) $errors[] = "Usuario invalido.";

    $is_self = $id === (int)$admin['id_usuario'];
    $is_admin_selected = $admin_role_id && in_array($admin_role_id, $role_ids, true);
    if ($is_self && $estado === 'inactivo') {
      $errors[] = "No puedes desactivar tu propio usuario.";
    }
    if ($is_self && !$is_admin_selected) {
      $errors[] = "No puedes quitarte el rol Administrador.";
    }
    if ($admin_role_id && !$is_admin_selected && $id && user_has_admin($pdo, $id)) {
      if (admin_count($pdo) <= 1) {
        $errors[] = "Debe existir al menos un administrador activo.";
      }
    }

    if (!$errors) {
      if ($correo !== ($current['correo'] ?? '')) {
        $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE correo = ? AND id_usuario <> ?");
        $stmt->execute([$correo, $id]);
        if ($stmt->fetch()) {
          $errors[] = "Este correo ya esta registrado.";
        }
      }
    }

    $new_photo = null;
    $final_photo = $current['foto_path'] ?? $default_photo;
    if (!$errors) {
      $new_photo = save_user_photo($_FILES['foto'] ?? null, $errors);
      $final_photo = $new_photo ?: ($current['foto_path'] ?? $default_photo);
      if (!$final_photo) $final_photo = $default_photo;
    }

    if (!$errors) {
      $fields = "
        UPDATE usuario
        SET correo=?, nombre=?, apellido=?, telefono=?, id_destino=?, foto_path=?, estado=?
      ";
      $params = [$correo, $nombre, $apellido, $telefono, $id_destino, $final_photo, $estado];
      if ($pass !== '') {
        $fields .= ", contrasena_hash=?";
        $params[] = password_hash($pass, PASSWORD_BCRYPT);
      }
      $fields .= " WHERE id_usuario=?";
      $params[] = $id;
      $pdo->prepare($fields)->execute($params);

      $pdo->prepare("DELETE FROM usuario_rol WHERE id_usuario=?")->execute([$id]);
      $stmt = $pdo->prepare("INSERT INTO usuario_rol (id_usuario, id_rol) VALUES (?,?)");
      foreach ($role_ids as $rid) {
        $stmt->execute([$id, $rid]);
      }

      if ($new_photo && !empty($current['foto_path'])) {
        delete_user_photo($current['foto_path'], $default_photo);
      }
      if ($is_self) {
        login_user($id);
      }
      redirect('admin/usuarios.php?updated=1');
    }
  } elseif ($action === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) $errors[] = "Usuario invalido.";
    if ($id === (int)$admin['id_usuario']) $errors[] = "No puedes desactivar tu propio usuario.";
    $stmt = $pdo->prepare("SELECT estado FROM usuario WHERE id_usuario=?");
    $stmt->execute([$id]);
    $current_state = $stmt->fetchColumn();
    if ($current_state === 'activo' && user_has_admin($pdo, $id) && admin_count($pdo) <= 1) {
      $errors[] = "Debe existir al menos un administrador activo.";
    }
    if (!$errors) {
      $pdo->prepare("UPDATE usuario SET estado = IF(estado='activo','inactivo','activo') WHERE id_usuario=?")->execute([$id]);
      redirect('admin/usuarios.php?updated=1');
    }
  } elseif ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) $errors[] = "Usuario invalido.";
    if ($id === (int)$admin['id_usuario']) $errors[] = "No puedes eliminar tu propio usuario.";
    if ($id && user_has_admin($pdo, $id) && admin_count($pdo) <= 1) {
      $errors[] = "Debe existir al menos un administrador activo.";
    }
    if (!$errors) {
      $stmt = $pdo->prepare("SELECT foto_path FROM usuario WHERE id_usuario=?");
      $stmt->execute([$id]);
      $row = $stmt->fetch();
      $pdo->prepare("DELETE FROM usuario WHERE id_usuario=?")->execute([$id]);
      if (!empty($row['foto_path'])) {
        delete_user_photo($row['foto_path'], $default_photo);
      }
      redirect('admin/usuarios.php?deleted=1');
    }
  }
}

$items = $pdo->query("
  SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.telefono, u.foto_path, u.estado, u.fecha_registro,
         d.pais, d.ciudad,
         GROUP_CONCAT(r.nombre_rol ORDER BY r.nombre_rol SEPARATOR ', ') AS roles
  FROM usuario u
  LEFT JOIN destino d ON d.id_destino = u.id_destino
  LEFT JOIN usuario_rol ur ON ur.id_usuario = u.id_usuario
  LEFT JOIN rol r ON r.id_rol = ur.id_rol
  GROUP BY u.id_usuario
  ORDER BY u.fecha_registro DESC
")->fetchAll();

$edit_item = null;
$edit_roles = [];
if ($edit_id) {
  $stmt = $pdo->prepare("SELECT * FROM usuario WHERE id_usuario=?");
  $stmt->execute([$edit_id]);
  $edit_item = $stmt->fetch();

  if ($edit_item) {
    $stmt = $pdo->prepare("SELECT id_rol FROM usuario_rol WHERE id_usuario=?");
    $stmt->execute([$edit_id]);
    $edit_roles = array_map(fn($row) => (int)$row['id_rol'], $stmt->fetchAll());
  }
}

$page_title = 'Usuarios y roles';
$page_subtitle = 'Administra perfiles y permisos de los usuarios registrados.';
?>
<?php include __DIR__ . '/_layout_start.php'; ?>
  <div class="admin-section-head">
    <h2>Usuarios y roles</h2>
    <p>Gestiona los perfiles, roles y estados de los usuarios de la plataforma.</p>
  </div>

  <div class="admin-page">
  <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <?php if (!empty($_GET['created'])): ?>
    <div class="alert alert-success">Usuario creado correctamente.</div>
  <?php endif; ?>
  <?php if (!empty($_GET['updated'])): ?>
    <div class="alert alert-success">Usuario actualizado correctamente.</div>
  <?php endif; ?>
  <?php if (!empty($_GET['deleted'])): ?>
    <div class="alert alert-success">Usuario eliminado correctamente.</div>
  <?php endif; ?>

  <div class="d-flex justify-content-end mb-3">
    <a class="btn btn-primary" href="<?= e(base_url('admin/usuarios.php?new=1')) ?>">Nuevo usuario</a>
  </div>

  <?php if ($edit_id && !$edit_item): ?>
    <div class="alert alert-warning">No se encontro el usuario para editar.</div>
  <?php endif; ?>

  <?php if ($new_modal): ?>
    <div class="req-modal-backdrop">
      <div class="req-modal">
        <div class="req-modal-head">
          <h2 class="h6 mb-0">Crear usuario</h2>
          <a class="req-modal-close" href="<?= e(base_url('admin/usuarios.php')) ?>">Cancelar</a>
        </div>
        <form method="post" class="row g-2 mt-3" enctype="multipart/form-data">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="action" value="create">
          <div class="col-md-6">
            <input class="form-control form-control-sm" name="nombre" placeholder="Nombre" required>
          </div>
          <div class="col-md-6">
            <input class="form-control form-control-sm" name="apellido" placeholder="Apellido" required>
          </div>
          <div class="col-md-6">
            <input class="form-control form-control-sm" name="telefono" placeholder="Telefono" required>
          </div>
          <div class="col-md-6">
            <input class="form-control form-control-sm" name="correo" placeholder="Correo" required type="email">
          </div>
          <div class="col-12">
            <select class="form-select form-select-sm" name="id_destino" required>
              <option value="" disabled selected>Seleccione un pais</option>
              <?php foreach ($destinos as $d): ?>
                <option value="<?= (int)$d['id_destino'] ?>"><?= e($d['pais'] . ($d['ciudad'] ? ' - ' . $d['ciudad'] : '')) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <input class="form-control form-control-sm" name="contrasena" placeholder="Contrasena" required type="password">
          </div>
          <div class="col-md-6">
            <select class="form-select form-select-sm" name="estado">
              <option value="activo" selected>Activo</option>
              <option value="inactivo">Inactivo</option>
            </select>
          </div>
          <div class="col-12">
            <input class="form-control form-control-sm" type="file" name="foto" accept=".png,.jpg,.jpeg,.webp">
            <div class="form-text">Foto opcional. Si no se sube, se usa la imagen por defecto.</div>
          </div>
          <div class="col-12">
            <div class="small text-secondary mb-1">Roles</div>
            <div class="d-flex flex-wrap gap-3">
              <?php foreach ($roles as $role): ?>
                <label class="form-check form-check-inline mb-0">
                  <input class="form-check-input" type="checkbox" name="roles[]" value="<?= (int)$role['id_rol'] ?>" <?= ($role['nombre_rol'] ?? '') === 'Viajero' ? 'checked' : '' ?>>
                  <span class="form-check-label"><?= e($role['nombre_rol']) ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="col-12"><button class="btn btn-primary w-100">Guardar</button></div>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($edit_item): ?>
    <div class="req-modal-backdrop">
      <div class="req-modal">
        <div class="req-modal-head">
          <h2 class="h6 mb-0">Editar usuario</h2>
          <a class="req-modal-close" href="<?= e(base_url('admin/usuarios.php')) ?>">Cancelar</a>
        </div>
        <form method="post" class="row g-2 mt-3" enctype="multipart/form-data">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" value="<?= (int)$edit_item['id_usuario'] ?>">
          <div class="col-md-6">
            <input class="form-control form-control-sm" name="nombre" placeholder="Nombre" required value="<?= e($edit_item['nombre'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <input class="form-control form-control-sm" name="apellido" placeholder="Apellido" required value="<?= e($edit_item['apellido'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <input class="form-control form-control-sm" name="telefono" placeholder="Telefono" required value="<?= e($edit_item['telefono'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <input class="form-control form-control-sm" name="correo" placeholder="Correo" required type="email" value="<?= e($edit_item['correo'] ?? '') ?>">
          </div>
          <div class="col-12">
            <select class="form-select form-select-sm" name="id_destino" required>
              <option value="" disabled>Seleccione un pais</option>
              <?php foreach ($destinos as $d): ?>
                <option value="<?= (int)$d['id_destino'] ?>" <?= (int)$edit_item['id_destino'] === (int)$d['id_destino'] ? 'selected' : '' ?>>
                  <?= e($d['pais'] . ($d['ciudad'] ? ' - ' . $d['ciudad'] : '')) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <input class="form-control form-control-sm" name="contrasena" placeholder="Nueva contrasena (opcional)" type="password">
          </div>
          <div class="col-md-6">
            <select class="form-select form-select-sm" name="estado">
              <option value="activo" <?= ($edit_item['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
              <option value="inactivo" <?= ($edit_item['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
            </select>
          </div>
          <div class="col-12">
            <input class="form-control form-control-sm" type="file" name="foto" accept=".png,.jpg,.jpeg,.webp">
            <div class="form-text">Sube una nueva foto para reemplazar la actual.</div>
          </div>
          <div class="col-12">
            <div class="small text-secondary mb-1">Roles</div>
            <div class="d-flex flex-wrap gap-3">
              <?php foreach ($roles as $role): ?>
                <?php $checked = in_array((int)$role['id_rol'], $edit_roles, true); ?>
                <label class="form-check form-check-inline mb-0">
                  <input class="form-check-input" type="checkbox" name="roles[]" value="<?= (int)$role['id_rol'] ?>" <?= $checked ? 'checked' : '' ?>>
                  <span class="form-check-label"><?= e($role['nombre_rol']) ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="col-12"><button class="btn btn-primary w-100">Actualizar</button></div>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">
      <h2 class="h6">Listado</h2>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Foto</th>
              <th>Nombre</th>
              <th>Correo</th>
              <th>Pais</th>
              <th>Roles</th>
              <th>Estado</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($items as $it): ?>
            <?php $display_name = user_display_name($it['nombre'] ?? '', $it['apellido'] ?? '', $it['correo'] ?? ''); ?>
            <tr>
              <td><?= (int)$it['id_usuario'] ?></td>
              <td>
                <img src="<?= e(asset_url(user_photo_path($it['foto_path'] ?? $default_photo))) ?>" alt="Foto de <?= e($display_name) ?>" width="44" height="44" class="rounded-circle border" style="object-fit:cover;">
              </td>
              <td><?= e($display_name) ?></td>
              <td><?= e($it['correo']) ?></td>
              <td><?= e($it['pais'] . ($it['ciudad'] ? ' - ' . $it['ciudad'] : '')) ?></td>
              <td><?= e($it['roles'] ?: 'Sin rol') ?></td>
              <td><span class="badge text-bg-<?= $it['estado']==='activo'?'success':'secondary' ?>"><?= e($it['estado']) ?></span></td>
              <td class="text-end">
                <a class="btn btn-outline-primary btn-sm me-1" href="<?= e(base_url('admin/usuarios.php?edit=' . (int)$it['id_usuario'])) ?>">Editar</a>
                <form method="post" class="d-inline">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= (int)$it['id_usuario'] ?>">
                  <button class="btn btn-outline-secondary btn-sm"><?= $it['estado']==='activo'?'Desactivar':'Activar' ?></button>
                </form>
                <form method="post" class="d-inline" onsubmit="return confirm('Eliminar este usuario?');">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int)$it['id_usuario'] ?>">
                  <button class="btn btn-outline-danger btn-sm">Eliminar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  </div>
<?php include __DIR__ . '/_layout_end.php'; ?>
