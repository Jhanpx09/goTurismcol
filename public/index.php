<?php
require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/core/db.php';
require_once __DIR__ . '/../app/core/auth.php';
start_session();

$pdo = db();
$destinos = $pdo->query("SELECT id_destino, pais, ciudad, bandera_path FROM destino WHERE estado='activo' ORDER BY pais, ciudad")->fetchAll();
$avisos = $pdo->query("
  SELECT a.titulo_aviso, a.detalle_aviso, a.fecha_publicacion, d.pais, d.ciudad
  FROM aviso_actualizacion a
  JOIN destino d ON d.id_destino = a.id_destino
  WHERE a.estado='activo'
  ORDER BY a.fecha_publicacion DESC
  LIMIT 5
")->fetchAll();
$stats = $pdo->query("
  SELECT
    (SELECT COUNT(*) FROM experiencia_viajero WHERE estado_moderacion='aprobada') AS total_experiencias,
    (SELECT COUNT(*) FROM destino WHERE estado='activo') AS total_destinos
")->fetch();
$destinos_destacados = $pdo->query("
  SELECT dd.id_destacado, dd.titulo, dd.descripcion, dd.imagen_path, dd.id_destino, d.pais, d.ciudad
  FROM destino_destacado dd
  JOIN destino d ON d.id_destino = dd.id_destino
  WHERE dd.estado='activo' AND d.estado='activo'
  ORDER BY dd.orden ASC, dd.id_destacado DESC
  LIMIT 4
")->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(config('app.app_name')) ?></title>
  <link href="<?= e(base_url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../app/views/partials/nav.php'; ?>

<main class="page">
  <section class="hero" style="--hero-image: url('<?= e(base_url('assets/img/main.webp')) ?>');">
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <h1>Planificación de viajes con información organizada</h1>
      <p>Consulta requisitos y trámites por destino, revisa experiencias de otros viajeros y publica la tuya de forma segura y moderada.</p>
      <div class="hero-actions">
        <a class="btn btn-primary" href="<?= e(base_url('requisitos.php')) ?>">Empezar ahora</a>
      </div>
    </div>
  </section>

  <div class="content-grid">
    <div class="col-main">
      <section class="card requirements-card requirements-card--home">
        <div class="card-title">
          <span class="card-icon">🧭</span>
          <h2>Consultar requisitos por destino</h2>
        </div>
        <form class="requirements-form" method="get" action="<?= e(base_url('requisitos.php')) ?>">
          <div class="select-wrap">
            <select class="select-native" name="destino" id="destino-select" required>
              <option value="" selected disabled>Seleccione un destino</option>
              <?php foreach ($destinos as $d): ?>
                <option value="<?= (int)$d['id_destino'] ?>">
                  <?= e($d['pais'] . ($d['ciudad'] ? ' - ' . $d['ciudad'] : '')) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="custom-select" data-select="destino-select">
              <button class="custom-select__trigger" type="button" aria-haspopup="listbox" aria-expanded="false">
                <span class="custom-select__value">Seleccione un destino</span>
                <span class="custom-select__caret">▾</span>
              </button>
              <div class="custom-select__menu" role="listbox">
                <div class="custom-select__search">
                  <input type="text" placeholder="Buscar destino" aria-label="Buscar destino">
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
                        <img src="<?= e(base_url($d['bandera_path'])) ?>" alt="Bandera de <?= e($d['pais']) ?>">
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
          <button class="btn btn-primary" type="submit">Consultar</button>
        </form>
        <p class="helper-text">La información se administra internamente e incluye la fecha de última actualización.</p>
      </section>

      <section class="card experience-card" style="--experience-image: url('<?= e(base_url('assets/img/experience.webp')) ?>');">
        <div class="experience-overlay"></div>
        <div class="experience-content">
          <h2>Experiencias de viajeros</h2>
          <p>Solo se muestra contenido aprobado por moderación.</p>
          <div class="experience-actions">
            <a class="btn btn-light" href="<?= e(base_url('experiencias.php')) ?>">Ver experiencias</a>
            <?php if (is_logged_in()): ?>
              <a class="btn btn-primary" href="<?= e(base_url('publicar_experiencia.php')) ?>">Publicar experiencia</a>
            <?php else: ?>
              <a class="btn btn-primary" href="<?= e(base_url('login.php')) ?>">Iniciar sesión para publicar</a>
            <?php endif; ?>
          </div>
        </div>
      </section>

      <section class="destinations" id="destinos">
        <div class="destinations-head">
          <h2>Destinos destacados</h2>
          <a class="link-primary" href="<?= e(base_url('requisitos.php')) ?>">Ver todos</a>
        </div>
        <?php if (!$destinos_destacados): ?>
          <p class="helper-text">Aun no hay destinos destacados.</p>
        <?php else: ?>
          <div class="destinations-grid">
            <?php foreach ($destinos_destacados as $d): ?>
              <article class="destination-card">
                <img class="destination-image" src="<?= e(base_url($d['imagen_path'])) ?>" alt="<?= e($d['titulo']) ?>">
                <div class="destination-body">
                  <div class="destination-meta">
                    <span><?= e($d['pais']) ?></span>
                    <?php if (!empty($d['ciudad'])): ?>
                      <span><?= e($d['ciudad']) ?></span>
                    <?php endif; ?>
                  </div>
                  <h3><?= e($d['titulo']) ?></h3>
                  <p><?= nl2br(e($d['descripcion'])) ?></p>
                  <a class="link-primary" href="<?= e(base_url('requisitos.php?destino=' . (int)$d['id_destino'])) ?>">Ver requisitos</a>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </div>

    <aside class="col-side">
      <section class="card notice-card">
        <div class="card-title">
          <span class="card-icon">🔔</span>
          <h2>Avisos de actualización</h2>
        </div>
        <?php if (!$avisos): ?>
          <p class="helper-text">No hay avisos recientes.</p>
        <?php else: ?>
          <div class="notice-list">
            <?php foreach ($avisos as $a): ?>
              <div class="notice-item">
                <div class="notice-header">
                  <strong><?= e($a['titulo_aviso']) ?></strong>
                  <span><?= e(date('Y-m-d', strtotime($a['fecha_publicacion']))) ?></span>
                </div>
                <div class="notice-meta">
                  <?= e($a['pais'] . ($a['ciudad'] ? ' - ' . $a['ciudad'] : '')) ?>
                </div>
                <div class="notice-body"><?= nl2br(e($a['detalle_aviso'])) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <a class="btn btn-outline btn-block" href="<?= e(base_url('avisos.php')) ?>">Ver historial de avisos</a>
      </section>

      <section class="card community-card">
        <h2>Comunidad</h2>
        <div class="community-grid">
          <div>
            <strong><?= (int)($stats['total_experiencias'] ?? 0) ?></strong>
            <span>Experiencias</span>
          </div>
          <div>
            <strong><?= (int)($stats['total_destinos'] ?? 0) ?></strong>
            <span>Destinos</span>
          </div>
        </div>
      </section>
    </aside>
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
