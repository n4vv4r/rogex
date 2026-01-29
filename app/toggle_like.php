<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/includes/auth_check.php";

header("Content-Type: application/json");

$user = $_SESSION['user'] ?? null;
if (!$user) {
  echo json_encode(["success" => false, "error" => "No autenticado"]);
  exit;
}

// obtener ID de usuario
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$user]);
$user_id = $stmt->fetchColumn();

if (!$user_id) {
  echo json_encode(["success" => false, "error" => "Usuario no encontrado"]);
  exit;
}

// obtener post_id
$data = json_decode(file_get_contents("php://input"), true);
$post_id = (int)($data["post_id"] ?? 0);

if ($post_id <= 0) {
  echo json_encode(["success" => false, "error" => "ID de post inválido"]);
  exit;
}

// comprobar si ya le dio like
$stmt = $pdo->prepare("SELECT id FROM feed_likes WHERE user_id = ? AND post_id = ?");
$stmt->execute([$user_id, $post_id]);
$like = $stmt->fetch();

if ($like) {
  // quitar like
  $pdo->prepare("DELETE FROM feed_likes WHERE user_id = ? AND post_id = ?")->execute([$user_id, $post_id]);
  $liked = false;
} else {
  // añadir like
  $pdo->prepare("INSERT INTO feed_likes (user_id, post_id) VALUES (?, ?)")->execute([$user_id, $post_id]);
  $liked = true;
}

// contar likes
$stmt = $pdo->prepare("SELECT COUNT(*) FROM feed_likes WHERE post_id = ?");
$stmt->execute([$post_id]);
$count = (int)$stmt->fetchColumn();

echo json_encode([
  "success" => true,
  "liked" => $liked,
  "like_count" => $count
]);
?>
