<?php require_once __DIR__ . '/_admin_guard.php';
$destinos = $pdo->query("SELECT id_destino, pais, ciudad FROM destino ORDER BY pais, ciudad")->fetchAll();
$errors = [];

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
      redirect('admin/avisos.php');
    }
  } elseif ($action === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare("UPDATE aviso_actualizacion SET estado = IF(estado='activo','inactivo','activo') WHERE id_aviso=?")->execute([$id]);
    redirect('admin/avisos.php');
  }
}

$avisos = $pdo->query("
  SELECT a.*, d.pais, d.ciudad
  FROM aviso_actualizacion a
  JOIN destino d ON d.id_destino = a.id_destino
  ORDER BY a.estado DESC, a.fecha_publicacion DESC
")->fetchAll();
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
                <form method="post" class="d-inline">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= (int)$a['id_aviso'] ?>">
                  <button class="btn btn-outline-secondary btn-sm"><?= $a['estado']==='activo'?'Desactivar':'Activar' ?></button>
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
