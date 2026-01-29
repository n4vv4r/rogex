<?php
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../includes/auth_check.php";

header("Content-Type: application/json");

$username = $_SESSION['user'] ?? null;
if (!$username) {
  echo json_encode(['success' => false, 'error' => 'Sesión no iniciada.']);
  exit;
}

$type = $_POST['type'] ?? 'profile_pic'; 

if (!in_array($type, ['profile_pic', 'banner'])) {
  echo json_encode(['success' => false, 'error' => 'Tipo de imagen no válido.']);
  exit;
}

$upload_dir = __DIR__ . "/../assets/uploads/users/";
if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);

$file = $_FILES[$type] ?? $_FILES['profile_pic'] ?? $_FILES['image'] ?? null;
if (!$file || $file['error']) {
  echo json_encode(['success' => false, 'error' => 'No se recibió el archivo correctamente.']);
  exit;
}

$allowed_types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
if (!array_key_exists($file['type'], $allowed_types)) {
  echo json_encode(['success' => false, 'error' => 'Formato no permitido (usa JPG, PNG o WEBP).']);
  exit;
}

if ($file['size'] > 8 * 1024 * 1024) {
  echo json_encode(['success' => false, 'error' => 'La imagen supera los 8 MB.']);
  exit;
}

$ext = $allowed_types[$file['type']];
$new_name = uniqid($type . "_") . "." . $ext;
$target = $upload_dir . $new_name;

if (!move_uploaded_file($file['tmp_name'], $target)) {
  echo json_encode(['success' => false, 'error' => 'No se pudo guardar la imagen.']);
  exit;
}

$col = $type;
$stmt = $pdo->prepare("SELECT $col FROM users WHERE username = ?");
$stmt->execute([$username]);
$old_img = $stmt->fetchColumn();

if ($old_img && $old_img !== 'default.png') {
  $old_path = $upload_dir . $old_img;
  if (file_exists($old_path)) unlink($old_path);
}

$stmt = $pdo->prepare("UPDATE users SET $col = ? WHERE username = ?");
$stmt->execute([$new_name, $username]);

echo json_encode([
  'success' => true,
  'url' => "/assets/uploads/users/$new_name"
]);
