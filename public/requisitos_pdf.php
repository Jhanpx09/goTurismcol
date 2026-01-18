<?php
require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/core/db.php';
start_session();

$pdo = db();
$destino_id = isset($_GET['destino']) ? (int)$_GET['destino'] : 0;

if (!$destino_id) {
  http_response_code(400);
  echo "Destino inválido.";
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM destino WHERE id_destino=? AND estado='activo'");
$stmt->execute([$destino_id]);
$destino = $stmt->fetch();

if (!$destino) {
  http_response_code(404);
  echo "Destino no encontrado.";
  exit;
}

$stmt = $pdo->prepare("
  SELECT titulo_requisito, descripcion_requisito, tipo_requisito, fuente_oficial, fecha_ultima_actualizacion
  FROM requisito_viaje
  WHERE id_destino=? AND estado='vigente'
  ORDER BY tipo_requisito, titulo_requisito
");
$stmt->execute([$destino_id]);
$requisitos = $stmt->fetchAll();

$html = '<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Requisitos</title>';
$html .= '<style>
  body{font-family: Arial, sans-serif; color:#0f172a; margin:40px;}
  h1{font-size:24px; margin-bottom:8px;}
  h2{font-size:16px; margin-top:24px;}
  .meta{color:#64748b; font-size:12px; margin-bottom:16px;}
  .item{margin-bottom:16px; padding-bottom:12px; border-bottom:1px solid #e2e8f0;}
  .item h3{margin:0 0 6px; font-size:14px;}
  .item p{margin:0 0 6px; font-size:12px; line-height:1.5;}
  .item small{color:#64748b;}
  </style></head><body>';
$html .= '<h1>Requisitos de viaje</h1>';
$html .= '<div class="meta">Destino: ' . htmlspecialchars($destino['pais'] . ($destino['ciudad'] ? ' - ' . $destino['ciudad'] : ''), ENT_QUOTES, 'UTF-8') . '</div>';

if (!$requisitos) {
  $html .= '<p>No hay requisitos vigentes registrados para este destino.</p>';
} else {
  foreach ($requisitos as $r) {
    $html .= '<div class="item">';
    $html .= '<h3>' . htmlspecialchars($r['titulo_requisito'], ENT_QUOTES, 'UTF-8') . '</h3>';
    $html .= '<p>' . nl2br(htmlspecialchars($r['descripcion_requisito'], ENT_QUOTES, 'UTF-8')) . '</p>';
    $html .= '<small>Actualizado: ' . htmlspecialchars($r['fecha_ultima_actualizacion'], ENT_QUOTES, 'UTF-8') . '</small>';
    if (!empty($r['fuente_oficial'])) {
      $html .= '<br><small>Fuente: ' . htmlspecialchars($r['fuente_oficial'], ENT_QUOTES, 'UTF-8') . '</small>';
    }
    $html .= '</div>';
  }
}
$html .= '</body></html>';

$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
  require_once $autoload;
}

if (class_exists('\\Dompdf\\Dompdf')) {
  $dompdf = new Dompdf\Dompdf();
  $dompdf->loadHtml($html);
  $dompdf->setPaper('A4', 'portrait');
  $dompdf->render();
  $dompdf->stream('requisitos_' . $destino_id . '.pdf', ['Attachment' => true]);
  exit;
}

header('Content-Type: text/html; charset=utf-8');
echo $html;
