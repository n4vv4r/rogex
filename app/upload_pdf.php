<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/includes/auth_check.php";

header('Content-Type: application/json');

if (!isset($_FILES['pdf'])) {
  echo json_encode(['success' => false, 'error' => 'No se recibió ningún archivo.']);
  exit;
}

$file = $_FILES['pdf'];

if ($file['error'] !== UPLOAD_ERR_OK) {
  echo json_encode(['success' => false, 'error' => 'Error al subir el archivo.']);
  exit;
}

$allowed_types = ['application/pdf'];
if (!in_array(mime_content_type($file['tmp_name']), $allowed_types)) {
  echo json_encode(['success' => false, 'error' => 'Solo se permiten archivos PDF.']);
  exit;
}

$upload_dir = __DIR__ . "/uploads/pdfs/";
if (!is_dir($upload_dir)) {
  mkdir($upload_dir, 0775, true);
}

$filename = basename($file['name']);
$filename = preg_replace('/[^A-Za-z0-9_\-.]/', '_', $filename);
$unique_name = uniqid('pdf_', true) . "_" . $filename;
$destination = $upload_dir . $unique_name;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
  echo json_encode(['success' => false, 'error' => 'Error al guardar el archivo en el servidor.']);
  exit;
}


// (TO-DO: hacer tabla "quizzes" más adelante)
try {
  $stmt = $pdo->prepare("INSERT INTO uploaded_pdfs (user_id, filename, uploaded_at) VALUES ((SELECT id FROM users WHERE username = ?), ?, NOW())");
  $stmt->execute([$_SESSION['user'], $unique_name]);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'error' => 'Error en base de datos: ' . $e->getMessage()]);
  exit;
}

echo json_encode(['success' => true, 'message' => 'PDF subido correctamente.', 'filename' => $unique_name]);
