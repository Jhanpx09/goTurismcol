<?php require_once __DIR__ . '/_admin_guard.php';
$errors = [];
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;

function validate_flag_upload(?array $file, array &$errors): ?string {
  if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    return null;
  }
  if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
    $errors[] = "Error al subir la bandera.";
    return null;
  }
  $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
  $allowed = ['jpg', 'jpeg', 'png', 'webp'];
  if (!$ext || !in_array($ext, $allowed, true)) {
    $errors[] = "Formato de bandera no válido. Use jpg, png o webp.";
    return null;
  }
  return $ext;
}

function save_flag_upload(array $file, int $destino_id, string $ext, array &$errors): ?string {
  $dir = __DIR__ . '/../assets/flags';
  if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
    $errors[] = "No se pudo crear la carpeta de banderas.";
    return null;
  }
  $filename = 'flag_' . $destino_id . '.' . $ext;
  $dest_path = $dir . '/' . $filename;
  if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
    $errors[] = "No se pudo guardar la bandera.";
    return null;
  }
  return 'assets/flags/' . $filename;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $action = $_POST['action'] ?? '';
  if ($action === 'create') {
    $pais = trim($_POST['pais'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $desc = trim($_POST['descripcion'] ?? '');
    $flag_ext = validate_flag_upload($_FILES['bandera'] ?? null, $errors);
    if (!$pais) $errors[] = "El país es obligatorio.";
    if (!$errors) {
      $pdo->prepare("INSERT INTO destino (pais, ciudad, descripcion_general) VALUES (?,?,?)")
          ->execute([$pais, $ciudad ?: null, $desc ?: null]);
      $nuevo_id = (int)$pdo->lastInsertId();
      if ($flag_ext) {
        $path = save_flag_upload($_FILES['bandera'], $nuevo_id, $flag_ext, $errors);
        if ($path) {
          $pdo->prepare("UPDATE destino SET bandera_path=? WHERE id_destino=?")->execute([$path, $nuevo_id]);
        }
      }
      if (!$errors) {
        redirect('admin/destinos.php');
      }
    }
  } elseif ($action === 'update') {
    $id = (int)($_POST['id_destino'] ?? 0);
    $pais = trim($_POST['pais'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $desc = trim($_POST['descripcion'] ?? '');
    $flag_ext = validate_flag_upload($_FILES['bandera'] ?? null, $errors);
    if (!$id) $errors[] = "Destino inválido.";
    if (!$pais) $errors[] = "El país es obligatorio.";
    if (!$errors) {
      $pdo->prepare("UPDATE destino SET pais=?, ciudad=?, descripcion_general=? WHERE id_destino=?")
          ->execute([$pais, $ciudad ?: null, $desc ?: null, $id]);
      if (!empty($_POST['clear_flag'])) {
        $pdo->prepare("UPDATE destino SET bandera_path=NULL WHERE id_destino=?")->execute([$id]);
      } elseif ($flag_ext) {
        $path = save_flag_upload($_FILES['bandera'], $id, $flag_ext, $errors);
        if ($path) {
          $pdo->prepare("UPDATE destino SET bandera_path=? WHERE id_destino=?")->execute([$path, $id]);
        }
      }
      if (!$errors) {
        redirect('admin/destinos.php?edit=' . $id);
      }
    }
  } elseif ($action === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare("UPDATE destino SET estado = IF(estado='activo','inactivo','activo') WHERE id_destino=?")->execute([$id]);
    redirect('admin/destinos.php');
  } elseif ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
      $stmt = $pdo->prepare("SELECT bandera_path FROM destino WHERE id_destino=?");
      $stmt->execute([$id]);
      $row = $stmt->fetch();
      $pdo->prepare("DELETE FROM destino WHERE id_destino=?")->execute([$id]);
      if (!empty($row['bandera_path'])) {
        $public_root = realpath(__DIR__ . '/..');
        $flags_dir = realpath($public_root . '/assets/flags');
        $candidate = realpath($public_root . '/' . ltrim($row['bandera_path'], '/'));
        if ($public_root && $flags_dir && $candidate && strpos($candidate, $flags_dir) === 0) {
          @unlink($candidate);
        }
      }
    }
    redirect('admin/destinos.php');
  }
}
$items = $pdo->query("SELECT * FROM destino ORDER BY estado DESC, pais, ciudad")->fetchAll();
$edit_item = null;
if ($edit_id) {
  $stmt = $pdo->prepare("SELECT * FROM destino WHERE id_destino=?");
  $stmt->execute([$edit_id]);
  $edit_item = $stmt->fetch();
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Destinos | Panel admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e(base_url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/_nav.php'; ?>
<main class="container py-4">
  <h1 class="h4 mb-3">Destinos</h1>
  <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h2 class="h6">Crear destino</h2>
      <form method="post" class="row g-2" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="create">
        <div class="col-md-4"><input class="form-control" name="pais" placeholder="País" required></div>
        <div class="col-md-4"><input class="form-control" name="ciudad" placeholder="Ciudad (opcional)"></div>
        <div class="col-md-4"><input class="form-control" name="descripcion" placeholder="Descripción (opcional)"></div>
        <div class="col-md-6">
          <input class="form-control" type="file" name="bandera" accept=".png,.jpg,.jpeg,.webp">
          <div class="form-text">Bandera en JPG/PNG/WebP. Se almacena en assets/flags/.</div>
        </div>
        <div class="col-12 d-grid"><button class="btn btn-primary">Guardar</button></div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h2 class="h6">Editar destino</h2>
      <?php if (!$edit_item): ?>
        <p class="text-secondary mb-0">Seleccione un destino desde el listado para editarlo.</p>
      <?php else: ?>
        <form method="post" class="row g-2" enctype="multipart/form-data">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id_destino" value="<?= (int)$edit_item['id_destino'] ?>">
          <div class="col-md-4"><input class="form-control" name="pais" value="<?= e($edit_item['pais']) ?>" required></div>
          <div class="col-md-4"><input class="form-control" name="ciudad" value="<?= e($edit_item['ciudad'] ?? '') ?>" placeholder="Ciudad (opcional)"></div>
          <div class="col-md-4"><input class="form-control" name="descripcion" value="<?= e($edit_item['descripcion_general'] ?? '') ?>" placeholder="Descripción (opcional)"></div>
          <div class="col-md-6">
            <input class="form-control" type="file" name="bandera" accept=".png,.jpg,.jpeg,.webp">
            <div class="form-text">Suba una nueva bandera para reemplazar la actual.</div>
          </div>
          <div class="col-md-6 d-flex align-items-end gap-2">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="clear_flag" id="clear_flag">
              <label class="form-check-label" for="clear_flag">Quitar bandera actual</label>
            </div>
          </div>
          <div class="col-12 d-grid"><button class="btn btn-primary">Actualizar</button></div>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <h2 class="h6">Listado</h2>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead><tr><th>ID</th><th>Bandera</th><th>Destino</th><th>Estado</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td><?= (int)$it['id_destino'] ?></td>
              <td>
                <?php if (!empty($it['bandera_path'])): ?>
                  <img src="<?= e(base_url($it['bandera_path'])) ?>" alt="Bandera" width="48" height="48" class="rounded">
                <?php else: ?>
                  <span class="text-secondary small">Sin imagen</span>
                <?php endif; ?>
              </td>
              <td><?= e($it['pais'] . ($it['ciudad'] ? ' - ' . $it['ciudad'] : '')) ?></td>
              <td><span class="badge text-bg-<?= $it['estado']==='activo'?'success':'secondary' ?>"><?= e($it['estado']) ?></span></td>
              <td class="text-end">
                <a class="btn btn-outline-primary btn-sm me-1" href="<?= e(base_url('admin/destinos.php?edit=' . (int)$it['id_destino'])) ?>">Editar</a>
                <form method="post" class="d-inline">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= (int)$it['id_destino'] ?>">
                  <button class="btn btn-outline-secondary btn-sm"><?= $it['estado']==='activo'?'Desactivar':'Activar' ?></button>
                </form>
                <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar este destino? Se eliminarán sus requisitos y avisos.');">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int)$it['id_destino'] ?>">
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
</main>
<?php include __DIR__ . '/../../app/views/partials/footer.php'; ?>
</body>
</html>
