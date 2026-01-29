<?php
if (!defined('BASE_URL')) {
  $cfg = __DIR__ . '/../../config.php';
  if (file_exists($cfg)) {
    require_once $cfg;
  }
}

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!isset($_SESSION['user'])) {
  $loginUrl = defined('BASE_URL') ? BASE_URL . '/login' : '/login';
  header("Location: " . $loginUrl);
  exit;
}
?>