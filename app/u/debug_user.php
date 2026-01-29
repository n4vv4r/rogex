<?php
// DEBUG TEMPORAL PARA /u
$cfg = __DIR__ . '/../../config.php';
if (!file_exists($cfg)) {
  header('Content-Type: text/plain; charset=utf-8', true, 500);
  echo "Missing config.php at: $cfg\n";
  exit;
}
require_once $cfg;
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: text/plain; charset=utf-8');

echo "--- debug_user.php ---\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
echo "_GET: " . print_r($_GET, true) . "\n";
echo "_SERVER PATH_INFO: " . ($_SERVER['PATH_INFO'] ?? 'n/a') . "\n";
echo "SESSION user: " . ($_SESSION['user'] ?? 'n/a') . "\n";

$requested_user = $_GET['user'] ?? null;
if ($requested_user === null) {
  echo "No user provided in GET[user].\n";
} else {
  echo "Requested user: $requested_user\n";
  try {
    $stmt = $pdo->prepare("SELECT username, profile_pic, is_premium, verified FROM users WHERE username = ?");
    $stmt->execute([$requested_user]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($profile) {
      $display = $profile['fullname'] ?? $profile['username'];
      $profile['fullname'] = $display;
    }
    echo "DB query result: " . print_r($profile, true) . "\n";
  } catch (Exception $e) {
    echo "DB ERROR: " . $e->getMessage() . "\n";
  }
}

echo "--- end ---\n";

?>