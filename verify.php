<?php
require_once __DIR__ . "/config.php";

if (empty($_GET['token'])) {
  echo "Token inválido."; exit;
}

$token = $_GET['token'];
$stmt = $pdo->prepare("SELECT id, username FROM users WHERE token=?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
  echo "Token inválido o ya usado."; exit;
}

$pdo->prepare("UPDATE users SET verified=1, token=NULL WHERE id=?")->execute([$user['id']]);

$_SESSION['flash_msg'] = [
  'text' => "Tu cuenta ha sido verificada correctamente. Ya puedes iniciar sesión.",
  'type' => 'success'
];

header("Location: /login");
exit;
