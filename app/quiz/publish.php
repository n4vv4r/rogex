<?php
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../includes/auth_check.php";

header("Content-Type: application/json");

if (empty($_POST['file'])) {
  echo json_encode(['success' => false, 'error' => 'Falta archivo PDF']); exit;
}

$file = basename($_POST['file']);

try {
  $stmt = $pdo->prepare("SELECT id, user_id FROM quizzes WHERE filename = ? AND user_id = (SELECT id FROM users WHERE username = ?)");
  $stmt->execute([$file, $_SESSION['user']]);
  $quiz = $stmt->fetch();

  if (!$quiz) {
    echo json_encode(['success' => false, 'error' => 'Quiz no encontrado o sin permisos']); exit;
  }

  $pdo->prepare("UPDATE quizzes SET visibility = 'public' WHERE id = ?")->execute([$quiz['id']]);
  echo json_encode(['success' => true, 'quiz_id' => $quiz['id']]);
} catch (Throwable $e) {
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
