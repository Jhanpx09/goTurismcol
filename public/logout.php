<?php
require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/core/auth.php';
logout_user();
redirect('index.php');
