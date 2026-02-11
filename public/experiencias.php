<?php
require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/core/db.php';
start_session();

$pdo = db();
$destinos = $pdo->query("SELECT id_destino, pais, ciudad, bandera_path FROM destino WHERE estado='activo' ORDER BY pais, ciudad")->fetchAll();
$filtro = isset($_GET['destino']) ? (int)$_GET['destino'] : 0;
$selected_label = 'Todos los destinos';
if ($filtro) {
  foreach ($destinos as $d) {
    if ((int)$d['id_destino'] === $filtro) {
      $selected_label = $d['pais'] . ($d['ciudad'] ? ' - ' . $d['ciudad'] : '');
      break;
    }
  }
}

$sql = "
  SELECT e.titulo, e.contenido, e.fecha_publicacion, d.pais, d.ciudad, d.bandera_path, u.correo, u.nombre, u.apellido, u.foto_path
  FROM experiencia_viajero e
  JOIN destino d ON d.id_destino = e.id_destino
  JOIN usuario u ON u.id_usuario = e.id_usuario
  WHERE e.estado_moderacion='aprobada'
";
$params = [];
if ($filtro) { $sql .= " AND e.id_destino=?"; $params[] = $filtro; }
$sql .= " ORDER BY e.fecha_publicacion DESC LIMIT 30";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Experiencias | <?= e(config('app.app_name')) ?></title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
  <script>
    (function () {
      document.documentElement.classList.add('js');
      try {
        if (sessionStorage.getItem('page-loading') === '1') {
          document.documentElement.classList.add('is-loading');
        }
      } catch (err) {}
    })();
  </script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e(asset_url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../app/views/partials/nav.php'; ?>
<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h1 class="h4 mb-0">Experiencias de viajeros (aprobadas)</h1>
    <?php if (is_logged_in()): ?>
      <a class="btn btn-primary" href="<?= e(base_url('publicar_experiencia.php')) ?>">Publicar experiencia</a>
    <?php else: ?>
      <a class="btn btn-primary" href="<?= e(base_url('login.php')) ?>">Iniciar sesion para publicar</a>
    <?php endif; ?>
  </div>

  <form class="row g-2 mt-3 mb-4" method="get" id="experiencias-filter">
    <div class="col-md-8">
      <select class="form-select select-native" name="destino" id="experiencias-select">
        <option value="0" <?= $filtro ? '' : 'selected' ?>>Todos los destinos</option>
        <?php foreach ($destinos as $d): ?>
          <option value="<?= (int)$d['id_destino'] ?>" <?= $filtro===(int)$d['id_destino'] ? 'selected' : '' ?>>
            <?= e($d['pais'] . ($d['ciudad'] ? ' - ' . $d['ciudad'] : '')) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <div class="flag-search" id="experiencias-search">
        <div class="flag-search__wrapper">
          <span class="material-icons-round flag-search__icon" aria-hidden="true">search</span>
          <input
            class="flag-search__input"
            type="text"
            id="experiencias-search-input"
            placeholder="Buscar pais..."
            autocomplete="off"
            value="<?= e($selected_label) ?>"
            aria-haspopup="listbox"
            aria-expanded="false"
            aria-controls="experiencias-search-list"
          >
          <div class="flag-search__list" id="experiencias-search-list" role="listbox">
            <button class="flag-search__option <?= $filtro ? '' : 'is-active' ?>" type="button" data-value="0" data-label="Todos los destinos">
              <span class="flag-sphere flag-sphere--xs">
                <span class="flag-fallback">T</span>
              </span>
              <span class="flag-search__label">
                <span class="flag-search__country">Todos los destinos</span>
              </span>
            </button>
            <?php foreach ($destinos as $d): ?>
              <?php $is_active = $filtro === (int)$d['id_destino']; ?>
              <button
                class="flag-search__option <?= $is_active ? 'is-active' : '' ?>"
                type="button"
                data-value="<?= (int)$d['id_destino'] ?>"
                data-label="<?= e($d['pais'] . ' ' . ($d['ciudad'] ?? '')) ?>"
              >
                <span class="flag-sphere flag-sphere--xs">
                  <?php if (!empty($d['bandera_path'])): ?>
                    <img src="<?= e(asset_url($d['bandera_path'])) ?>" alt="Bandera de <?= e($d['pais']) ?>">
                  <?php else: ?>
                    <span class="flag-fallback"><?= e(substr($d['pais'], 0, 1)) ?></span>
                  <?php endif; ?>
                </span>
                <span class="flag-search__label">
                  <span class="flag-search__country"><?= e($d['pais']) ?></span>
                  <?php if (!empty($d['ciudad'])): ?>
                    <span class="flag-search__city"><?= e($d['ciudad']) ?></span>
                  <?php endif; ?>
                </span>
              </button>
            <?php endforeach; ?>
            <div class="flag-search__empty">Sin resultados</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4 d-grid"><button class="btn btn-outline-secondary">Filtrar</button></div>
  </form>

  <?php if (!$items): ?>
    <div class="alert alert-info">No hay experiencias aprobadas para mostrar.</div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($items as $it): ?>
        <?php $display_name = user_display_name($it['nombre'] ?? '', $it['apellido'] ?? '', $it['correo'] ?? ''); ?>
        <div class="col-12">
          <div class="card shadow-sm">
            <div class="card-body">
              <div class="d-flex justify-content-between flex-wrap gap-2">
                <h2 class="h6 mb-0"><?= e($it['titulo']) ?></h2>
                <div class="d-flex align-items-center gap-2">
                  <span class="text-secondary small"><?= e($it['pais'] . ($it['ciudad'] ? ' - ' . $it['ciudad'] : '')) ?></span>
                  <span class="experience-flag">
                    <span class="flag-sphere flag-sphere--sm">
                      <?php if (!empty($it['bandera_path'])): ?>
                        <img src="<?= e(asset_url($it['bandera_path'])) ?>" alt="Bandera de <?= e($it['pais']) ?>">
                      <?php else: ?>
                        <span class="flag-fallback"><?= e(substr($it['pais'], 0, 1)) ?></span>
                      <?php endif; ?>
                    </span>
                  </span>
                </div>
              </div>
              <div class="experience-meta">
                <span class="text-secondary small">Publicado: <?= e(date('Y-m-d', strtotime($it['fecha_publicacion']))) ?></span>
                <span class="experience-author">
                  <span class="user-avatar user-avatar--sm">
                    <img src="<?= e(asset_url(user_photo_path($it['foto_path'] ?? ''))) ?>" alt="Foto de <?= e($display_name) ?>">
                  </span>
                  <span class="text-secondary small"><?= e($display_name) ?></span>
                </span>
              </div>
              <p class="mt-3 mb-0"><?= nl2br(e($it['contenido'])) ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
<div class="page-loading" id="page-loading" aria-hidden="true">
  <div class="page-loading__spinner" role="status" aria-label="Cargando"></div>
  <div class="page-loading__text">Cargando...</div>
</div>
<?php include __DIR__ . '/../app/views/partials/footer.php'; ?>
<script>
  (function () {
    var form = document.getElementById('experiencias-filter');
    var loader = document.getElementById('page-loading');
    if (!form || !loader) return;

    var markLoading = function () {
      try {
        sessionStorage.setItem('page-loading', '1');
      } catch (err) {}
      document.documentElement.classList.add('is-loading');
      document.body.classList.add('is-loading');
    };

    form.addEventListener('submit', function (event) {
      if (form.dataset.submitting === 'true') return;
      event.preventDefault();
      form.dataset.submitting = 'true';
      markLoading();
      window.setTimeout(function () {
        form.submit();
      }, 80);
    });

    window.addEventListener('pageshow', function () {
      try {
        sessionStorage.removeItem('page-loading');
      } catch (err) {}
      document.documentElement.classList.remove('is-loading');
      document.body.classList.remove('is-loading');
      form.dataset.submitting = 'false';
    });
  })();
</script>
<script>
  (function () {
    var search = document.getElementById('experiencias-search');
    if (!search) return;

    var input = search.querySelector('.flag-search__input');
    var options = Array.prototype.slice.call(search.querySelectorAll('.flag-search__option'));
    var empty = search.querySelector('.flag-search__empty');
    var select = document.getElementById('experiencias-select');
    var form = document.getElementById('experiencias-filter');
    var selectedLabel = input ? input.value : '';

    function openList() {
      search.classList.add('is-open');
      if (input) input.setAttribute('aria-expanded', 'true');
    }

    function closeList() {
      search.classList.remove('is-open');
      if (input) {
        input.setAttribute('aria-expanded', 'false');
        input.value = selectedLabel;
      }
    }

    function filterOptions(query) {
      var term = (query || '').toLowerCase().trim();
      var matches = 0;
      options.forEach(function (option) {
        var label = option.getAttribute('data-label') || option.textContent || '';
        var isMatch = !term || label.toLowerCase().indexOf(term) !== -1;
        option.classList.toggle('is-hidden', !isMatch);
        if (isMatch) matches += 1;
      });
      if (empty) empty.style.display = matches ? 'none' : 'block';
    }

    function setActive(option) {
      options.forEach(function (opt) { opt.classList.remove('is-active'); });
      option.classList.add('is-active');
    }

    function optionLabel(option) {
      var country = option.querySelector('.flag-search__country');
      var city = option.querySelector('.flag-search__city');
      var label = country ? country.textContent : option.textContent;
      if (city && city.textContent) {
        label += ' - ' + city.textContent;
      }
      return label;
    }

    function submitForm() {
      if (!form) return;
      if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
      } else {
        form.submit();
      }
    }

    if (input) {
      input.addEventListener('focus', function () {
        openList();
        filterOptions(input.value === selectedLabel ? '' : input.value);
        input.select();
      });

      input.addEventListener('click', function () {
        openList();
        filterOptions(input.value === selectedLabel ? '' : input.value);
      });

      input.addEventListener('input', function () {
        openList();
        filterOptions(input.value);
      });

      input.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
          closeList();
          input.blur();
        }
        if (event.key === 'ArrowDown') {
          event.preventDefault();
          for (var i = 0; i < options.length; i += 1) {
            if (!options[i].classList.contains('is-hidden')) {
              options[i].focus();
              break;
            }
          }
        }
        if (event.key === 'Enter') {
          event.preventDefault();
        }
      });
    }

    options.forEach(function (option) {
      option.addEventListener('click', function () {
        var value = option.getAttribute('data-value');
        var label = optionLabel(option);
        selectedLabel = label;
        if (input) input.value = label;
        if (select) select.value = value;
        setActive(option);
        closeList();
        submitForm();
      });
    });

    document.addEventListener('click', function (event) {
      if (!search.contains(event.target)) closeList();
    });

    filterOptions('');
  })();
</script>
</body>
</html>





