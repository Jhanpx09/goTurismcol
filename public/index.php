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
$hero_slides = [];
try {
  $hero_slides = $pdo->query("
    SELECT id_slide, titulo, descripcion, enlace_texto, enlace_url, imagen_path, intervalo_segundos
    FROM hero_slide
    WHERE estado='activo'
    ORDER BY orden ASC, id_slide DESC
  ")->fetchAll();
} catch (PDOException $e) {
  $hero_slides = [];
}
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
  <link href="<?= e(asset_url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../app/views/partials/nav.php'; ?>

<main class="page">
  <?php if ($hero_slides): ?>
    <?php $hero_first = $hero_slides[0]; ?>
    <section class="hero hero-slider">
      <div class="hero-slider-track">
        <?php foreach ($hero_slides as $idx => $slide): ?>
          <div
            class="hero-slide <?= $idx === 0 ? 'is-active' : '' ?>"
            style="--hero-image: url('<?= e(asset_url($slide['imagen_path'])) ?>');"
            data-title="<?= e($slide['titulo']) ?>"
            data-description="<?= e($slide['descripcion']) ?>"
            data-cta-text="<?= e($slide['enlace_texto']) ?>"
            data-cta-url="<?= e($slide['enlace_url']) ?>"
            data-interval="<?= (int)($slide['intervalo_segundos'] ?? 7) ?>"
          ></div>
        <?php endforeach; ?>
      </div>
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <h1 class="hero-title">
          <span class="hero-title-text"><?= e($hero_first['titulo']) ?></span>
          <span class="hero-title-caret" aria-hidden="true">|</span>
        </h1>
        <p class="hero-description"><?= e($hero_first['descripcion']) ?></p>
        <div class="hero-actions">
          <a class="btn btn-primary hero-cta" href="<?= e($hero_first['enlace_url']) ?>"><?= e($hero_first['enlace_texto']) ?></a>
        </div>
      </div>
    </section>
  <?php else: ?>
    <section class="hero" style="--hero-image: url('<?= e(asset_url('assets/img/main.webp')) ?>');">
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <h1>Planificaci¢n de viajes con informaci¢n organizada</h1>
        <p>Consulta requisitos y tr mites por destino, revisa experiencias de otros viajeros y publica la tuya de forma segura y moderada.</p>
        <div class="hero-actions">
          <a class="btn btn-primary" href="<?= e(base_url('requisitos.php')) ?>">Empezar ahora</a>
        </div>
      </div>
    </section>
  <?php endif; ?>

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
          <button class="btn btn-primary" type="submit">Consultar</button>
        </form>
        <p class="helper-text">La información se administra internamente e incluye la fecha de última actualización.</p>
      </section>

      <section class="card experience-card" style="--experience-image: url('<?= e(asset_url('assets/img/plub_epxence.webp')) ?>');">
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
                <img class="destination-image" src="<?= e(asset_url($d['imagen_path'])) ?>" alt="<?= e($d['titulo']) ?>">
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

  (function () {
    var slider = document.querySelector('.hero-slider');
    if (!slider) return;
    var slides = Array.prototype.slice.call(slider.querySelectorAll('.hero-slide'));
    if (!slides.length) return;

    var titleText = slider.querySelector('.hero-title-text');
    var descEl = slider.querySelector('.hero-description');
    var ctaEl = slider.querySelector('.hero-cta');
    var currentIndex = 0;
    var typingTimer = null;
    var timer = null;
    var defaultSeconds = 7;
    var prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function typeTitle(text) {
      if (!titleText) return;
      if (typingTimer) {
        clearTimeout(typingTimer);
        typingTimer = null;
      }
      titleText.textContent = '';
      if (prefersReduced) {
        titleText.textContent = text;
        return;
      }
      var idx = 0;
      function step() {
        titleText.textContent = text.slice(0, idx);
        idx += 1;
        if (idx <= text.length) {
          typingTimer = window.setTimeout(step, 40);
        }
      }
      step();
    }

    function setSlide(index) {
      slides.forEach(function (slide, i) {
        slide.classList.toggle('is-active', i === index);
      });
      var slide = slides[index];
      if (!slide) return;
      var title = slide.getAttribute('data-title') || '';
      var description = slide.getAttribute('data-description') || '';
      var ctaText = slide.getAttribute('data-cta-text') || 'Ver mas';
      var ctaUrl = slide.getAttribute('data-cta-url') || '#';
      if (descEl) descEl.textContent = description;
      if (ctaEl) {
        ctaEl.textContent = ctaText;
        ctaEl.setAttribute('href', ctaUrl);
      }
      typeTitle(title);
    }

    function getSlideDelay(slide) {
      if (!slide) return defaultSeconds * 1000;
      var seconds = parseInt(slide.getAttribute('data-interval'), 10);
      if (!seconds || seconds < 3) seconds = defaultSeconds;
      if (seconds > 30) seconds = 30;
      return seconds * 1000;
    }

    function scheduleNext() {
      if (prefersReduced || slides.length <= 1) return;
      if (timer) {
        clearTimeout(timer);
      }
      var delay = getSlideDelay(slides[currentIndex]);
      timer = window.setTimeout(function () {
        currentIndex = (currentIndex + 1) % slides.length;
        setSlide(currentIndex);
        scheduleNext();
      }, delay);
    }

    setSlide(currentIndex);
    scheduleNext();
  })();
</script>
</body>
</html>

