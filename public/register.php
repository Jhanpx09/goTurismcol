<?php
require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/core/db.php';
require_once __DIR__ . '/../app/core/auth.php';
start_session();

$pdo = db();
$destinos = $pdo->query("SELECT id_destino, pais, ciudad, bandera_path FROM destino WHERE estado='activo' ORDER BY pais, ciudad")->fetchAll();
$destino_ids = array_map(fn($row) => (int)$row['id_destino'], $destinos ?: []);
$selected_destino = (int)($_POST['id_destino'] ?? 0);

function save_user_photo(?array $file, array &$errors): ?string {
  if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    return null;
  }
  if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
    $errors[] = "Error al subir la foto.";
    return null;
  }
  $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
  $allowed = ['jpg', 'jpeg', 'png', 'webp'];
  if (!$ext || !in_array($ext, $allowed, true)) {
    $errors[] = "Formato de foto no valido. Use jpg, png o webp.";
    return null;
  }
  $dir = __DIR__ . '/assets/usuarios';
  if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
    $errors[] = "No se pudo crear la carpeta de fotos.";
    return null;
  }
  $unique = str_replace('.', '', uniqid('user_', true));
  $filename = $unique . '.' . $ext;
  $dest_path = $dir . '/' . $filename;
  if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
    $errors[] = "No se pudo guardar la foto.";
    return null;
  }
  return 'assets/usuarios/' . $filename;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $nombre = trim($_POST['nombre'] ?? '');
  $apellido = trim($_POST['apellido'] ?? '');
  $telefono = trim($_POST['telefono'] ?? '');
  $id_destino = (int)($_POST['id_destino'] ?? 0);
  $correo = trim($_POST['correo'] ?? '');
  $pass  = $_POST['contrasena'] ?? '';
  $pass2 = $_POST['contrasena2'] ?? '';

  if ($nombre === '') $errors[] = "El nombre es obligatorio.";
  if ($apellido === '') $errors[] = "El apellido es obligatorio.";
  if ($telefono === '') $errors[] = "El telefono es obligatorio.";
  if ($telefono !== '' && strlen(preg_replace('/\\D/', '', $telefono)) < 7) {
    $errors[] = "El telefono debe tener al menos 7 digitos.";
  }
  if ($id_destino <= 0 || !in_array($id_destino, $destino_ids, true)) $errors[] = "Seleccione un pais valido.";
  if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errors[] = "Ingrese un correo valido.";
  if (strlen($pass) < 8) $errors[] = "La contrasena debe tener al menos 8 caracteres.";
  if ($pass !== $pass2) $errors[] = "Las contrasenas no coinciden.";

  if (!$errors) {
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE correo = ?");
    $stmt->execute([$correo]);
    if ($stmt->fetch()) {
      $errors[] = "Este correo ya esta registrado.";
    } else {
      $hash = password_hash($pass, PASSWORD_BCRYPT);
      $foto_path = save_user_photo($_FILES['foto'] ?? null, $errors);
      if (!$foto_path) {
        $foto_path = 'assets/img/user-default.svg';
      }
      $stmt = $pdo->prepare("
        INSERT INTO usuario (correo, nombre, apellido, telefono, id_destino, foto_path, contrasena_hash)
        VALUES (?,?,?,?,?,?,?)
      ");
      $stmt->execute([$correo, $nombre, $apellido, $telefono, $id_destino, $foto_path, $hash]);
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
      <form method="post" class="row g-3" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div class="col-md-6">
          <label class="form-label">Nombre</label>
          <input type="text" name="nombre" class="form-control" required value="<?= e($_POST['nombre'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Apellido</label>
          <input type="text" name="apellido" class="form-control" required value="<?= e($_POST['apellido'] ?? '') ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Pais</label>
          <div class="select-wrap">
            <select class="select-native" name="id_destino" id="registro-destino" required>
              <option value="" disabled selected>Seleccione un pais</option>
              <?php foreach ($destinos as $d): ?>
                <option value="<?= (int)$d['id_destino'] ?>" <?= $selected_destino === (int)$d['id_destino'] ? 'selected' : '' ?>>
                  <?= e($d['pais'] . ($d['ciudad'] ? ' - ' . $d['ciudad'] : '')) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="custom-select" data-select="registro-destino">
              <button class="custom-select__trigger" type="button" aria-haspopup="listbox" aria-expanded="false">
                <span class="custom-select__value">Seleccione un pais</span>
                <span class="custom-select__caret">v</span>
              </button>
              <div class="custom-select__menu" role="listbox">
                <div class="custom-select__search">
                  <input type="text" placeholder="Buscar pais" aria-label="Buscar pais">
                </div>
                <?php foreach ($destinos as $d): ?>
                  <button
                    class="custom-select__option"
                    type="button"
                    data-value="<?= (int)$d['id_destino'] ?>"
                    data-label="<?= e($d['pais'] . ($d['ciudad'] ? ' - ' . $d['ciudad'] : '')) ?>"
                    role="option"
                  >
                    <span class="flag-sphere flag-sphere--xs">
                      <?php if (!empty($d['bandera_path'])): ?>
                        <img src="<?= e(asset_url($d['bandera_path'])) ?>" alt="Bandera de <?= e($d['pais']) ?>">
                      <?php else: ?>
                        <span class="flag-fallback"><?= e(substr($d['pais'], 0, 1)) ?></span>
                      <?php endif; ?>
                    </span>
                    <span class="custom-select__label"><?= e($d['pais'] . ($d['ciudad'] ? ' - ' . $d['ciudad'] : '')) ?></span>
                  </button>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Telefono</label>
          <input type="text" name="telefono" class="form-control" required value="<?= e($_POST['telefono'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Correo</label>
          <input type="email" name="correo" class="form-control" required value="<?= e($_POST['correo'] ?? '') ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Fotografia (opcional)</label>
          <input type="file" name="foto" class="form-control" accept=".png,.jpg,.jpeg,.webp">
          <div class="form-text">Si no subes una imagen, se asignara una foto por defecto.</div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Contrasena</label>
          <input type="password" name="contrasena" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Confirmar contrasena</label>
          <input type="password" name="contrasena2" class="form-control" required>
        </div>
        <div class="col-12 d-grid"><button class="btn btn-primary">Registrarme</button></div>
      </form>
      <p class="text-secondary small mt-3 mb-0">Si ya tienes cuenta, <a href="<?= e(base_url('login.php')) ?>">inicia sesiÃ³n</a>.</p>
    </div>
  </div>
</main>
<?php include __DIR__ . '/../app/views/partials/footer.php'; ?>
<script>
  document.documentElement.classList.add('js');
  document.querySelectorAll('.custom-select').forEach(function (wrapper) {
    var selectId = wrapper.getAttribute('data-select');
    var select = document.getElementById(selectId);
    if (!select) return;

    var trigger = wrapper.querySelector('.custom-select__trigger');
    var valueEl = wrapper.querySelector('.custom-select__value');
    var options = wrapper.querySelectorAll('.custom-select__option');
    var searchInput = wrapper.querySelector('.custom-select__search input');

    function clearSearch() {
      if (!searchInput) return;
      searchInput.value = '';
      filterOptions('');
    }

    function filterOptions(query) {
      var term = (query || '').toLowerCase().trim();
      options.forEach(function (option) {
        var label = option.getAttribute('data-label') || option.textContent || '';
        var match = !term || label.toLowerCase().indexOf(term) !== -1;
        option.classList.toggle('is-hidden', !match);
      });
    }

    function closeMenu() {
      wrapper.classList.remove('is-open');
      trigger.setAttribute('aria-expanded', 'false');
      clearSearch();
    }

    function openMenu() {
      wrapper.classList.add('is-open');
      trigger.setAttribute('aria-expanded', 'true');
      if (searchInput) {
        searchInput.focus();
        searchInput.select();
      }
    }

    function syncSelected() {
      var selected = select.value;
      options.forEach(function (opt) { opt.classList.remove('is-selected'); });
      options.forEach(function (option) {
        if (option.getAttribute('data-value') === selected) {
          option.classList.add('is-selected');
          var label = option.querySelector('.custom-select__label');
          valueEl.textContent = label ? label.textContent : option.textContent;
        }
      });
    }

    trigger.addEventListener('click', function () {
      if (wrapper.classList.contains('is-open')) {
        closeMenu();
      } else {
        openMenu();
      }
    });

    options.forEach(function (option) {
      option.addEventListener('click', function () {
        var value = option.getAttribute('data-value');
        var label = option.querySelector('.custom-select__label');
        select.value = value;
        valueEl.textContent = label ? label.textContent : option.textContent;
        options.forEach(function (opt) { opt.classList.remove('is-selected'); });
        option.classList.add('is-selected');
        select.dispatchEvent(new Event('change'));
        closeMenu();
      });
    });

    if (searchInput) {
      searchInput.addEventListener('input', function () {
        filterOptions(searchInput.value);
      });
      searchInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
          event.preventDefault();
        }
      });
    }

    document.addEventListener('click', function (event) {
      if (!wrapper.contains(event.target)) closeMenu();
    });

    syncSelected();
  });
</script>
</body>
</html>



