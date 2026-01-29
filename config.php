<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);
ini_set('display_errors', 0);
ini_set('error_log', sys_get_temp_dir() . '/rogex-php.log');


if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
  session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '.rogex.net',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
  ]);
} else {
  session_set_cookie_params(0, '/', '.rogex.net', true, true);
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$DB_HOST = "localhost";
$DB_NAME = "u62fedelasjons80_rogex";
$DB_USER = "u62fedelasjons80_roger";
$DB_PASS = "!Vivi!2006@";

try {
  $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (Exception $e) {
  http_response_code(500);
  exit("Error de conexión a la base de datos.");
}

define('RECAPTCHA_SITE_KEY', 'XXX');
define('RECAPTCHA_SECRET',   'XXX');

define('SITE_EMAIL', 'admin@rogex.net');
define('SITE_NAME',  'RogeX');
define('BASE_URL',   'https://www.rogex.net');

define('APP_URL',    'https://app.rogex.net');



$env_path = __DIR__ . '/.env';
if (file_exists($env_path)) {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

define('OPENAI_API_KEY', $_ENV['OPENAI_API_KEY'] ?? '');
/* ===== Modo pre-lanzamiento =====
   Mientras esté en TRUE, cualquier registro queda marcado como premium
*/
define('PRELAUNCH_MODE', true);
