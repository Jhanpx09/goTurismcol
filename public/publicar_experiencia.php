<?php
require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/core/db.php';
require_once __DIR__ . '/../app/core/auth.php';
require_login();

$pdo = db();
$destinos = $pdo->query("SELECT id_destino, pais, ciudad FROM destino WHERE estado='activo' ORDER BY pais, ciudad")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $destino = (int)($_POST['destino'] ?? 0);
  $titulo = trim($_POST['titulo'] ?? '');
  $contenido = trim($_POST['contenido'] ?? '');

  if (!$destino) $errors[] = "Seleccione un destino.";
  if (strlen($titulo) < 5) $errors[] = "El título debe tener al menos 5 caracteres.";
  if (strlen($contenido) < 30) $errors[] = "La experiencia debe tener al menos 30 caracteres.";

  if (!$errors) {
    $u = current_user();
    $stmt = $pdo->prepare("INSERT INTO experiencia_viajero (id_usuario, id_destino, titulo, contenido) VALUES (?,?,?,?)");
    $stmt->execute([(int)$u['id_usuario'], $destino, $titulo, $contenido]);
    redirect('mis_experiencias.php');
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Publicar experiencia | <?= e(config('app.app_name')) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e(asset_url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../app/views/partials/nav.php'; ?>
<main class="container py-4" style="max-width: 900px;">
  <div class="card shadow-sm">
    <div class="card-body">
      <h1 class="h4 mb-3">Publicar experiencia de viaje</h1>
      <p class="text-secondary">Las experiencias se publican después de una revisión por el administrador.</p>

      <?php if ($errors): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
      <?php endif; ?>

      <form method="post" class="row g-3">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div class="col-12">
          <label class="form-label">Destino</label>
          <select class="form-select" name="destino" required>
            <option value="" disabled selected>Seleccione un destino</option>
            <?php foreach ($destinos as $d): ?>
              <option value="<?= (int)$d['id_destino'] ?>"><?= e($d['pais'] . ($d['ciudad'] ? ' - ' . $d['ciudad'] : '')) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Título</label>
          <input class="form-control" name="titulo" required value="<?= e($_POST['titulo'] ?? '') ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Experiencia</label>
          <textarea class="form-control" name="contenido" rows="7" required><?= e($_POST['contenido'] ?? '') ?></textarea>
        </div>
        <div class="col-12 d-grid"><button class="btn btn-primary">Enviar a revisión</button></div>
      </form>
    </div>
  </div>
</main>
<?php include __DIR__ . '/../app/views/partials/footer.php'; ?>
</body>
</html>
