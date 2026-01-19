(function () {
  var body = document.body;
  if (!body || body.classList.contains('admin-body')) return;
  var container = document.getElementById('particles-js');
  if (!container) return;

  body.classList.add('has-particles');

  var canvas = document.createElement('canvas');
  canvas.setAttribute('aria-hidden', 'true');
  container.appendChild(canvas);
  var ctx = canvas.getContext('2d');
  if (!ctx) return;

  var dpr = window.devicePixelRatio || 1;
  var width = 0;
  var height = 0;
  var particles = [];
  var config = {
    count: 80,
    densityArea: 800,
    color: '#ffffff',
    size: 5,
    sizeRandom: true,
    opacity: 0.5,
    speed: 6,
    lines: true,
    lineDistance: 150,
    lineColor: '#ffffff',
    lineOpacity: 0.4
  };
  var themeState = '';

  function clamp(value, min, max) {
    return Math.max(min, Math.min(max, value));
  }

  function getPrimaryColor() {
    var style = window.getComputedStyle(body);
    var primary = style.getPropertyValue('--primary');
    if (primary) {
      return primary.trim();
    }
    return '#2563eb';
  }

  function resolveColors() {
    var isDark = body.classList.contains('theme-dark');
    themeState = isDark ? 'dark' : 'light';
    if (isDark) {
      config.color = config.color || '#ffffff';
      config.lineColor = config.lineColor || config.color;
    } else {
      var primary = getPrimaryColor();
      config.color = primary;
      config.lineColor = primary;
    }
  }

  function applyConfig(json) {
    var particlesCfg = (json && json.particles) || {};
    var numberCfg = particlesCfg.number || {};
    var densityCfg = numberCfg.density || {};
    config.count = numberCfg.value || config.count;
    config.densityArea = densityCfg.value_area || config.densityArea;
    config.color = (particlesCfg.color && particlesCfg.color.value) || config.color;
    config.opacity = (particlesCfg.opacity && particlesCfg.opacity.value) || config.opacity;
    config.size = (particlesCfg.size && particlesCfg.size.value) || config.size;
    config.sizeRandom = particlesCfg.size ? !!particlesCfg.size.random : config.sizeRandom;
    config.speed = (particlesCfg.move && particlesCfg.move.speed) || config.speed;
    config.lines = particlesCfg.line_linked ? !!particlesCfg.line_linked.enable : config.lines;
    config.lineDistance = (particlesCfg.line_linked && particlesCfg.line_linked.distance) || config.lineDistance;
    config.lineColor = (particlesCfg.line_linked && particlesCfg.line_linked.color) || config.lineColor;
    config.lineOpacity = (particlesCfg.line_linked && particlesCfg.line_linked.opacity) || config.lineOpacity;
    resolveColors();
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

  function buildParticles() {
    var area = (width * height) / config.densityArea;
    var count = config.count;
    if (area > 1) {
      count = Math.round(config.count * area);
    }
    count = clamp(count, 24, 160);
    particles = [];
    for (var i = 0; i < count; i += 1) {
      particles.push(createParticle());
    }
  }

  function createParticle() {
    var radius = config.sizeRandom ? config.size * (0.4 + Math.random() * 0.9) : config.size;
    var angle = Math.random() * Math.PI * 2;
    var speed = config.speed * 0.03;
    var velocity = speed * (0.4 + Math.random() * 0.8);
    return {
      x: Math.random() * width,
      y: Math.random() * height,
      r: radius,
      vx: Math.cos(angle) * velocity,
      vy: Math.sin(angle) * velocity
    };
  }

  function draw() {
    var isDark = body.classList.contains('theme-dark') ? 'dark' : 'light';
    if (isDark !== themeState) {
      resolveColors();
    }

    ctx.clearRect(0, 0, width, height);
    ctx.fillStyle = config.color;
    ctx.globalAlpha = config.opacity;
    for (var i = 0; i < particles.length; i += 1) {
      var p = particles[i];
      p.x += p.vx;
      p.y += p.vy;
      if (p.x < -10) p.x = width + 10;
      if (p.x > width + 10) p.x = -10;
      if (p.y < -10) p.y = height + 10;
      if (p.y > height + 10) p.y = -10;
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fill();
    }

    if (config.lines) {
      ctx.strokeStyle = config.lineColor;
      for (var a = 0; a < particles.length; a += 1) {
        for (var b = a + 1; b < particles.length; b += 1) {
          var pa = particles[a];
          var pb = particles[b];
          var dx = pa.x - pb.x;
          var dy = pa.y - pb.y;
          var dist = Math.sqrt(dx * dx + dy * dy);
          if (dist <= config.lineDistance) {
            var alpha = config.lineOpacity * (1 - dist / config.lineDistance);
            ctx.globalAlpha = alpha;
            ctx.beginPath();
            ctx.moveTo(pa.x, pa.y);
            ctx.lineTo(pb.x, pb.y);
            ctx.stroke();
          }
        }
      }
    }

    ctx.globalAlpha = 1;
    window.requestAnimationFrame(draw);
  }

  function init() {
    resize();
    window.addEventListener('resize', resize);
    window.requestAnimationFrame(draw);
  }

  fetch('particulas/particles.json', { cache: 'no-store' })
    .then(function (response) {
      if (!response.ok) return null;
      return response.json();
    })
    .then(function (json) {
      if (json) applyConfig(json);
      init();
    })
    .catch(function () {
      resolveColors();
      init();
    });
})();
