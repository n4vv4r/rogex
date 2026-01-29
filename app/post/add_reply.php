<?php
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../includes/auth_check.php";

header("Content-Type: application/json; charset=utf-8");

$raw = json_decode(file_get_contents("php://input"), true);
$post_id = $raw["post_id"] ?? null;
$content = trim($raw["content"] ?? "");
$username = $_SESSION["user"] ?? null;

if (!$username || !$post_id || !$content) {
  echo json_encode(["success" => false, "error" => "Datos inválidos."]);
  exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
$user_id = $stmt->fetchColumn();

if (!$user_id) {
  echo json_encode(["success" => false, "error" => "Usuario no encontrado."]);
  exit;
}

$stmt = $pdo->prepare("INSERT INTO feed_replies (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
$stmt->execute([$post_id, $user_id, $content]);

echo json_encode(["success" => true]);
