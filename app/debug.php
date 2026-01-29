<?php

$cfg = __DIR__ . '/../config.php';
if (!file_exists($cfg)) {
  header('Content-Type: text/plain; charset=utf-8', true, 500);
  echo "Missing config.php at: $cfg\n";
  exit;
}
require_once $cfg;

header('Content-Type: text/plain; charset=utf-8');

echo "--- RogeX debug page ---\n";
echo "PHP version: " . PHP_VERSION . "\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "error_log: " . ini_get('error_log') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
echo "\n_GET:\n" . print_r($_GET, true) . "\n";
echo "\nSESSION status: " . session_status() . "\n";
echo "\n_SESSION:\n" . print_r($_SESSION, true) . "\n";

try {
  if (!isset($pdo)) throw new Exception('No $pdo variable available');
  $stmt = $pdo->query("SELECT COUNT(*) AS c FROM users");
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  echo "\nDB OK: users count = " . ($row['c'] ?? 'n/a') . "\n";
} catch (Exception $e) {
  echo "\nDB ERROR: " . $e->getMessage() . "\n";
}

$log = __DIR__ . '/../php-error.log';
if (file_exists($log)) {
  echo "\n--- php-error.log (last 2000 chars) ---\n";
  $contents = file_get_contents($log);
  echo substr($contents, -2000);
} else {
  echo "\nNo php-error.log found at: $log\n";
}

echo "\n--- end ---\n";
