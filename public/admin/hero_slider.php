<?php require_once __DIR__ . '/_admin_guard.php';
$errors = [];
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$new_modal = isset($_GET['new']) ? 1 : 0;

function save_hero_upload(?array $file, array &$errors, bool $required): ?string {
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
  $dir = __DIR__ . '/../assets/hero_slider';
  if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
    $errors[] = "No se pudo crear la carpeta de imagenes.";
    return null;
  }
  $unique = str_replace('.', '', uniqid('hero_', true));
  $filename = $unique . '.' . $ext;
  $dest_path = $dir . '/' . $filename;
  if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
    $errors[] = "No se pudo guardar la imagen.";
    return null;
  }
  return 'assets/hero_slider/' . $filename;
}

function delete_hero_image(?string $path): void {
  if (!$path) return;
  $public_root = realpath(__DIR__ . '/..');
  $images_dir = realpath($public_root . '/assets/hero_slider');
  $candidate = realpath($public_root . '/' . ltrim($path, '/'));
  if ($public_root && $images_dir && $candidate && strpos($candidate, $images_dir) === 0) {
    @unlink($candidate);
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $action = $_POST['action'] ?? '';

  if ($action === 'create') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $enlace_texto = trim($_POST['enlace_texto'] ?? '');
    $enlace_url = trim($_POST['enlace_url'] ?? '');
    $intervalo_segundos = (int)($_POST['intervalo_segundos'] ?? 7);
    $estado = $_POST['estado'] ?? 'activo';
    if (!in_array($estado, ['activo', 'inactivo'], true)) {
      $estado = 'activo';
    }

    if (!$titulo) $errors[] = "El titulo es obligatorio.";
    if (!$descripcion) $errors[] = "La descripcion es obligatoria.";
    if (!$enlace_texto) $errors[] = "El texto del enlace es obligatorio.";
    if (!$enlace_url) $errors[] = "El enlace es obligatorio.";
    if ($intervalo_segundos < 3) $errors[] = "El tiempo debe ser de al menos 3 segundos.";
    if ($intervalo_segundos > 30) $errors[] = "El tiempo no debe superar 30 segundos.";
    $imagen_path = save_hero_upload($_FILES['imagen'] ?? null, $errors, true);

    if (!$errors) {
      $pdo->prepare("
        INSERT INTO hero_slide (titulo, descripcion, enlace_texto, enlace_url, imagen_path, intervalo_segundos, estado)
        VALUES (?,?,?,?,?,?,?)
      ")->execute([$titulo, $descripcion, $enlace_texto, $enlace_url, $imagen_path, $intervalo_segundos, $estado]);
      redirect('admin/hero_slider.php?created=1');
    }
  } elseif ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $enlace_texto = trim($_POST['enlace_texto'] ?? '');
    $enlace_url = trim($_POST['enlace_url'] ?? '');
    $intervalo_segundos = (int)($_POST['intervalo_segundos'] ?? 7);
    $estado = $_POST['estado'] ?? 'activo';
    if (!in_array($estado, ['activo', 'inactivo'], true)) {
      $estado = 'activo';
    }

    if (!$id) $errors[] = "Slider invalido.";
    if (!$titulo) $errors[] = "El titulo es obligatorio.";
    if (!$descripcion) $errors[] = "La descripcion es obligatoria.";
    if (!$enlace_texto) $errors[] = "El texto del enlace es obligatorio.";
    if (!$enlace_url) $errors[] = "El enlace es obligatorio.";
    if ($intervalo_segundos < 3) $errors[] = "El tiempo debe ser de al menos 3 segundos.";
    if ($intervalo_segundos > 30) $errors[] = "El tiempo no debe superar 30 segundos.";

    $stmt = $pdo->prepare("SELECT imagen_path FROM hero_slide WHERE id_slide=?");
    $stmt->execute([$id]);
    $current = $stmt->fetch();
    if (!$current) $errors[] = "Slider invalido.";

    $new_image = save_hero_upload($_FILES['imagen'] ?? null, $errors, false);
    $final_image = $new_image ?: ($current['imagen_path'] ?? null);
    if (!$final_image) $errors[] = "La imagen es obligatoria.";

    if (!$errors) {
      $pdo->prepare("
        UPDATE hero_slide
        SET titulo=?, descripcion=?, enlace_texto=?, enlace_url=?, imagen_path=?, intervalo_segundos=?, estado=?
        WHERE id_slide=?
      ")->execute([$titulo, $descripcion, $enlace_texto, $enlace_url, $final_image, $intervalo_segundos, $estado, $id]);
      if ($new_image && !empty($current['imagen_path'])) {
        delete_hero_image($current['imagen_path']);
      }
      redirect('admin/hero_slider.php?updated=1');
    }
  } elseif ($action === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare("UPDATE hero_slide SET estado = IF(estado='activo','inactivo','activo') WHERE id_slide=?")->execute([$id]);
    redirect('admin/hero_slider.php?updated=1');
  } elseif ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
      $stmt = $pdo->prepare("SELECT imagen_path FROM hero_slide WHERE id_slide=?");
      $stmt->execute([$id]);
      $row = $stmt->fetch();
      $pdo->prepare("DELETE FROM hero_slide WHERE id_slide=?")->execute([$id]);
      if (!empty($row['imagen_path'])) {
        delete_hero_image($row['imagen_path']);
      }
    }
    redirect('admin/hero_slider.php');
  }
}

$items = [];
try {
  $items = $pdo->query("
    SELECT *
    FROM hero_slide
    ORDER BY estado DESC, orden ASC, id_slide DESC
  ")->fetchAll();
} catch (PDOException $e) {
  $errors[] = "No se encontro la tabla hero_slide. Ejecuta database.sql.";
}

$edit_item = null;
if ($edit_id) {
  $stmt = $pdo->prepare("SELECT * FROM hero_slide WHERE id_slide=?");
  $stmt->execute([$edit_id]);
  $edit_item = $stmt->fetch();
}
$page_title = 'Slider portada';
$page_subtitle = 'Administra las imagenes y textos del hero principal.';
?>
<?php include __DIR__ . '/_layout_start.php'; ?>
  <div class="admin-section-head">
    <h2>Slider portada</h2>
    <p>Controla las imagenes, titulos y enlaces que aparecen en la portada.</p>
  </div>

  <div class="admin-page">
  <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <?php if (!empty($_GET['created'])): ?>
    <div class="alert alert-success">Slide creado correctamente.</div>
  <?php endif; ?>
  <?php if (!empty($_GET['updated'])): ?>
    <div class="alert alert-success">Slide actualizado correctamente.</div>
  <?php endif; ?>

  <div class="d-flex justify-content-end mb-3">
    <a class="btn btn-primary" href="<?= e(base_url('admin/hero_slider.php?new=1')) ?>">Nueva imagen</a>
  </div>

  <?php if ($edit_id && !$edit_item): ?>
    <div class="alert alert-warning">No se encontro el slide para editar.</div>
  <?php endif; ?>

  <?php if ($new_modal): ?>
    <div class="req-modal-backdrop">
      <div class="req-modal">
        <div class="req-modal-head">
          <h2 class="h6 mb-0">Crear slide</h2>
          <a class="req-modal-close" href="<?= e(base_url('admin/hero_slider.php')) ?>">Cancelar</a>
        </div>
        <form method="post" class="row g-2 mt-3" enctype="multipart/form-data">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="action" value="create">
          <div class="col-md-6">
            <input class="form-control form-control-sm" name="titulo" placeholder="Titulo" required>
          </div>
          <div class="col-md-6">
            <input class="form-control form-control-sm" name="enlace_texto" placeholder="Texto del enlace" required>
          </div>
          <div class="col-12">
            <input class="form-control form-control-sm" name="enlace_url" placeholder="Enlace (URL o ruta)" required>
          </div>
          <div class="col-12">
            <textarea class="form-control form-control-sm" name="descripcion" rows="3" placeholder="Descripcion" required></textarea>
          </div>
          <div class="col-md-6">
            <input class="form-control form-control-sm featured-input" type="file" name="imagen" accept=".png,.jpg,.jpeg,.webp" required>
            <div class="form-text">Imagen para el hero (JPG/PNG/WebP). Recomendado: 1920x1080 px.</div>
          </div>
          <div class="col-md-6">
            <div class="border rounded p-2 bg-light">
              <div class="small text-secondary mb-1">Vista previa</div>
              <img class="img-fluid rounded featured-preview" alt="Vista previa" style="display:none; max-height:140px;">
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label small">Tiempo de visualizacion (seg.)</label>
            <input class="form-control form-control-sm" type="number" name="intervalo_segundos" value="7" min="3" max="30" placeholder="Ej: 7" required>
            <div class="form-text">Minimo 3 segundos, maximo 30 segundos.</div>
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
          <h2 class="h6 mb-0">Editar slide</h2>
          <a class="req-modal-close" href="<?= e(base_url('admin/hero_slider.php')) ?>">Cancelar</a>
        </div>
        <form method="post" class="row g-2 mt-3" enctype="multipart/form-data">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" value="<?= (int)$edit_item['id_slide'] ?>">
          <div class="col-md-6">
            <input class="form-control form-control-sm" name="titulo" placeholder="Titulo" required value="<?= e($edit_item['titulo']) ?>">
          </div>
          <div class="col-md-6">
            <input class="form-control form-control-sm" name="enlace_texto" placeholder="Texto del enlace" required value="<?= e($edit_item['enlace_texto']) ?>">
          </div>
          <div class="col-12">
            <input class="form-control form-control-sm" name="enlace_url" placeholder="Enlace (URL o ruta)" required value="<?= e($edit_item['enlace_url']) ?>">
          </div>
          <div class="col-12">
            <textarea class="form-control form-control-sm" name="descripcion" rows="3" placeholder="Descripcion" required><?= e($edit_item['descripcion']) ?></textarea>
          </div>
          <div class="col-md-6">
            <input class="form-control form-control-sm featured-input" type="file" name="imagen" accept=".png,.jpg,.jpeg,.webp">
            <div class="form-text">Sube una nueva imagen para reemplazar la actual. Recomendado: 1920x1080 px.</div>
          </div>
          <div class="col-md-6">
            <div class="border rounded p-2 bg-light">
              <div class="small text-secondary mb-1">Vista previa</div>
              <?php if (!empty($edit_item['imagen_path'])): ?>
                <img class="img-fluid rounded featured-preview" alt="Vista previa" src="<?= e(asset_url($edit_item['imagen_path'])) ?>" style="max-height:140px;">
              <?php else: ?>
                <img class="img-fluid rounded featured-preview" alt="Vista previa" style="display:none; max-height:140px;">
              <?php endif; ?>
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label small">Tiempo de visualizacion (seg.)</label>
            <input class="form-control form-control-sm" type="number" name="intervalo_segundos" value="<?= (int)($edit_item['intervalo_segundos'] ?? 7) ?>" min="3" max="30" placeholder="Ej: 7" required>
            <div class="form-text">Minimo 3 segundos, maximo 30 segundos.</div>
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
          <thead>
            <tr>
              <th>ID</th>
              <th>Imagen</th>
              <th>Titulo</th>
              <th>CTA</th>
              <th>Tiempo (s)</th>
              <th>Estado</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td><?= (int)$it['id_slide'] ?></td>
              <td>
                <?php if (!empty($it['imagen_path'])): ?>
                  <img src="<?= e(asset_url($it['imagen_path'])) ?>" alt="Imagen" width="72" height="72" class="rounded" style="object-fit:cover;">
                <?php else: ?>
                  <span class="text-secondary small">Sin imagen</span>
                <?php endif; ?>
              </td>
              <td><?= e($it['titulo']) ?></td>
              <td>
                <div class="small"><?= e($it['enlace_texto']) ?></div>
                <div class="text-secondary small"><?= e($it['enlace_url']) ?></div>
              </td>
              <td><?= (int)($it['intervalo_segundos'] ?? 7) ?></td>
              <td><span class="badge text-bg-<?= $it['estado']==='activo'?'success':'secondary' ?>"><?= e($it['estado']) ?></span></td>
              <td class="text-end">
                <a class="btn btn-outline-primary btn-sm me-1" href="<?= e(base_url('admin/hero_slider.php?edit=' . (int)$it['id_slide'])) ?>">Editar</a>
                <form method="post" class="d-inline">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= (int)$it['id_slide'] ?>">
                  <button class="btn btn-outline-secondary btn-sm"><?= $it['estado']==='activo'?'Desactivar':'Activar' ?></button>
                </form>
                <form method="post" class="d-inline" onsubmit="return confirm('Eliminar este slide?');">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int)$it['id_slide'] ?>">
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
