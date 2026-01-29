<?php
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../includes/auth_check.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: /u/change_pic.php");
  exit;
}

$username = $_SESSION['user'] ?? null;
if (!$username) {
  header("Location: ../../login");
  exit;
}

if (empty($_FILES['profile_pic']['tmp_name'])) {
  $_SESSION['flash_msg'] = ['type' => 'error', 'text' => 'No se seleccionó ninguna imagen.'];
  header("Location: /u/change_pic.php");
  exit;
}

$file = $_FILES['profile_pic'];
$allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
$max_size = 2 * 1024 * 1024; // 2MB

if (!in_array($file['type'], $allowed_types)) {
  $_SESSION['flash_msg'] = ['type' => 'error', 'text' => 'Formato no válido. Usa PNG, JPG o WEBP.'];
  header("Location: /u/change_pic.php");
  exit;
}

if ($file['size'] > $max_size) {
  $_SESSION['flash_msg'] = ['type' => 'error', 'text' => 'La imagen supera los 2 MB.'];
  header("Location: /u/change_pic.php");
  exit;
}

$upload_dir = __DIR__ . "/../assets/uploads/users/";
if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$new_name = uniqid("pf_") . "." . strtolower($ext);
$target = $upload_dir . $new_name;

if (!move_uploaded_file($file['tmp_name'], $target)) {
  $_SESSION['flash_msg'] = ['type' => 'error', 'text' => 'Error al subir la imagen.'];
  header("Location: /u/change_pic.php");
  exit;
}

$stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE username = ?");
$stmt->execute([$username]);
$old_pic = $stmt->fetchColumn();

if ($old_pic && $old_pic !== 'default.png') {
  $old_path = $upload_dir . $old_pic;
  if (file_exists($old_path)) unlink($old_path);
}

$stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE username = ?");
$stmt->execute([$new_name, $username]);

$_SESSION['flash_msg'] = ['type' => 'success', 'text' => 'Foto de perfil actualizada correctamente.'];
header("Location: /u/$username");
exit;
