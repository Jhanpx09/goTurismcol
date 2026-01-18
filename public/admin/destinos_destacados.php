<?php require_once __DIR__ . '/_admin_guard.php';
$errors = [];
$destinos = $pdo->query("SELECT id_destino, pais, ciudad FROM destino ORDER BY pais, ciudad")->fetchAll();
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$new_modal = isset($_GET['new']) ? 1 : 0;

function save_destacado_upload(?array $file, array &$errors, bool $required): ?string {
  if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    if ($required) $errors[] = "La imagen es obligatoria.";
    return null;
  }
  if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
    $errors[] = "Error al subir la imagen.";
    return null;
  }
  $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
  $allowed = ['jpg', 'jpeg', 'png', 'webp'];
  if (!$ext || !in_array($ext, $allowed, true)) {
    $errors[] = "Formato de imagen no valido. Use jpg, png o webp.";
    return null;
  }
  $dir = __DIR__ . '/../assets/destinos_destacados';
  if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
    $errors[] = "No se pudo crear la carpeta de imagenes.";
    return null;
  }
  $unique = str_replace('.', '', uniqid('destacado_', true));
  $filename = $unique . '.' . $ext;
  $dest_path = $dir . '/' . $filename;
  if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
    $errors[] = "No se pudo guardar la imagen.";
    return null;
  }
  return 'assets/destinos_destacados/' . $filename;
}

function delete_destacado_image(?string $path): void {
  if (!$path) return;
  $public_root = realpath(__DIR__ . '/..');
  $images_dir = realpath($public_root . '/assets/destinos_destacados');
  $candidate = realpath($public_root . '/' . ltrim($path, '/'));
  if ($public_root && $images_dir && $candidate && strpos($candidate, $images_dir) === 0) {
    @unlink($candidate);
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $action = $_POST['action'] ?? '';

  if ($action === 'create') {
    $id_destino = (int)($_POST['id_destino'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $orden = (int)($_POST['orden'] ?? 0);
    $estado = $_POST['estado'] ?? 'activo';
    if (!in_array($estado, ['activo', 'inactivo'], true)) {
      $estado = 'activo';
    }

    if (!$id_destino) $errors[] = "Seleccione un destino existente.";
    if (!$titulo) $errors[] = "El titulo es obligatorio.";
    if (!$descripcion) $errors[] = "La descripcion es obligatoria.";
    $imagen_path = save_destacado_upload($_FILES['imagen'] ?? null, $errors, true);

    if (!$errors) {
      $pdo->prepare("
        INSERT INTO destino_destacado (id_destino, titulo, descripcion, imagen_path, orden, estado)
        VALUES (?,?,?,?,?,?)
      ")->execute([$id_destino, $titulo, $descripcion, $imagen_path, $orden, $estado]);
      redirect('admin/destinos_destacados.php?created=1');
    }
  } elseif ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $id_destino = (int)($_POST['id_destino'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $orden = (int)($_POST['orden'] ?? 0);
    $estado = $_POST['estado'] ?? 'activo';
    if (!in_array($estado, ['activo', 'inactivo'], true)) {
      $estado = 'activo';
    }

    if (!$id) $errors[] = "Destino destacado invalido.";
    if (!$id_destino) $errors[] = "Seleccione un destino existente.";
    if (!$titulo) $errors[] = "El titulo es obligatorio.";
    if (!$descripcion) $errors[] = "La descripcion es obligatoria.";

    $stmt = $pdo->prepare("SELECT imagen_path FROM destino_destacado WHERE id_destacado=?");
    $stmt->execute([$id]);
    $current = $stmt->fetch();
    if (!$current) $errors[] = "Destino destacado invalido.";

    $new_image = save_destacado_upload($_FILES['imagen'] ?? null, $errors, false);
    $final_image = $new_image ?: ($current['imagen_path'] ?? null);
    if (!$final_image) $errors[] = "La imagen es obligatoria.";

    if (!$errors) {
      $pdo->prepare("
        UPDATE destino_destacado
        SET id_destino=?, titulo=?, descripcion=?, imagen_path=?, orden=?, estado=?
        WHERE id_destacado=?
      ")->execute([$id_destino, $titulo, $descripcion, $final_image, $orden, $estado, $id]);
      if ($new_image && !empty($current['imagen_path'])) {
        delete_destacado_image($current['imagen_path']);
      }
      redirect('admin/destinos_destacados.php?updated=1');
    }
  } elseif ($action === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare("UPDATE destino_destacado SET estado = IF(estado='activo','inactivo','activo') WHERE id_destacado=?")->execute([$id]);
    redirect('admin/destinos_destacados.php?updated=1');
  } elseif ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
      $stmt = $pdo->prepare("SELECT imagen_path FROM destino_destacado WHERE id_destacado=?");
      $stmt->execute([$id]);
      $row = $stmt->fetch();
      $pdo->prepare("DELETE FROM destino_destacado WHERE id_destacado=?")->execute([$id]);
      if (!empty($row['imagen_path'])) {
        delete_destacado_image($row['imagen_path']);
      }
    }
    redirect('admin/destinos_destacados.php');
  }
}

$items = $pdo->query("
  SELECT dd.*, d.pais, d.ciudad
  FROM destino_destacado dd
  JOIN destino d ON d.id_destino = dd.id_destino
  ORDER BY dd.estado DESC, dd.orden ASC, dd.id_destacado DESC
")->fetchAll();

$edit_item = null;
if ($edit_id) {
  $stmt = $pdo->prepare("
    SELECT dd.*, d.pais, d.ciudad
    FROM destino_destacado dd
    JOIN destino d ON d.id_destino = dd.id_destino
    WHERE dd.id_destacado=?
  ");
  $stmt->execute([$edit_id]);
  $edit_item = $stmt->fetch();
}
$page_title = 'Destinos destacados';
$page_subtitle = 'Gestiona la seccion de publicidad en la portada.';
?>
<?php include __DIR__ . '/_layout_start.php'; ?>
  <div class="admin-section-head">
    <h2>Destinos destacados</h2>
    <p>Administra el contenido promocional que se muestra en la portada.</p>
  </div>

  <div class="admin-page">
  <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <?php if (!empty($_GET['created'])): ?>
    <div class="alert alert-success">Destino destacado creado correctamente.</div>
  <?php endif; ?>
  <?php if (!empty($_GET['updated'])): ?>
    <div class="alert alert-success">Destino destacado actualizado correctamente.</div>
  <?php endif; ?>

  <div class="d-flex justify-content-end mb-3">
    <a class="btn btn-primary" href="<?= e(base_url('admin/destinos_destacados.php?new=1')) ?>">Nuevo destino destacado</a>
  </div>

  <?php if ($edit_id && !$edit_item): ?>
    <div class="alert alert-warning">No se encontro el destino destacado para editar.</div>
  <?php endif; ?>

  <?php if ($new_modal): ?>
    <div class="req-modal-backdrop">
      <div class="req-modal">
        <div class="req-modal-head">
          <h2 class="h6 mb-0">Crear destino destacado</h2>
          <a class="req-modal-close" href="<?= e(base_url('admin/destinos_destacados.php')) ?>">Cancelar</a>
        </div>
        <form method="post" class="row g-2 mt-3" enctype="multipart/form-data">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="action" value="create">
          <div class="col-md-4">
            <input class="form-control form-control-sm" name="titulo" placeholder="Titulo" required>
          </div>
          <div class="col-md-4">
            <select class="form-select form-select-sm" name="id_destino" required>
              <option value="" disabled selected>Asociar destino</option>
              <?php foreach ($destinos as $d): ?>
                <option value="<?= (int)$d['id_destino'] ?>"><?= e($d['pais'] . ($d['ciudad'] ? ' - ' . $d['ciudad'] : '')) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <input class="form-control form-control-sm" type="number" name="orden" value="0" min="0" placeholder="Orden">
          </div>
          <div class="col-12">
            <textarea class="form-control form-control-sm" name="descripcion" rows="3" placeholder="Descripcion" required></textarea>
          </div>
          <div class="col-md-6">
            <input class="form-control form-control-sm featured-input" type="file" name="imagen" accept=".png,.jpg,.jpeg,.webp" required>
            <div class="form-text">Imagen del destino (JPG/PNG/WebP).</div>
          </div>
          <div class="col-md-6">
            <div class="border rounded p-2 bg-light">
              <div class="small text-secondary mb-1">Vista previa</div>
              <img class="img-fluid rounded featured-preview" alt="Vista previa" style="display:none; max-height:140px;">
            </div>
          </div>
          <div class="col-md-4">
            <select class="form-select form-select-sm" name="estado">
              <option value="activo" selected>Activo</option>
              <option value="inactivo">Inactivo</option>
            </select>
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
          <h2 class="h6 mb-0">Editar destino destacado</h2>
          <a class="req-modal-close" href="<?= e(base_url('admin/destinos_destacados.php')) ?>">Cancelar</a>
        </div>
        <form method="post" class="row g-2 mt-3" enctype="multipart/form-data">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" value="<?= (int)$edit_item['id_destacado'] ?>">
          <div class="col-md-4">
            <input class="form-control form-control-sm" name="titulo" value="<?= e($edit_item['titulo']) ?>" required>
          </div>
          <div class="col-md-4">
            <select class="form-select form-select-sm" name="id_destino" required>
              <?php foreach ($destinos as $d): ?>
                <option value="<?= (int)$d['id_destino'] ?>" <?= (int)$edit_item['id_destino'] === (int)$d['id_destino'] ? 'selected' : '' ?>>
                  <?= e($d['pais'] . ($d['ciudad'] ? ' - ' . $d['ciudad'] : '')) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <input class="form-control form-control-sm" type="number" name="orden" value="<?= (int)$edit_item['orden'] ?>" min="0" placeholder="Orden">
          </div>
          <div class="col-12">
            <textarea class="form-control form-control-sm" name="descripcion" rows="3" required><?= e($edit_item['descripcion']) ?></textarea>
          </div>
          <div class="col-md-6">
            <input class="form-control form-control-sm featured-input" type="file" name="imagen" accept=".png,.jpg,.jpeg,.webp">
            <div class="form-text">Suba una nueva imagen para reemplazar la actual.</div>
          </div>
          <div class="col-md-6">
            <div class="border rounded p-2 bg-light">
              <div class="small text-secondary mb-1">Vista previa</div>
              <?php if (!empty($edit_item['imagen_path'])): ?>
                <img class="img-fluid rounded featured-preview" src="<?= e(asset_url($edit_item['imagen_path'])) ?>" alt="Imagen actual" style="max-height:140px;">
              <?php else: ?>
                <img class="img-fluid rounded featured-preview" alt="Vista previa" style="display:none; max-height:140px;">
              <?php endif; ?>
            </div>
          </div>
          <div class="col-md-4">
            <select class="form-select form-select-sm" name="estado">
              <option value="activo" <?= $edit_item['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
              <option value="inactivo" <?= $edit_item['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
            </select>
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
          <thead><tr><th>ID</th><th>Imagen</th><th>Titulo</th><th>Destino asociado</th><th>Orden</th><th>Estado</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td><?= (int)$it['id_destacado'] ?></td>
              <td>
                <?php if (!empty($it['imagen_path'])): ?>
                  <img src="<?= e(asset_url($it['imagen_path'])) ?>" alt="Imagen" width="72" height="72" class="rounded" style="object-fit:cover;">
                <?php else: ?>
                  <span class="text-secondary small">Sin imagen</span>
                <?php endif; ?>
              </td>
              <td><?= e($it['titulo']) ?></td>
              <td><?= e($it['pais'] . ($it['ciudad'] ? ' - ' . $it['ciudad'] : '')) ?></td>
              <td><?= (int)$it['orden'] ?></td>
              <td><span class="badge text-bg-<?= $it['estado']==='activo'?'success':'secondary' ?>"><?= e($it['estado']) ?></span></td>
              <td class="text-end">
                <a class="btn btn-outline-primary btn-sm me-1" href="<?= e(base_url('admin/destinos_destacados.php?edit=' . (int)$it['id_destacado'])) ?>">Editar</a>
                <form method="post" class="d-inline">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= (int)$it['id_destacado'] ?>">
                  <button class="btn btn-outline-secondary btn-sm"><?= $it['estado']==='activo'?'Desactivar':'Activar' ?></button>
                </form>
                <form method="post" class="d-inline" onsubmit="return confirm('Eliminar este destino destacado?');">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int)$it['id_destacado'] ?>">
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
  document.querySelectorAll('.featured-input').forEach((input) => {
    const wrapper = input.closest('form');
    const preview = wrapper ? wrapper.querySelector('.featured-preview') : null;
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
