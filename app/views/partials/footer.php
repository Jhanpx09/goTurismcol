<footer class="site-footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <img class="brand-logo" src="<?= e(asset_url('assets/img/logo.webp')) ?>" alt="goTurismCol">
      <span class="brand-name">go<span class="brand-accent">Turism</span><span class="brand-accent-secondary">Col</span></span>
    </div>
    <p>© <?= date('Y') ?> <?= e(config('app.app_name')) ?>. Todos los derechos reservados.</p>
    <div class="footer-links">
      <a href="<?= e(base_url('experiencias.php')) ?>">Experiencias</a>
      <a href="<?= e(base_url('requisitos.php')) ?>">Requisitos</a>
      <a href="<?= e(base_url('register.php')) ?>">Registro</a>
    </div>
  </div>
</footer>
<div id="particles-js" class="particles-bg" aria-hidden="true"></div>
<button class="site-theme-toggle" type="button" id="site-theme-toggle" aria-label="Cambiar tema" aria-pressed="false">
  <span class="theme-icon icon-dark" aria-hidden="true">
    <svg viewBox="0 0 24 24" fill="currentColor" role="img">
      <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
    </svg>
  </span>
  <span class="theme-icon icon-light" aria-hidden="true">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" role="img">
      <circle cx="12" cy="12" r="5"></circle>
      <line x1="12" y1="1" x2="12" y2="3"></line>
      <line x1="12" y1="21" x2="12" y2="23"></line>
      <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
      <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
      <line x1="1" y1="12" x2="3" y2="12"></line>
      <line x1="21" y1="12" x2="23" y2="12"></line>
      <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
      <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
    </svg>
  </span>
</button>
<script src="<?= e(asset_url('particulas/js/site-particles.js')) ?>"></script>
<script>
  (function () {
    var body = document.body;
    if (!body || body.classList.contains('admin-body')) return;
    var toggle = document.getElementById('site-theme-toggle');
    var storageKey = 'site-theme';
    var stored = localStorage.getItem(storageKey);
    var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    var isDark = stored ? stored === 'dark' : prefersDark;

    function applyTheme(dark) {
      body.classList.toggle('theme-dark', dark);
      if (toggle) {
        toggle.setAttribute('aria-pressed', dark ? 'true' : 'false');
        toggle.setAttribute('aria-label', dark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro');
      }
    }

    applyTheme(isDark);

    if (toggle) {
      toggle.addEventListener('click', function () {
        isDark = !body.classList.contains('theme-dark');
        applyTheme(isDark);
        localStorage.setItem(storageKey, isDark ? 'dark' : 'light');
      });
    }
  })();
</script>
