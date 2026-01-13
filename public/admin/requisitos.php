<?php require_once __DIR__ . '/_admin_guard.php';
$destinos = $pdo->query("SELECT id_destino, pais, ciudad FROM destino ORDER BY pais, ciudad")->fetchAll();
$destino_id = isset($_GET['destino']) ? (int)$_GET['destino'] : 0;
$errors = [];
function requisito_tipo_label(string $tipo): string {
  $key = strtolower(trim($tipo));
  $map = [
    'obligatorio' => 'Obligatorio',
    'recomendado' => 'Recomendado',
    'informacion' => 'Información gral.',
    'información' => 'Información gral.',
  ];
  return $map[$key] ?? ucfirst($tipo);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $action = $_POST['action'] ?? '';

  if ($action === 'create') {
    $id_destino = (int)($_POST['id_destino'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $desc = trim($_POST['descripcion'] ?? '');
    $fuente = trim($_POST['fuente'] ?? '');
    $fecha = $_POST['fecha'] ?? date('Y-m-d');

    if (!$id_destino) $errors[] = "Seleccione un destino.";
    if (!$titulo) $errors[] = "El título es obligatorio.";
    if (!$tipo) $errors[] = "El tipo es obligatorio.";
    if (!$desc) $errors[] = "La descripción es obligatoria.";

    if (!$errors) {
      $pdo->prepare("
        INSERT INTO requisito_viaje (id_destino, titulo_requisito, descripcion_requisito, tipo_requisito, fuente_oficial, fecha_ultima_actualizacion, creado_por)
        VALUES (?,?,?,?,?,?,?)
      ")->execute([$id_destino, $titulo, $desc, $tipo, $fuente ?: null, $fecha, (int)$admin['id_usuario']]);
      redirect('admin/requisitos.php?destino=' . $id_destino);
    }
  } elseif ($action === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare("UPDATE requisito_viaje SET estado = IF(estado='vigente','no_vigente','vigente') WHERE id_requisito=?")->execute([$id]);
    redirect('admin/requisitos.php?destino=' . (int)($_POST['destino_id'] ?? 0));
  } elseif ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $texto = trim($_POST['cambio'] ?? '');
    if (!$texto) $errors[] = "Describa el cambio realizado.";
    if (!$errors) {
      $pdo->prepare("INSERT INTO actualizacion_requisito (id_requisito, descripcion_cambio, actualizado_por) VALUES (?,?,?)")
          ->execute([$id, $texto, (int)$admin['id_usuario']]);
      $pdo->prepare("UPDATE requisito_viaje SET fecha_ultima_actualizacion = CURDATE() WHERE id_requisito=?")->execute([$id]);
      redirect('admin/requisitos.php?destino=' . (int)($_POST['destino_id'] ?? 0));
    }
  }
}

$requisitos = [];
if ($destino_id) {
  $stmt = $pdo->prepare("SELECT * FROM requisito_viaje WHERE id_destino=? ORDER BY estado DESC, tipo_requisito, titulo_requisito");
  $stmt->execute([$destino_id]);
  $requisitos = $stmt->fetchAll();
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Requisitos | Panel admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e(base_url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/_nav.php'; ?>
<main class="container py-4">
  <h1 class="h4 mb-3">Requisitos de viaje</h1>
  <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

  <form class="row g-2 mb-4" method="get">
    <div class="col-md-8">
      <select class="form-select" name="destino" required>
        <option value="" disabled <?= $destino_id ? '' : 'selected' ?>>Seleccione un destino</option>
        <?php foreach ($destinos as $d): ?>
          <option value="<?= (int)$d['id_destino'] ?>" <?= $destino_id===(int)$d['id_destino']?'selected':'' ?>>
            <?= e($d['pais'] . ($d['ciudad'] ? ' - ' . $d['ciudad'] : '')) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4 d-grid"><button class="btn btn-outline-secondary">Cargar</button></div>
  </form>

  <?php if ($destino_id): ?>
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <h2 class="h6">Crear requisito</h2>
        <form method="post" class="row g-2">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="action" value="create">
          <input type="hidden" name="id_destino" value="<?= (int)$destino_id ?>">
          <div class="col-md-4"><input class="form-control" name="titulo" placeholder="Título" required></div>
          <div class="col-md-3">
            <select class="form-select" name="tipo" required>
              <option value="" disabled selected>Tipo</option>
              <option value="obligatorio">Obligatorio</option>
              <option value="recomendado">Recomendado</option>
              <option value="informacion">Información gral.</option>
            </select>
          </div>
          <div class="col-md-2"><input class="form-control" type="date" name="fecha" value="<?= e(date('Y-m-d')) ?>" required></div>
          <div class="col-md-3"><input class="form-control" name="fuente" placeholder="Fuente oficial (opcional)"></div>
          <div class="col-12"><textarea class="form-control" name="descripcion" rows="3" placeholder="Descripción" required></textarea></div>
          <div class="col-12 d-grid"><button class="btn btn-primary">Guardar</button></div>
        </form>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-body">
        <h2 class="h6">Listado</h2>
        <?php if (!$requisitos): ?>
          <div class="alert alert-info mb-0">No hay requisitos registrados para este destino.</div>
        <?php else: ?>
          <?php foreach ($requisitos as $r): ?>
            <div class="border rounded p-3 mb-3 bg-white">
              <div class="d-flex justify-content-between flex-wrap gap-2">
                <strong><?= e($r['titulo_requisito']) ?></strong>
                <span class="badge text-bg-<?= $r['estado']==='vigente'?'success':'secondary' ?>"><?= e($r['estado']) ?></span>
              </div>
              <div class="text-secondary small">Tipo: <?= e(requisito_tipo_label($r['tipo_requisito'])) ?> · Últ. actualización: <?= e($r['fecha_ultima_actualizacion']) ?></div>
              <div class="mt-2"><?= nl2br(e($r['descripcion_requisito'])) ?></div>
              <?php if ($r['fuente_oficial']): ?>
                <div class="text-secondary small mt-2"><strong>Fuente:</strong> <?= nl2br(e($r['fuente_oficial'])) ?></div>
              <?php endif; ?>

              <div class="mt-3 d-flex gap-2 flex-wrap">
                <form method="post">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= (int)$r['id_requisito'] ?>">
                  <input type="hidden" name="destino_id" value="<?= (int)$destino_id ?>">
                  <button class="btn btn-outline-secondary btn-sm"><?= $r['estado']==='vigente'?'Marcar no vigente':'Marcar vigente' ?></button>
                </form>

                <form method="post" class="d-flex gap-2 flex-wrap">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="id" value="<?= (int)$r['id_requisito'] ?>">
                  <input type="hidden" name="destino_id" value="<?= (int)$destino_id ?>">
                  <input class="form-control form-control-sm" name="cambio" placeholder="Describir cambio y registrar actualización" style="min-width:320px;" required>
                  <button class="btn btn-primary btn-sm">Registrar</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  <?php else: ?>
    <p class="text-secondary">Seleccione un destino para gestionar sus requisitos.</p>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/../../app/views/partials/footer.php'; ?>
</body>
</html>
