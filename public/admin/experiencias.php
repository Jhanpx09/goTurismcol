<?php require_once __DIR__ . '/_admin_guard.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $id = (int)($_POST['id'] ?? 0);
  $decision = $_POST['decision'] ?? '';
  $obs = trim($_POST['observacion'] ?? '');

  if (!$id) $errors[] = "Experiencia inválida.";
  if (!in_array($decision, ['aprobada','rechazada'], true)) $errors[] = "Decisión inválida.";

  if (!$errors) {
    if ($decision === 'aprobada') {
      $pdo->prepare("UPDATE experiencia_viajero SET estado_moderacion='aprobada', fecha_publicacion=NOW() WHERE id_experiencia=?")->execute([$id]);
    } else {
      $pdo->prepare("UPDATE experiencia_viajero SET estado_moderacion='rechazada' WHERE id_experiencia=?")->execute([$id]);
    }
    $pdo->prepare("INSERT INTO moderacion_experiencia (id_experiencia, id_admin, decision, observacion) VALUES (?,?,?,?)")
        ->execute([$id, (int)$admin['id_usuario'], $decision, $obs ?: null]);
    redirect('admin/experiencias.php');
  }
}

$pendientes = $pdo->query("
  SELECT e.id_experiencia, e.titulo, e.contenido, e.fecha_envio, d.pais, d.ciudad, u.correo
  FROM experiencia_viajero e
  JOIN destino d ON d.id_destino = e.id_destino
  JOIN usuario u ON u.id_usuario = e.id_usuario
  WHERE e.estado_moderacion='pendiente'
  ORDER BY e.fecha_envio ASC
")->fetchAll();
$page_title = 'Moderacion';
$page_subtitle = 'Revisa y aprueba las experiencias pendientes.';
?>
<?php include __DIR__ . '/_layout_start.php'; ?>
  <div class="admin-section-head">
    <h2>Moderacion de experiencias</h2>
    <p>Aprueba o rechaza las experiencias enviadas por los viajeros.</p>
  </div>

  <div class="admin-page">
  <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

  <?php if (!$pendientes): ?>
    <div class="alert alert-info">No hay experiencias pendientes.</div>
  <?php else: ?>
    <?php foreach ($pendientes as $p): ?>
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-between flex-wrap gap-2">
            <strong><?= e($p['titulo']) ?></strong>
            <span class="text-secondary small"><?= e($p['pais'] . ($p['ciudad'] ? ' - ' . $p['ciudad'] : '')) ?></span>
          </div>
          <div class="text-secondary small mt-1">
            Autor: <?= e($p['correo']) ?> · Enviado: <?= e(date('Y-m-d', strtotime($p['fecha_envio']))) ?>
          </div>
          <p class="mt-3"><?= nl2br(e($p['contenido'])) ?></p>

          <form method="post" class="row g-2 align-items-end">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= (int)$p['id_experiencia'] ?>">
            <div class="col-md-5">
              <label class="form-label">Observación (opcional)</label>
              <input class="form-control" name="observacion" placeholder="Motivo breve si se rechaza">
            </div>
            <div class="col-md-7 d-flex gap-2">
              <button class="btn btn-success" name="decision" value="aprobada">Aprobar</button>
              <button class="btn btn-danger" name="decision" value="rechazada">Rechazar</button>
            </div>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
  </div>
<?php include __DIR__ . '/_layout_end.php'; ?>
