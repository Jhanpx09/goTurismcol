<?php require_once __DIR__ . '/_admin_guard.php';
$destinos = $pdo->query("SELECT id_destino, pais, ciudad FROM destino ORDER BY pais, ciudad")->fetchAll();
$errors = [];
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$delete_id = isset($_GET['delete']) ? (int)$_GET['delete'] : 0;
$edit_item_override = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $action = $_POST['action'] ?? '';
  if ($action === 'create') {
    $id_destino = (int)($_POST['id_destino'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $detalle = trim($_POST['detalle'] ?? '');
    if (!$id_destino) $errors[] = "Seleccione un destino.";
    if (!$titulo) $errors[] = "El título es obligatorio.";
    if (!$detalle) $errors[] = "El detalle es obligatorio.";
    if (!$errors) {
      $pdo->prepare("INSERT INTO aviso_actualizacion (id_destino, titulo_aviso, detalle_aviso, publicado_por) VALUES (?,?,?,?)")
          ->execute([$id_destino, $titulo, $detalle, (int)$admin['id_usuario']]);
      redirect('admin/avisos.php?created=1');
    }
  } elseif ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $id_destino = (int)($_POST['id_destino'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $detalle = trim($_POST['detalle'] ?? '');
    if (!$id) $errors[] = "Aviso invalido.";
    if (!$id_destino) $errors[] = "Seleccione un destino.";
    if (!$titulo) $errors[] = "El titulo es obligatorio.";
    if (!$detalle) $errors[] = "El detalle es obligatorio.";
    if (!$errors) {
      $pdo->prepare("UPDATE aviso_actualizacion SET id_destino=?, titulo_aviso=?, detalle_aviso=? WHERE id_aviso=?")
          ->execute([$id_destino, $titulo, $detalle, $id]);
      redirect('admin/avisos.php?updated=1');
    } elseif ($id) {
      $edit_id = $id;
      $edit_item_override = [
        'id_aviso' => $id,
        'id_destino' => $id_destino,
        'titulo_aviso' => $titulo,
        'detalle_aviso' => $detalle,
      ];
    }
  } elseif ($action === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare("UPDATE aviso_actualizacion SET estado = IF(estado='activo','inactivo','activo') WHERE id_aviso=?")->execute([$id]);
    redirect('admin/avisos.php?updated=1');
  } elseif ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
      $pdo->prepare("DELETE FROM aviso_actualizacion WHERE id_aviso=?")->execute([$id]);
    }
    redirect('admin/avisos.php?deleted=1');
  }
}

$avisos = $pdo->query("
  SELECT a.*, d.pais, d.ciudad
  FROM aviso_actualizacion a
  JOIN destino d ON d.id_destino = a.id_destino
  ORDER BY a.estado DESC, a.fecha_publicacion DESC
")->fetchAll();
$edit_item = null;
if ($edit_id) {
  if ($edit_item_override) {
    $edit_item = $edit_item_override;
  } else {
    $stmt = $pdo->prepare("SELECT * FROM aviso_actualizacion WHERE id_aviso=?");
    $stmt->execute([$edit_id]);
    $edit_item = $stmt->fetch();
  }
}
$delete_item = null;
if ($delete_id) {
  $stmt = $pdo->prepare("
    SELECT a.*, d.pais, d.ciudad
    FROM aviso_actualizacion a
    JOIN destino d ON d.id_destino = a.id_destino
    WHERE a.id_aviso=?
  ");
  $stmt->execute([$delete_id]);
  $delete_item = $stmt->fetch();
}
$page_title = 'Avisos';
$page_subtitle = 'Publica comunicados importantes por destino.';
?>
<?php include __DIR__ . '/_layout_start.php'; ?>
  <div class="admin-section-head">
    <h2>Avisos de actualizacion</h2>
    <p>Publica comunicados importantes asociados a un destino.</p>
  </div>

  <div class="admin-page">
  <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <?php if (!empty($_GET['created'])): ?>
    <div class="alert alert-success">Aviso creado correctamente.</div>
  <?php endif; ?>
  <?php if (!empty($_GET['updated'])): ?>
    <div class="alert alert-success">Aviso actualizado correctamente.</div>
  <?php endif; ?>
  <?php if (!empty($_GET['deleted'])): ?>
    <div class="alert alert-success">Aviso eliminado correctamente.</div>
  <?php endif; ?>

  <?php if ($edit_id && !$edit_item): ?>
    <div class="alert alert-warning">No se encontro el aviso para editar.</div>
  <?php endif; ?>
  <?php if ($delete_id && !$delete_item): ?>
    <div class="alert alert-warning">No se encontro el aviso para eliminar.</div>
  <?php endif; ?>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h2 class="h6">Crear aviso</h2>
      <form method="post" class="row g-2">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="create">
        <div class="col-md-4">
          <select class="form-select" name="id_destino" required>
            <option value="" disabled selected>Seleccione un destino</option>
            <?php foreach ($destinos as $d): ?>
              <option value="<?= (int)$d['id_destino'] ?>"><?= e($d['pais'] . ($d['ciudad'] ? ' - ' . $d['ciudad'] : '')) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4"><input class="form-control" name="titulo" placeholder="Título" required></div>
        <div class="col-md-4"><input class="form-control" name="detalle" placeholder="Detalle" required></div>
        <div class="col-12 d-grid"><button class="btn btn-primary">Publicar</button></div>
      </form>
    </div>
  </div>

  <?php if ($edit_item): ?>
    <div class="req-modal-backdrop">
      <div class="req-modal">
        <div class="req-modal-head">
          <h2 class="h6 mb-0">Editar aviso</h2>
          <a class="req-modal-close" href="<?= e(base_url('admin/avisos.php')) ?>">Cancelar</a>
        </div>
        <form method="post" class="row g-2 mt-3">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" value="<?= (int)$edit_item['id_aviso'] ?>">
          <div class="col-md-4">
            <select class="form-select form-select-sm" name="id_destino" required>
              <option value="" disabled>Seleccione un destino</option>
              <?php foreach ($destinos as $d): ?>
                <option value="<?= (int)$d['id_destino'] ?>" <?= (int)$edit_item['id_destino'] === (int)$d['id_destino'] ? 'selected' : '' ?>>
                  <?= e($d['pais'] . ($d['ciudad'] ? ' - ' . $d['ciudad'] : '')) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6"><input class="form-control form-control-sm" name="titulo" placeholder="Titulo" required value="<?= e($edit_item['titulo_aviso']) ?>"></div>
          <div class="col-12">
            <textarea class="form-control form-control-sm" name="detalle" rows="4" placeholder="Detalle" required><?= e($edit_item['detalle_aviso']) ?></textarea>
          </div>
          <div class="col-12 d-grid"><button class="btn btn-primary">Actualizar</button></div>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($delete_item): ?>
    <div class="req-modal-backdrop">
      <div class="req-modal">
        <div class="req-modal-head">
          <h2 class="h6 mb-0">Eliminar aviso</h2>
          <a class="req-modal-close" href="<?= e(base_url('admin/avisos.php')) ?>">Cancelar</a>
        </div>
        <div class="mt-3">
          <p class="mb-2">Confirma eliminar el aviso <strong><?= e($delete_item['titulo_aviso']) ?></strong> del destino <?= e($delete_item['pais'] . ($delete_item['ciudad'] ? ' - ' . $delete_item['ciudad'] : '')) ?>.</p>
          <form method="post" class="d-grid">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$delete_item['id_aviso'] ?>">
            <button class="btn btn-danger">Eliminar</button>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">
      <h2 class="h6">Listado</h2>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead><tr><th>ID</th><th>Destino</th><th>Titulo</th><th>Fecha</th><th>Estado</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($avisos as $a): ?>
            <tr>
              <td><?= (int)$a['id_aviso'] ?></td>
              <td><?= e($a['pais'] . ($a['ciudad'] ? ' - ' . $a['ciudad'] : '')) ?></td>
              <td><?= e($a['titulo_aviso']) ?></td>
              <td><?= e(date('Y-m-d', strtotime($a['fecha_publicacion']))) ?></td>
              <td><span class="badge text-bg-<?= $a['estado']==='activo'?'success':'secondary' ?>"><?= e($a['estado']) ?></span></td>
              <td class="text-end">
                <a class="btn btn-outline-primary btn-sm me-1" href="<?= e(base_url('admin/avisos.php?edit=' . (int)$a['id_aviso'])) ?>">Editar</a>
                <form method="post" class="d-inline me-1">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= (int)$a['id_aviso'] ?>">
                  <button class="btn btn-outline-secondary btn-sm"><?= $a['estado']==='activo'?'Desactivar':'Activar' ?></button>
                </form>
                <a class="btn btn-outline-danger btn-sm" href="<?= e(base_url('admin/avisos.php?delete=' . (int)$a['id_aviso'])) ?>">Eliminar</a>
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
