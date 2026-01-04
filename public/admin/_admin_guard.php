<?php
require_once __DIR__ . '/../../app/core/helpers.php';
require_once __DIR__ . '/../../app/core/db.php';
require_once __DIR__ . '/../../app/core/auth.php';
require_admin();
$pdo = db();
$admin = current_user();
