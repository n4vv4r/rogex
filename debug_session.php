<?php
require_once __DIR__ . "/config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Debug de Sesión — RogeX</title>
  <style>
    body {
      background: #0b0b0b;
      color: #00ff66;
      font-family: monospace;
      padding: 30px;
    }
    h1 {
      font-size: 1.8em;
      margin-bottom: 1rem;
    }
    pre {
      background: #111;
      border: 1px solid #222;
      padding: 1rem;
      border-radius: 10px;
      overflow-x: auto;
      color: #8effb2;
    }
    .cookie {
      background: #141414;
      border: 1px solid #333;
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 1rem;
    }
    a {
      color: #00ffaa;
      text-decoration: none;
    }
    .warn { color: #ff5555; }
  </style>
</head>
<body>
  <h1>Depuración de Sesión PHP</h1>
  <p>Verifica si la sesión y cookies se comparten correctamente entre <b>rogex.net</b> y <b>app.rogex.net</b>.</p>
HTML;

$session_id = session_id();
if ($session_id) {
    echo "<div class='cookie'>ok! <b>PHPSESSID:</b> {$session_id}</div>";
} else {
    echo "<div class='cookie warn'>No hay sesión iniciada (session_id vacío)</div>";
}

if (!empty($_SESSION)) {
    echo "<h2>Contenido actual de \$_SESSION</h2>";
    echo "<pre>" . htmlspecialchars(print_r($_SESSION, true)) . "</pre>";
} else {
    echo "<p class='warn'>warn! \$_SESSION está vacío. Puede que la cookie no se esté guardando correctamente o se esté perdiendo el dominio.</p>";
}

echo "<h2>Cabeceras del Servidor</h2>";
$headers = [
    'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? '—',
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? '—',
    'HTTPS' => $_SERVER['HTTPS'] ?? '—',
];
echo "<pre>" . htmlspecialchars(print_r($headers, true)) . "</pre>";

echo "<h2>Cookies Recibidas</h2>";
echo "<pre>" . htmlspecialchars(print_r($_COOKIE, true)) . "</pre>";

echo "<hr><p><a href='/logout.php'>Cerrar sesión</a> — <a href='/'>Volver al inicio</a></p>";

echo "</body></html>";
