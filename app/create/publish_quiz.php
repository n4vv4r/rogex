<?php
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../includes/auth_check.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
  exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$filename = $input['filename'] ?? '';
$title = trim($input['title'] ?? '');

if (empty($filename) || empty($title)) {
  echo json_encode(['success' => false, 'error' => 'Faltan datos necesarios.']);
  exit;
}

if (empty($_SESSION['user'])) {
  echo json_encode(['success' => false, 'error' => 'No has iniciado sesión.']);
  exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$_SESSION['user']]);
$user_id = $stmt->fetchColumn();

if (!$user_id) {
  echo json_encode(['success' => false, 'error' => 'Usuario no encontrado.']);
  exit;
}

$stmt = $pdo->prepare("SELECT id FROM quizzes WHERE filename = ?");
$stmt->execute([$filename]);
$existing_id = $stmt->fetchColumn();

if (!$existing_id) {
  echo json_encode(['success' => false, 'error' => 'El quiz no existe o no fue generado aún.']);
  exit;
}

try {
  $stmt = $pdo->prepare("UPDATE quizzes SET title = ?, visibility = 'public' WHERE id = ?");
  $stmt->execute([$title, $existing_id]);

  echo json_encode([
    'success' => true,
    'quiz_id' => $existing_id,
    'message' => 'Quiz publicado correctamente.'
  ]);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'error' => 'Error al guardar en base de datos: ' . $e->getMessage()]);
}
