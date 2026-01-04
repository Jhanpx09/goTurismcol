<?php
require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/core/db.php';
require_once __DIR__ . '/../app/core/auth.php';
start_session();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $correo = trim($_POST['correo'] ?? '');
  $pass  = $_POST['contrasena'] ?? '';

  $pdo = db();
  $stmt = $pdo->prepare("SELECT id_usuario, contrasena_hash, estado FROM usuario WHERE correo = ?");
  $stmt->execute([$correo]);
  $u = $stmt->fetch();

  if (!$u || $u['estado'] !== 'activo' || !password_verify($pass, $u['contrasena_hash'])) {
    $errors[] = "Credenciales inválidas.";
  } else {
    login_user((int)$u['id_usuario']);
    redirect('index.php');
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Iniciar sesión | <?= e(config('app.app_name')) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e(base_url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../app/views/partials/nav.php'; ?>
<main class="container py-4" style="max-width: 720px;">
  <div class="card shadow-sm">
    <div class="card-body">
      <h1 class="h4 mb-3">Iniciar sesión</h1>
      <?php if ($errors): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
      <?php endif; ?>
      <form method="post" class="row g-3">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div class="col-12">
          <label class="form-label">Correo</label>
          <input type="email" name="correo" class="form-control" required value="<?= e($_POST['correo'] ?? '') ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Contraseña</label>
          <input type="password" name="contrasena" class="form-control" required>
        </div>
        <div class="col-12 d-grid"><button class="btn btn-primary">Ingresar</button></div>
      </form>
      <div class="text-secondary small mt-3"><strong>Acceso admin:</strong> admin@umb.local / Admin123!</div>
    </div>
  </div>
</main>
<?php include __DIR__ . '/../app/views/partials/footer.php'; ?>
</body>
</html>
