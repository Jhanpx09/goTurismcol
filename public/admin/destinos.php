<?php require_once __DIR__ . '/_admin_guard.php';
$errors = [];
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$new_modal = isset($_GET['new']) ? 1 : 0;

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
      $stmt = $pdo->prepare("INSERT INTO destino (pais, ciudad, descripcion_general) VALUES (?,?,?)")
          ->execute([$pais, $ciudad ?: null, $desc ?: null]);
      $nuevo_id = (int)$pdo->lastInsertId();
      if ($flag_ext) {
        $path = save_flag_upload($_FILES['bandera'], $nuevo_id, $flag_ext, $errors);
        if ($path) {
          $pdo->prepare("UPDATE destino SET bandera_path=? WHERE id_destino=?")->execute([$path, $nuevo_id]);
        }
      }
      if (!$errors) {
        redirect('admin/destinos.php?created=1');
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
        redirect('admin/destinos.php?updated=1');
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
$page_title = 'Destinos';
$page_subtitle = 'Gestiona los destinos disponibles en la plataforma.';
?>
<?php include __DIR__ . '/_layout_start.php'; ?>
  <div class="admin-section-head">
    <h2>Destinos</h2>
    <p>Crear y activar/desactivar destinos disponibles en la plataforma.</p>
  </div>

  <div class="admin-page">
  <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <?php if (!empty($_GET['created'])): ?>
    <div class="alert alert-success">Destino creado correctamente.</div>
  <?php endif; ?>
  <?php if (!empty($_GET['updated'])): ?>
    <div class="alert alert-success">Destino actualizado correctamente.</div>
  <?php endif; ?>

  <div class="d-flex justify-content-end mb-3">
    <a class="btn btn-primary" href="<?= e(base_url('admin/destinos.php?new=1')) ?>">Nuevo destino</a>
  </div>

  <?php if ($edit_id && !$edit_item): ?>
    <div class="alert alert-warning">No se encontro el destino seleccionado para editar.</div>
  <?php endif; ?>

  <?php if ($new_modal): ?>
    <div class="req-modal-backdrop">
      <div class="req-modal">
        <div class="req-modal-head">
          <h2 class="h6 mb-0">Crear destino</h2>
          <a class="req-modal-close" href="<?= e(base_url('admin/destinos.php')) ?>">Cancelar</a>
        </div>
        <form method="post" class="row g-2 mt-3" enctype="multipart/form-data">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="action" value="create">
          <div class="col-md-4"><input class="form-control form-control-sm" name="pais" placeholder="Pais" required></div>
          <div class="col-md-4"><input class="form-control form-control-sm" name="ciudad" placeholder="Ciudad (opcional)"></div>
          <div class="col-md-4"><input class="form-control form-control-sm" name="descripcion" placeholder="Descripcion (opcional)"></div>
          <div class="col-md-6">
            <input class="form-control form-control-sm flag-input" type="file" name="bandera" accept=".png,.jpg,.jpeg,.webp">
            <div class="form-text">Bandera en JPG/PNG/WebP. Recomendado: 640x480 px.</div>
          </div>
          <div class="col-md-6">
            <div class="border rounded p-2 bg-light">
              <div class="small text-secondary mb-1">Vista previa</div>
              <img class="img-fluid rounded flag-preview" alt="Vista previa de bandera" style="display:none; max-height:120px;">
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
          <h2 class="h6 mb-0">Editar destino</h2>
          <a class="req-modal-close" href="<?= e(base_url('admin/destinos.php')) ?>">Cancelar</a>
        </div>
        <form method="post" class="row g-2 mt-3" enctype="multipart/form-data">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id_destino" value="<?= (int)$edit_item['id_destino'] ?>">
          <div class="col-md-4"><input class="form-control form-control-sm" name="pais" value="<?= e($edit_item['pais']) ?>" required></div>
          <div class="col-md-4"><input class="form-control form-control-sm" name="ciudad" value="<?= e($edit_item['ciudad'] ?? '') ?>" placeholder="Ciudad (opcional)"></div>
          <div class="col-md-4"><input class="form-control form-control-sm" name="descripcion" value="<?= e($edit_item['descripcion_general'] ?? '') ?>" placeholder="Descripcion (opcional)"></div>
          <div class="col-md-6">
            <input class="form-control form-control-sm flag-input" type="file" name="bandera" accept=".png,.jpg,.jpeg,.webp" data-current="<?= e($edit_item['bandera_path'] ?? '') ?>">
            <div class="form-text">Suba una nueva bandera para reemplazar la actual. Recomendado: 640x480 px.</div>
          </div>
          <div class="col-md-6">
            <div class="border rounded p-2 bg-light">
              <div class="small text-secondary mb-1">Vista previa</div>
              <?php if (!empty($edit_item['bandera_path'])): ?>
                <img class="img-fluid rounded flag-preview" src="<?= e(base_url($edit_item['bandera_path'])) ?>" alt="Bandera actual" style="max-height:120px;">
              <?php else: ?>
                <img class="img-fluid rounded flag-preview" alt="Vista previa de bandera" style="display:none; max-height:120px;">
              <?php endif; ?>
            </div>
          </div>
          <div class="col-md-6 d-flex align-items-end gap-2">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="clear_flag" id="clear_flag">
              <label class="form-check-label" for="clear_flag">Quitar bandera actual</label>
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
  </div>

  <script>
  document.querySelectorAll('.flag-input').forEach((input) => {
    const wrapper = input.closest('form');
    const preview = wrapper ? wrapper.querySelector('.flag-preview') : null;
    if (!preview) return;

    input.addEventListener('change', () => {
      const file = input.files && input.files[0];
      if (!file) {
        preview.style.display = 'none';
        preview.removeAttribute('src');
        return;
      }
      const reader = new FileReader();
      reader.onload = (event) => {
        preview.src = event.target.result;
        preview.style.display = 'block';
      };
      reader.readAsDataURL(file);
    });
  });
</script>
<?php include __DIR__ . '/_layout_end.php'; ?>
