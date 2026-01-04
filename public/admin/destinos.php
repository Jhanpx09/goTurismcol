<?php require_once __DIR__ . '/_admin_guard.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $action = $_POST['action'] ?? '';
  if ($action === 'create') {
    $pais = trim($_POST['pais'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $desc = trim($_POST['descripcion'] ?? '');
    if (!$pais) $errors[] = "El país es obligatorio.";
    if (!$errors) {
      $pdo->prepare("INSERT INTO destino (pais, ciudad, descripcion_general) VALUES (?,?,?)")
          ->execute([$pais, $ciudad ?: null, $desc ?: null]);
      redirect('admin/destinos.php');
    }
  } elseif ($action === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare("UPDATE destino SET estado = IF(estado='activo','inactivo','activo') WHERE id_destino=?")->execute([$id]);
    redirect('admin/destinos.php');
  }
}
$items = $pdo->query("SELECT * FROM destino ORDER BY estado DESC, pais, ciudad")->fetchAll();
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
      <form method="post" class="row g-2">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="create">
        <div class="col-md-4"><input class="form-control" name="pais" placeholder="País" required></div>
        <div class="col-md-4"><input class="form-control" name="ciudad" placeholder="Ciudad (opcional)"></div>
        <div class="col-md-4"><input class="form-control" name="descripcion" placeholder="Descripción (opcional)"></div>
        <div class="col-12 d-grid"><button class="btn btn-primary">Guardar</button></div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <h2 class="h6">Listado</h2>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead><tr><th>ID</th><th>Destino</th><th>Estado</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td><?= (int)$it['id_destino'] ?></td>
              <td><?= e($it['pais'] . ($it['ciudad'] ? ' - ' . $it['ciudad'] : '')) ?></td>
              <td><span class="badge text-bg-<?= $it['estado']==='activo'?'success':'secondary' ?>"><?= e($it['estado']) ?></span></td>
              <td class="text-end">
                <form method="post" class="d-inline">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= (int)$it['id_destino'] ?>">
                  <button class="btn btn-outline-secondary btn-sm"><?= $it['estado']==='activo'?'Desactivar':'Activar' ?></button>
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
