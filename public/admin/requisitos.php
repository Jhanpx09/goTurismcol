<?php require_once __DIR__ . '/_admin_guard.php';
$destinos = $pdo->query("SELECT id_destino, pais, ciudad FROM destino ORDER BY pais, ciudad")->fetchAll();
$destino_id = isset($_GET['destino']) ? (int)$_GET['destino'] : 0;
$errors = [];
function requisito_tipo_label(string $tipo): string {
  $key = strtolower(trim($tipo));
  $map = [
    'obligatorio' => 'Obligatorio',
    'recomendado' => 'Recomendado',
    'informacion' => 'Información gral.',
    'información' => 'Información gral.',
  ];
  return $map[$key] ?? ucfirst($tipo);
}

$requisito_iconos = [
  'fiber_manual_record' => 'Vineta',
  'warning' => 'Advertencia',
  'check_circle' => 'Check',
  'assignment_ind' => 'Pasaporte',
];
$requisito_icono_default = 'check_circle';

function requisito_icono_normalize(?string $icono, array $permitidos, string $fallback): string {
  $icono = trim((string)$icono);
  return ($icono && isset($permitidos[$icono])) ? $icono : $fallback;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $action = $_POST['action'] ?? '';

  if ($action === 'create') {
    $id_destino = (int)($_POST['id_destino'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $desc = trim($_POST['descripcion'] ?? '');
    $icono = requisito_icono_normalize($_POST['icono'] ?? '', $requisito_iconos, $requisito_icono_default);
    $fuente = trim($_POST['fuente'] ?? '');
    $fecha = trim($_POST['fecha'] ?? '');

    if (!$id_destino) $errors[] = "Seleccione un destino.";
    if (!$titulo) $errors[] = "El título es obligatorio.";
    if (!$tipo) $errors[] = "El tipo es obligatorio.";
    if (!$desc) $errors[] = "La descripción es obligatoria.";
    if (!$fecha) $errors[] = "La fecha de actualizacion es obligatoria.";
    if (!$fuente) $errors[] = "La fuente oficial es obligatoria.";

    if (!$errors) {
      $stmt = $pdo->prepare("
        INSERT INTO requisito_viaje (id_destino, titulo_requisito, descripcion_requisito, tipo_requisito, icono, fuente_oficial, fecha_ultima_actualizacion, creado_por)
        VALUES (?,?,?,?,?,?,?,?)
      ");
      $stmt->execute([$id_destino, $titulo, $desc, $tipo, $icono, $fuente ?: null, $fecha, (int)$admin['id_usuario']]);
      if ($stmt->rowCount() > 0) {
        redirect('admin/requisitos.php?destino=' . $id_destino . '&created=1');
      } else {
        $errors[] = "No se pudo crear el requisito.";
      }
    }
  } elseif ($action === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare("UPDATE requisito_viaje SET estado = IF(estado='vigente','no_vigente','vigente') WHERE id_requisito=?")->execute([$id]);
    redirect('admin/requisitos.php?destino=' . (int)($_POST['destino_id'] ?? 0) . '&updated=1');
  } elseif ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $texto = trim($_POST['cambio'] ?? '');
    if (!$texto) $errors[] = "Describa el cambio realizado.";
    if (!$errors) {
      $pdo->prepare("INSERT INTO actualizacion_requisito (id_requisito, descripcion_cambio, actualizado_por) VALUES (?,?,?)")
          ->execute([$id, $texto, (int)$admin['id_usuario']]);
      $pdo->prepare("UPDATE requisito_viaje SET fecha_ultima_actualizacion = CURDATE() WHERE id_requisito=?")->execute([$id]);
      redirect('admin/requisitos.php?destino=' . (int)($_POST['destino_id'] ?? 0) . '&updated=1');
    }
  } elseif ($action === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $desc = trim($_POST['descripcion'] ?? '');
    $icono = requisito_icono_normalize($_POST['icono'] ?? '', $requisito_iconos, $requisito_icono_default);
    $fuente = trim($_POST['fuente'] ?? '');
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $texto = trim($_POST['cambio'] ?? '');

    if (!$titulo) $errors[] = "El título es obligatorio.";
    if (!$tipo) $errors[] = "El tipo es obligatorio.";
    if (!$desc) $errors[] = "La descripción es obligatoria.";
    if (!$texto) $errors[] = "Describa el cambio realizado.";

    if (!$errors) {
      $pdo->prepare("
        UPDATE requisito_viaje
        SET titulo_requisito=?, descripcion_requisito=?, tipo_requisito=?, icono=?, fuente_oficial=?, fecha_ultima_actualizacion=?
        WHERE id_requisito=?
      ")->execute([$titulo, $desc, $tipo, $icono, $fuente ?: null, $fecha, $id]);
      $pdo->prepare("INSERT INTO actualizacion_requisito (id_requisito, descripcion_cambio, actualizado_por) VALUES (?,?,?)")
          ->execute([$id, $texto, (int)$admin['id_usuario']]);
      redirect('admin/requisitos.php?destino=' . (int)($_POST['destino_id'] ?? 0));
    }
  } elseif ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
      $pdo->prepare("DELETE FROM requisito_viaje WHERE id_requisito=?")->execute([$id]);
    }
    redirect('admin/requisitos.php?destino=' . (int)($_POST['destino_id'] ?? 0));
  }
}

$requisitos = [];
if ($destino_id) {
  $stmt = $pdo->prepare("SELECT * FROM requisito_viaje WHERE id_destino=? ORDER BY estado DESC, tipo_requisito, titulo_requisito");
  $stmt->execute([$destino_id]);
  $requisitos = $stmt->fetchAll();

  $actualizaciones = [];
  if ($requisitos) {
    $ids = array_map(fn($r) => (int)$r['id_requisito'], $requisitos);
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT ar.*, u.correo FROM actualizacion_requisito ar JOIN usuario u ON u.id_usuario = ar.actualizado_por WHERE ar.id_requisito IN ($in) ORDER BY ar.fecha_actualizacion DESC");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $row) {
      $actualizaciones[(int)$row['id_requisito']][] = $row;
    }
  }
}

$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$new_modal = isset($_GET['new']) ? 1 : 0;
$edit_requisito = null;
if ($edit_id && $requisitos) {
  foreach ($requisitos as $req) {
    if ((int)$req['id_requisito'] === $edit_id) {
      $edit_requisito = $req;
      break;
    }
  }
}
$icono_selected_new = $requisito_icono_default;
$icono_selected_edit = $edit_requisito
  ? requisito_icono_normalize($edit_requisito['icono'] ?? '', $requisito_iconos, $requisito_icono_default)
  : $requisito_icono_default;
$page_title = 'Requisitos';
$page_subtitle = 'Gestiona requisitos de viaje y actualizaciones.';
?>
<?php include __DIR__ . '/_layout_start.php'; ?>
  <div class="admin-section-head">
    <h2>Requisitos de viaje</h2>
    <p>Administra requisitos por destino y registra cambios.</p>
  </div>

  <div class="admin-page">
  <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

  <form class="row g-2 mb-4" method="get">
    <div class="col-md-8">
      <select class="form-select" name="destino" required>
        <option value="" disabled <?= $destino_id ? '' : 'selected' ?>>Seleccione un destino</option>
        <?php foreach ($destinos as $d): ?>
          <option value="<?= (int)$d['id_destino'] ?>" <?= $destino_id===(int)$d['id_destino']?'selected':'' ?>>
            <?= e($d['pais'] . ($d['ciudad'] ? ' - ' . $d['ciudad'] : '')) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4 d-grid"><button class="btn btn-outline-secondary">Cargar</button></div>
  </form>

  <?php if ($destino_id): ?>
    <?php if (!empty($_GET['created'])): ?>
      <div class="alert alert-success">Requisito creado correctamente.</div>
    <?php endif; ?>
    <?php if (!empty($_GET['updated'])): ?>
      <div class="alert alert-success">Requisito actualizado correctamente.</div>
    <?php endif; ?>

    <div class="d-flex justify-content-end mb-3">
      <a class="btn btn-primary" href="<?= e(base_url('admin/requisitos.php?destino=' . (int)$destino_id . '&new=1')) ?>">+ Nuevo requisito</a>
    </div>

    <?php if ($new_modal): ?>
      <div class="req-modal-backdrop">
        <div class="req-modal">
          <div class="req-modal-head">
            <h2 class="h6 mb-0">Crear requisito</h2>
            <a class="req-modal-close" href="<?= e(base_url('admin/requisitos.php?destino=' . (int)$destino_id)) ?>">Cancelar</a>
          </div>
          <form method="post" class="row g-2 mt-3">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="id_destino" value="<?= (int)$destino_id ?>">
            <div class="col-md-4"><input class="form-control form-control-sm" name="titulo" placeholder="Titulo" required></div>
            <div class="col-md-3">
              <select class="form-select form-select-sm" name="tipo" required>
                <option value="" disabled selected>Tipo</option>
                <option value="obligatorio">Obligatorio</option>
                <option value="recomendado">Recomendado</option>
                <option value="informacion">Informacion gral.</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label small text-secondary">Icono</label>
              <div class="d-flex flex-wrap gap-2">
                <?php foreach ($requisito_iconos as $icon_value => $icon_label): ?>
                  <?php $icon_id = 'icono-new-' . $icon_value; ?>
                  <input class="btn-check" type="radio" name="icono" id="<?= e($icon_id) ?>" value="<?= e($icon_value) ?>" <?= $icon_value === $icono_selected_new ? 'checked' : '' ?>>
                  <label class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" for="<?= e($icon_id) ?>">
                    <span class="material-icons-round" aria-hidden="true"><?= e($icon_value) ?></span>
                    <span><?= e($icon_label) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="col-md-2"><input class="form-control form-control-sm" type="date" name="fecha" value="<?= e(date('Y-m-d')) ?>" required></div>
            <div class="col-md-3"><input class="form-control form-control-sm" name="fuente" placeholder="Fuente oficial" required></div>
            <div class="col-12"><textarea class="form-control form-control-sm" name="descripcion" rows="3" placeholder="Descripcion" required></textarea></div>
            <div class="col-12"><button class="btn btn-primary w-100">Guardar</button></div>
          </form>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($edit_requisito): ?>
      <div class="req-modal-backdrop">
        <div class="req-modal">
          <div class="req-modal-head">
            <h2 class="h6 mb-0">Editar requisito</h2>
            <a class="req-modal-close" href="<?= e(base_url('admin/requisitos.php?destino=' . (int)$destino_id)) ?>">Cancelar</a>
          </div>
          <form method="post" class="row g-2 mt-3">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= (int)$edit_requisito['id_requisito'] ?>">
            <input type="hidden" name="destino_id" value="<?= (int)$destino_id ?>">
            <div class="col-md-4"><input class="form-control form-control-sm" name="titulo" value="<?= e($edit_requisito['titulo_requisito']) ?>" required></div>
            <div class="col-md-3">
              <select class="form-select form-select-sm" name="tipo" required>
                <option value="obligatorio" <?= $edit_requisito['tipo_requisito']==='obligatorio'?'selected':'' ?>>Obligatorio</option>
                <option value="recomendado" <?= $edit_requisito['tipo_requisito']==='recomendado'?'selected':'' ?>>Recomendado</option>
                <option value="informacion" <?= $edit_requisito['tipo_requisito']==='informacion'?'selected':'' ?>>Informacion gral.</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label small text-secondary">Icono</label>
              <div class="d-flex flex-wrap gap-2">
                <?php foreach ($requisito_iconos as $icon_value => $icon_label): ?>
                  <?php $icon_id = 'icono-edit-' . $icon_value; ?>
                  <input class="btn-check" type="radio" name="icono" id="<?= e($icon_id) ?>" value="<?= e($icon_value) ?>" <?= $icon_value === $icono_selected_edit ? 'checked' : '' ?>>
                  <label class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" for="<?= e($icon_id) ?>">
                    <span class="material-icons-round" aria-hidden="true"><?= e($icon_value) ?></span>
                    <span><?= e($icon_label) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="col-md-2"><input class="form-control form-control-sm" type="date" name="fecha" value="<?= e($edit_requisito['fecha_ultima_actualizacion']) ?>" required></div>
            <div class="col-md-3"><input class="form-control form-control-sm" name="fuente" value="<?= e($edit_requisito['fuente_oficial']) ?>" placeholder="Fuente oficial (opcional)"></div>
            <div class="col-12">
              <label class="form-label small text-secondary" for="edit_descripcion">Descripcion</label>
              <textarea class="form-control form-control-sm" id="edit_descripcion" name="descripcion" rows="3" placeholder="Descripcion" required><?= e($edit_requisito['descripcion_requisito']) ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label small text-secondary" for="edit_cambio">Cambio para el historial</label>
              <input class="form-control form-control-sm" id="edit_cambio" name="cambio" placeholder="Describir cambio para el historial" required>
            </div>
            <div class="col-12"><button class="btn btn-primary w-100">Guardar cambios</button></div>
          </form>
        </div>
      </div>
    <?php endif; ?>

    <div class="card shadow-sm">
      <div class="card-body">
        <h2 class="h6">Listado</h2>
        <?php if (!$requisitos): ?>
          <div class="alert alert-info mb-0">No hay requisitos registrados para este destino.</div>
        <?php else: ?>
          <?php foreach ($requisitos as $r): ?>
            <div class="border rounded p-3 mb-3 bg-white">
              <div class="req-layout">
                <div class="req-main">
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <strong><?= e($r['titulo_requisito']) ?></strong>
                    <span class="badge text-bg-<?= $r['estado']==='vigente'?'success':'secondary' ?>"><?= e($r['estado']) ?></span>
                  </div>
                  <div class="d-flex align-items-center gap-4 text-secondary small mt-1 flex-wrap">
                    <div>Tipo: <strong><?= e(requisito_tipo_label($r['tipo_requisito'])) ?></strong></div>
                    <div>Ult. actualizacion: <?= e($r['fecha_ultima_actualizacion']) ?></div>
                  </div>

                  <div class="bg-light rounded p-2 mt-3 req-desc">
                    <div class="small text-secondary fw-semibold text-uppercase mb-1">Descripcion</div>
                    <div><?= nl2br(e($r['descripcion_requisito'])) ?></div>
                  </div>
                  <?php if ($r['fuente_oficial']): ?>
                    <div class="text-secondary small mt-2"><strong>Fuente:</strong> <?= nl2br(e($r['fuente_oficial'])) ?></div>
                  <?php endif; ?>

                  <?php if (!empty($actualizaciones[(int)$r['id_requisito']])): ?>
                    <div class="mt-3 border-top pt-2">
                      <div class="small text-secondary fw-semibold text-uppercase">Actualizaciones recientes</div>
                      <div class="mt-2 d-grid gap-2 req-updates">
                        <?php $updates = array_slice($actualizaciones[(int)$r['id_requisito']], 0, 5); ?>
                        <?php foreach ($updates as $a): ?>
                          <div class="d-flex gap-2 align-items-start">
                            <div class="text-secondary small">?</div>
                            <div class="small">
                              <div><span class="text-secondary"><?= e(date('Y-m-d H:i', strtotime($a['fecha_actualizacion']))) ?></span> - <?= nl2br(e($a['descripcion_cambio'])) ?></div>
                              <div class="text-secondary"><?= e($a['correo']) ?></div>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="req-actions">
                  <a class="req-action req-action--primary" href="<?= e(base_url('admin/requisitos.php?destino=' . (int)$destino_id . '&edit=' . (int)$r['id_requisito'])) ?>">Editar</a>
                  <form method="post">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?= (int)$r['id_requisito'] ?>">
                    <input type="hidden" name="destino_id" value="<?= (int)$destino_id ?>">
                    <button class="req-action req-action--muted"><?= $r['estado']==='vigente'?'Ocultar':'Mostrar' ?></button>
                  </form>
                  <form method="post" onsubmit="return confirm('Eliminar este requisito? Esta accion no se puede deshacer.');">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$r['id_requisito'] ?>">
                    <input type="hidden" name="destino_id" value="<?= (int)$destino_id ?>">
                    <button class="req-action req-action--danger">Eliminar</button>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  <?php else: ?>
    <p class="text-secondary">Seleccione un destino para gestionar sus requisitos.</p>
  <?php endif; ?>
  </div>
<?php include __DIR__ . '/_layout_end.php'; ?>
