        </div>
      </div>
    </div>
  </div>
</div>

<button class="admin-theme-toggle" type="button" id="admin-theme-toggle" aria-label="Cambiar tema">
  <span class="material-icons-round icon-dark">dark_mode</span>
  <span class="material-icons-round icon-light">light_mode</span>
</button>

<script>
  (function () {
    var body = document.body;
    var toggle = document.getElementById('admin-theme-toggle');
    if (!toggle) return;
    var stored = localStorage.getItem('admin-theme');
    if (stored === 'dark') {
      body.classList.add('admin-dark');
    }
    toggle.addEventListener('click', function () {
      body.classList.toggle('admin-dark');
      localStorage.setItem('admin-theme', body.classList.contains('admin-dark') ? 'dark' : 'light');
    });
  })();
</script>
<script>
  (function () {
    var canvas = document.getElementById('admin-particles');
    if (!canvas) return;
    var ctx = canvas.getContext('2d');
    if (!ctx) return;
    var width = 0;
    var height = 0;
    var dpr = window.devicePixelRatio || 1;
    var particles = [];

    function buildParticles() {
      var count = Math.max(24, Math.min(120, Math.floor((width * height) / 26000)));
      particles = [];
      for (var i = 0; i < count; i += 1) {
        particles.push({
          x: Math.random() * width,
          y: Math.random() * height,
          r: 1 + Math.random() * 2.4,
          vx: (Math.random() - 0.5) * 0.4,
          vy: (Math.random() - 0.5) * 0.4
        });
      }
    }

    function resize() {
      width = window.innerWidth;
      height = window.innerHeight;
      canvas.width = width * dpr;
      canvas.height = height * dpr;
      canvas.style.width = width + 'px';
      canvas.style.height = height + 'px';
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      buildParticles();
    }

    function particleColor() {
      return document.body.classList.contains('admin-dark')
        ? 'rgba(148, 163, 184, 0.45)'
        : 'rgba(59, 130, 246, 0.25)';
    }

    function step() {
      ctx.clearRect(0, 0, width, height);
      ctx.fillStyle = particleColor();
      for (var i = 0; i < particles.length; i += 1) {
        var p = particles[i];
        p.x += p.vx;
        p.y += p.vy;
        if (p.x < -5) p.x = width + 5;
        if (p.x > width + 5) p.x = -5;
        if (p.y < -5) p.y = height + 5;
        if (p.y > height + 5) p.y = -5;
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
        ctx.fill();
      }
      window.requestAnimationFrame(step);
    }

    window.addEventListener('resize', resize);
    resize();
    window.requestAnimationFrame(step);
  })();
</script>
</body>
</html>
