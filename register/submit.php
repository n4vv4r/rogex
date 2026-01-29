<?php
ob_start();
require_once __DIR__ . "/../config.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

function flash($text, $type = 'error', $redirect = '/register') {
  $_SESSION['flash_msg'] = ['text' => $text, 'type' => $type];
  header("Location: " . $redirect);
  exit;
}

if (!isset($_POST['username'], $_POST['fullname'], $_POST['email'], $_POST['password'], $_POST['confirm_password'])) {
  flash("Faltan campos.");
}

$username = trim($_POST['username']);
$fullname = trim($_POST['fullname']);
$email    = trim($_POST['email']);
$pass     = $_POST['password'];
$confirm  = $_POST['confirm_password'];


if (!preg_match('/^[A-Za-z0-9._]+$/', $username)) {
  flash("El nombre de usuario solo puede contener letras, números, guiones bajos (_) o puntos (.)");
}

if (strpos($username, '..') !== false) {
  flash("El nombre de usuario no puede contener dos puntos seguidos (..)");
}

if (preg_match('/^[._]|[._]$/', $username)) {
  flash("El nombre de usuario no puede empezar ni terminar con '.' o '_'");
}

if (strlen($username) < 4) {
  flash("El nombre de usuario debe tener al menos 4 caracteres.");
}

//if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
//  flash("Email inválido.");
//}

if ($pass !== $confirm) flash("Las contraseñas no coinciden.");
if (strlen($pass) < 8) flash("La contraseña debe tener al menos 8 caracteres.");

if (empty($_POST['g-recaptcha-response'])) flash("⚠️ Captcha requerido.");
$recaptcha = $_POST['g-recaptcha-response'];
$verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . RECAPTCHA_SECRET . "&response=" . $recaptcha);
$keys = json_decode($verify, true);
if (empty($keys['success'])) flash("Captcha inválido.");

try {
  $hash = password_hash($pass, PASSWORD_BCRYPT);
  $token = bin2hex(random_bytes(16));
  $isPremium = PRELAUNCH_MODE ? 1 : 0;

  $stmt = $pdo->prepare("INSERT INTO users (username, fullname, email, password_hash, token, is_premium) VALUES (?,?,?,?,?,?)");
  $stmt->execute([$username, $fullname, $email, $hash, $token, $isPremium]);
} catch (PDOException $e) {
  if ($e->getCode() === '23000') flash("Usuario o email ya existen.");
  flash("Error al registrar.");
}

$verifyLink = BASE_URL . "/verify.php?token=" . urlencode($token);
$template = file_get_contents(__DIR__ . "/../mails/templates/verify.html");
$message = str_replace(['{{fullname}}', '{{verify_link}}'], [htmlspecialchars($fullname), $verifyLink], $template);

$subject = "Verifica tu cuenta en RogeX";
$headers  = "From: ".SITE_NAME." <".SITE_EMAIL.">\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
@mail($email, $subject, $message, $headers);

flash("Usuario creado correctamente. Revisa tu correo para confirmar.", 'success', '/');

ob_end_flush();