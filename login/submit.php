<?php
require_once __DIR__ . "/../config.php";

function flash($text, $type = 'error', $redirect = '/login') {
  $_SESSION['flash_msg'] = ['text' => $text, 'type' => $type];
  header("Location: " . $redirect);
  exit;
}

if (empty($_POST['username']) || empty($_POST['password'])) {
  flash("Completa todos los campos.");
}

$user = trim($_POST['username']);
$pass = $_POST['password'];

// Buscar usuario
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
$stmt->execute([$user, $user]);
$row = $stmt->fetch();

if (!$row || !password_verify($pass, $row['password_hash'])) {
  flash("Usuario o contraseña incorrectos.");
}

if (!$row['verified']) {
  flash("Tu cuenta no está verificada. Revisa tu correo.");
}

$_SESSION['user'] = $row['username'];
flash("Sesión iniciada correctamente. ¡Bienvenido a RogeX!", 'success', APP_URL . '/');
