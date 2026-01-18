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
</body>
</html>
