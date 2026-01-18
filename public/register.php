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
  $pass2 = $_POST['contrasena2'] ?? '';

  if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errors[] = "Ingrese un correo válido.";
  if (strlen($pass) < 8) $errors[] = "La contraseña debe tener mínimo 8 caracteres.";
  if ($pass !== $pass2) $errors[] = "Las contraseñas no coinciden.";

  if (!$errors) {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE correo = ?");
    $stmt->execute([$correo]);
    if ($stmt->fetch()) {
      $errors[] = "Este correo ya está registrado.";
    } else {
      $hash = password_hash($pass, PASSWORD_BCRYPT);
      $stmt = $pdo->prepare("INSERT INTO usuario (correo, contrasena_hash) VALUES (?,?)");
      $stmt->execute([$correo, $hash]);
      $id = (int)$pdo->lastInsertId();
      $pdo->prepare("INSERT INTO usuario_rol (id_usuario, id_rol) VALUES (?,1)")->execute([$id]);
      login_user($id);
      redirect('index.php');
    }
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registrarse | <?= e(config('app.app_name')) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e(asset_url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../app/views/partials/nav.php'; ?>
<main class="container py-4" style="max-width: 720px;">
  <div class="card shadow-sm">
    <div class="card-body">
      <h1 class="h4 mb-3">Crear cuenta</h1>
      <?php if ($errors): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
      <?php endif; ?>
      <form method="post" class="row g-3">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div class="col-12">
          <label class="form-label">Correo</label>
          <input type="email" name="correo" class="form-control" required value="<?= e($_POST['correo'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Contraseña</label>
          <input type="password" name="contrasena" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Confirmar contraseña</label>
          <input type="password" name="contrasena2" class="form-control" required>
        </div>
        <div class="col-12 d-grid"><button class="btn btn-primary">Registrarme</button></div>
      </form>
      <p class="text-secondary small mt-3 mb-0">Si ya tienes cuenta, <a href="<?= e(base_url('login.php')) ?>">inicia sesión</a>.</p>
    </div>
  </div>
</main>
<?php include __DIR__ . '/../app/views/partials/footer.php'; ?>
</body>
</html>
