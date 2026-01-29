<?php


if (session_status() === PHP_SESSION_NONE) {
  $is_localhost = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']);

  if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
    session_set_cookie_params([
      'lifetime' => 0,
      'path' => '/',
      'domain' => $is_localhost ? '' : '.rogex.net',
      'secure' => !$is_localhost,
      'httponly' => true,
      'samesite' => 'Lax'
    ]);
  } else {
    session_set_cookie_params(0, '/', $is_localhost ? '' : '.rogex.net', !$is_localhost, true);
  }

  session_start();
}

if (!isset($_SESSION['last_regen'])) {
  $_SESSION['last_regen'] = time();
} elseif (time() - $_SESSION['last_regen'] > 1800) { // cada 30 min
  session_regenerate_id(true);
  $_SESSION['last_regen'] = time();
}
