<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/includes/auth_check.php"; 

header("Content-Type: application/json");

if (empty($_POST['post_id'])) {
  echo json_encode(["success" => false, "error" => "ID de publicación no proporcionado."]);
  exit;
}

$post_id = (int) $_POST['post_id'];

$current_username = $_SESSION['user'] ?? null;
if (!$current_username) {
  echo json_encode(["success" => false, "error" => "Usuario no autenticado."]);
  exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$current_username]);
$current_user_id = $stmt->fetchColumn();

if (!$current_user_id) {
  echo json_encode(["success" => false, "error" => "Usuario no encontrado."]);
  exit;
}

$stmt = $pdo->prepare("SELECT id FROM feed_posts WHERE id = ? AND user_id = ?");
$stmt->execute([$post_id, $current_user_id]);
$post = $stmt->fetch();

if (!$post) {
  echo json_encode(["success" => false, "error" => "No tienes permiso para eliminar este post."]);
  exit;
}

$stmt = $pdo->prepare("DELETE FROM feed_posts WHERE id = ? AND user_id = ?");
$stmt->execute([$post_id, $current_user_id]);

if ($stmt->rowCount() > 0) {
  echo json_encode(["success" => true]);
} else {
  echo json_encode(["success" => false, "error" => "Error al eliminar la publicación."]);
}
?>
