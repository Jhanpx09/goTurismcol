<?php
require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/core/db.php';
start_session();

$pdo = db();
$destinos = $pdo->query("SELECT id_destino, pais, ciudad, bandera_path FROM destino WHERE estado='activo' ORDER BY pais, ciudad")->fetchAll();

$destino_id = isset($_GET['destino']) ? (int)$_GET['destino'] : 0;
if (!$destino_id && $destinos) {
  $destino_id = (int)$destinos[0]['id_destino'];
}

$destino = null;
$requisitos = [];
$experiencias = [];
if ($destino_id) {
  $stmt = $pdo->prepare("SELECT * FROM destino WHERE id_destino=? AND estado='activo'");
  $stmt->execute([$destino_id]);
  $destino = $stmt->fetch();

  if ($destino) {
    $stmt = $pdo->prepare("
      SELECT titulo_requisito, descripcion_requisito, tipo_requisito, fuente_oficial, fecha_ultima_actualizacion
      FROM requisito_viaje
      WHERE id_destino=? AND estado='vigente'
      ORDER BY tipo_requisito, titulo_requisito
    ");
    $stmt->execute([$destino_id]);
    $requisitos = $stmt->fetchAll();

    $stmt = $pdo->prepare("
      SELECT e.titulo, e.contenido, e.fecha_publicacion, u.correo, u.nombre, u.apellido, u.foto_path
      FROM experiencia_viajero e
      JOIN usuario u ON u.id_usuario = e.id_usuario
      WHERE e.id_destino=? AND e.estado_moderacion='aprobada'
      ORDER BY e.fecha_publicacion DESC
      LIMIT 20
    ");
    $stmt->execute([$destino_id]);
    $experiencias = $stmt->fetchAll();
  }
}

function map_requisito_tipo(string $tipo): string {
  $key = strtolower(trim($tipo));
  $aliases = [
    'obligatorio' => 'obligatorio',
    'obligatoria' => 'obligatorio',
    'recomendado' => 'recomendado',
    'recomendada' => 'recomendado',
    'informacion' => 'informacion',
    'informaciÃ³n' => 'informacion',
    'info' => 'informacion',
    'general' => 'informacion',
    'informacion general' => 'informacion',
    'informacion gral' => 'informacion',
    'migratorio' => 'obligatorio',
    'documental' => 'obligatorio',
    'sanitario' => 'obligatorio',
  ];
  return $aliases[$key] ?? 'informacion';
}

function tipo_label(string $tipo): string {
  $map = [
    'obligatorio' => 'Obligatorio',
    'recomendado' => 'Recomendado',
    'informacion' => 'InformaciÃ³n gral.',
  ];
  return $map[$tipo] ?? 'InformaciÃ³n gral.';
}

$grouped = [
  'obligatorio' => [],
  'recomendado' => [],
];
foreach ($requisitos as $r) {
  $bucket = map_requisito_tipo($r['tipo_requisito']);
  if (!isset($grouped[$bucket])) {
    $bucket = 'recomendado';
  }
  $grouped[$bucket][] = $r;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Requisitos | <?= e(config('app.app_name')) ?></title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
  <link href="<?= e(asset_url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../app/views/partials/nav.php'; ?>

<main class="requirements-page">
  <section class="requirements-hero">
    <h1>Requisitos por destino</h1>
    <p>Selecciona un paÃ­s del carrusel para visualizar los requisitos de viaje, trÃ¡mites migratorios y recomendaciones de salud actualizados.</p>
  </section>

  <section class="flag-carousel">
    <?php if (!$destinos): ?>
      <p class="helper-text">No hay destinos activos para mostrar.</p>
    <?php else: ?>
      <div class="flag-search" id="flag-search">
        <div class="flag-search__wrapper">
          <span class="material-icons-round flag-search__icon" aria-hidden="true">search</span>
          <input
            class="flag-search__input"
            type="text"
            id="flag-search-input"
            placeholder="Buscar pais..."
            autocomplete="off"
            aria-haspopup="listbox"
            aria-expanded="false"
            aria-controls="flag-search-list"
          >
          <div class="flag-search__list" id="flag-search-list" role="listbox">
            <?php foreach ($destinos as $d): ?>
              <?php $is_active = $destino_id === (int)$d['id_destino']; ?>
              <button
                class="flag-search__option <?= $is_active ? 'is-active' : '' ?>"
                type="button"
                data-destino="<?= (int)$d['id_destino'] ?>"
                data-label="<?= e($d['pais'] . ' ' . ($d['ciudad'] ?? '')) ?>"
                data-url="<?= e(base_url('requisitos.php?destino=' . (int)$d['id_destino'])) ?>"
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
      <div class="flag-carousel__inner">
        <button class="flag-nav flag-nav--prev" type="button" aria-label="Mover a la izquierda" aria-controls="flag-track">
          <span class="material-icons-round flag-nav__icon" aria-hidden="true">chevron_left</span>
        </button>
        <div class="flag-carousel__viewport">
          <div class="flag-track" id="flag-track" data-current-id="<?= (int)$destino_id ?>">
            <?php foreach ($destinos as $d): ?>
              <?php $is_active = $destino_id === (int)$d['id_destino']; ?>
              <a class="flag-item <?= $is_active ? 'is-active' : '' ?>" href="<?= e(base_url('requisitos.php?destino=' . (int)$d['id_destino'])) ?>" data-destino="<?= (int)$d['id_destino'] ?>">
                <span class="flag-sphere">
                  <?php if (!empty($d['bandera_path'])): ?>
                    <img src="<?= e(asset_url($d['bandera_path'])) ?>" alt="Bandera de <?= e($d['pais']) ?>">
                  <?php else: ?>
                    <span class="flag-fallback"><?= e(substr($d['pais'], 0, 1)) ?></span>
                  <?php endif; ?>
                </span>
                <span class="flag-label"><?= e($d['pais']) ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
        <button class="flag-nav flag-nav--next" type="button" aria-label="Mover a la derecha" aria-controls="flag-track">
          <span class="material-icons-round flag-nav__icon" aria-hidden="true">chevron_right</span>
        </button>
      </div>
    <?php endif; ?>
  </section>

  <section class="requirements-card">
    <?php if (!$destino): ?>
      <p class="helper-text">Seleccione un destino para ver los requisitos disponibles.</p>
    <?php else: ?>
      <div class="requirements-tabs">
        <?php foreach (['obligatorio', 'recomendado', 'experiencias'] as $idx => $key): ?>
          <button class="tab-btn <?= $idx === 0 ? 'is-active' : '' ?>" data-tab="<?= e($key) ?>">
            <?= $key === 'experiencias' ? 'Experiencias' : e(tipo_label($key)) ?>
          </button>
        <?php endforeach; ?>
      </div>

      <div class="requirements-content">
        <?php foreach ($grouped as $key => $items): ?>
          <div class="tab-panel <?= $key === 'obligatorio' ? 'is-active' : '' ?>" data-panel="<?= e($key) ?>">
            <div class="panel-head">
              <h2><?= e(tipo_label($key)) ?></h2>
              <?php if ($destino): ?>
                <span><?= e($destino['pais'] . ($destino['ciudad'] ? ' - ' . $destino['ciudad'] : '')) ?></span>
              <?php endif; ?>
            </div>

            <?php if (!$items): ?>
              <p class="helper-text">No hay requisitos registrados para esta categorÃ­a.</p>
            <?php else: ?>
              <div class="requirements-list">
                <?php foreach ($items as $r): ?>
                  <article class="requirement-item">
                    <div class="requirement-icon">âœ”</div>
                    <div class="requirement-body">
                      <h3><?= e($r['titulo_requisito']) ?></h3>
                      <p><?= nl2br(e($r['descripcion_requisito'])) ?></p>
                      <div class="requirement-meta">
                        <span>Actualizado: <?= e($r['fecha_ultima_actualizacion']) ?></span>
                        <?php if (!empty($r['fuente_oficial'])): ?>
                          <span>Fuente: <?= e($r['fuente_oficial']) ?></span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>

        <div class="tab-panel" data-panel="experiencias">
          <div class="panel-head">
            <h2>Experiencias</h2>
            <?php if ($destino): ?>
              <span><?= e($destino['pais'] . ($destino['ciudad'] ? ' - ' . $destino['ciudad'] : '')) ?></span>
            <?php endif; ?>
          </div>

          <?php if (!$experiencias): ?>
            <p class="helper-text">No hay experiencias aprobadas para este destino.</p>
          <?php else: ?>
            <div class="experience-list">
              <?php foreach ($experiencias as $it): ?>
                <?php $display_name = user_display_name($it['nombre'] ?? '', $it['apellido'] ?? '', $it['correo'] ?? ''); ?>
                <article class="experience-item">
                  <div class="experience-header">
                    <h3><?= e($it['titulo']) ?></h3>
                    <span><?= e(date('Y-m-d', strtotime($it['fecha_publicacion']))) ?></span>
                  </div>
                  <p><?= nl2br(e($it['contenido'])) ?></p>
                  <div class="experience-author">
                    <span class="user-avatar user-avatar--xs">
                      <img src="<?= e(asset_url(user_photo_path($it['foto_path'] ?? ''))) ?>" alt="Foto de <?= e($display_name) ?>">
                    </span>
                    <small><?= e($display_name) ?></small>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
            <div class="experience-footer">
              <a class="link-primary" href="<?= e(base_url('experiencias.php?destino=' . (int)$destino_id)) ?>">Ver todas las experiencias</a>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="requirements-footer">
        <a class="btn btn-primary" href="<?= e(base_url('requisitos_pdf.php?destino=' . (int)$destino_id)) ?>">Descargar guÃ­a completa (PDF)</a>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php include __DIR__ . '/../app/views/partials/footer.php'; ?>
<script>
  document.querySelectorAll('.tab-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var key = btn.getAttribute('data-tab');
      document.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.remove('is-active'); });
      document.querySelectorAll('.tab-panel').forEach(function (p) { p.classList.remove('is-active'); });
      btn.classList.add('is-active');
      var panel = document.querySelector('.tab-panel[data-panel=\"' + key + '\"]');
      if (panel) panel.classList.add('is-active');
    });
  });

  var flagTrack = document.getElementById('flag-track');
  if (flagTrack) {
    var prevBtn = document.querySelector('.flag-nav--prev');
    var nextBtn = document.querySelector('.flag-nav--next');
    var storageKey = 'requisitos-flag-scroll';
    var currentId = flagTrack.getAttribute('data-current-id');

    var getScrollStep = function () {
      var item = flagTrack.querySelector('.flag-item');
      if (!item) return 240;
      var styles = window.getComputedStyle(flagTrack);
      var gapValue = parseFloat(styles.columnGap);
      if (Number.isNaN(gapValue)) gapValue = parseFloat(styles.gap);
      if (Number.isNaN(gapValue)) gapValue = 0;
      return item.getBoundingClientRect().width + gapValue;
    };

    var updateFlagNav = function () {
      var maxScroll = flagTrack.scrollWidth - flagTrack.clientWidth;
      if (prevBtn) prevBtn.disabled = flagTrack.scrollLeft <= 2;
      if (nextBtn) nextBtn.disabled = flagTrack.scrollLeft >= maxScroll - 2;
    };

    var restoreScroll = function () {
      var saved = null;
      try {
        saved = JSON.parse(localStorage.getItem(storageKey));
      } catch (err) {
        saved = null;
      }

      if (saved && currentId && String(saved.id) === String(currentId)) {
        if (typeof saved.scrollLeft === 'number') {
          flagTrack.scrollLeft = saved.scrollLeft;
          updateFlagNav();
          return;
        }
      }

      var activeItem = flagTrack.querySelector('.flag-item.is-active');
      if (activeItem) {
        activeItem.scrollIntoView({ block: 'nearest', inline: 'center' });
        updateFlagNav();
      }
    };

    var scrollFlags = function (dir) {
      var amount = getScrollStep();
      flagTrack.scrollBy({ left: amount * dir, behavior: 'smooth' });
    };

    if (prevBtn) {
      prevBtn.addEventListener('click', function () {
        scrollFlags(-1);
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', function () {
        scrollFlags(1);
      });
    }

    flagTrack.addEventListener('click', function (event) {
      var target = event.target.closest('.flag-item');
      if (!target) return;
      var id = target.getAttribute('data-destino') || currentId;
      var payload = {
        id: id,
        scrollLeft: flagTrack.scrollLeft
      };
      try {
        localStorage.setItem(storageKey, JSON.stringify(payload));
      } catch (err) {
        // ignore storage errors
      }
    });

    flagTrack.addEventListener('scroll', function () {
      window.requestAnimationFrame(updateFlagNav);
    });

    window.addEventListener('resize', function () {
      window.requestAnimationFrame(updateFlagNav);
    });

    restoreScroll();
  }

  (function () {
    var search = document.getElementById('flag-search');
    if (!search) return;

    var input = search.querySelector('.flag-search__input');
    var list = search.querySelector('.flag-search__list');
    var options = Array.prototype.slice.call(search.querySelectorAll('.flag-search__option'));
    var empty = search.querySelector('.flag-search__empty');

    function openList() {
      search.classList.add('is-open');
      if (input) input.setAttribute('aria-expanded', 'true');
    }

    function closeList() {
      search.classList.remove('is-open');
      if (input) input.setAttribute('aria-expanded', 'false');
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

    function focusFirstVisible() {
      for (var i = 0; i < options.length; i += 1) {
        if (!options[i].classList.contains('is-hidden')) {
          options[i].focus();
          return;
        }
      }
    }

    if (input) {
      input.addEventListener('focus', function () {
        openList();
        filterOptions(input.value);
      });

      input.addEventListener('click', function () {
        openList();
        filterOptions(input.value);
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
          focusFirstVisible();
        }
      });
    }

    options.forEach(function (option) {
      option.addEventListener('click', function () {
        var url = option.getAttribute('data-url');
        if (url) window.location.href = url;
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

