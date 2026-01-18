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
