<?php
function db() : PDO {
  static $pdo = null;
  if ($pdo) return $pdo;

  $config = require __DIR__ . '/config.php';
  $db = $config['db'];
  $dsn = sprintf("mysql:host=%s;dbname=%s;charset=%s", $db['host'], $db['name'], $db['charset']);

  $options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ];

  try {
    $pdo = new PDO($dsn, $db['user'], $db['pass'], $options);
    return $pdo;
  } catch (PDOException $e) {
    http_response_code(500);
    echo "<h1>Error de conexión</h1>";
    echo "<p>No se pudo conectar a la base de datos. Verifique app/core/config.php.</p>";
    echo "<pre style='white-space:pre-wrap'>" . htmlspecialchars($e->getMessage()) . "</pre>";
    exit;
  }
}
