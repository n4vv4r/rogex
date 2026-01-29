<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$cookieParams = session_get_cookie_params();

try {
    if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '.rogex.net',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'None'
        ]);
    } else {
        session_set_cookie_params(0, '/', '.rogex.net', true, true);
    }
} catch (Throwable $e) {
    echo "<h2 style='color:red;'>Error configurando cookies: " . htmlspecialchars($e->getMessage()) . "</h2>";
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Depuración de Sesión PHP</title>
<style>
  body { background:#0f0f0f; color:#0f0; font-family: monospace; padding:2rem; }
  pre { background:#111; padding:1rem; border-radius:10px; overflow-x:auto; }
  a { color:#4ade80; }
</style>
</head>
<body>
<h1>Depuración de Sesión PHP</h1>
<p>Verifica si la sesión y cookies se comparten correctamente entre <b>rogex.net</b> y <b>app.rogex.net</b>.</p>

<?php
echo "<p><b> PHPSESSID:</b> " . session_id() . "</p>";
echo "<h2> Contenido actual de \$_SESSION</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2> Cabeceras del Servidor</h2><pre>";
print_r($_SERVER);
echo "</pre>";

echo "<h2> Cookies Recibidas</h2><pre>";
print_r($_COOKIE);
echo "</pre>";
?>
<p><a href="/logout.php">Cerrar sesión</a> — <a href="/">Volver al inicio</a></p>
</body>
</html>
