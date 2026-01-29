<?php
require_once __DIR__ . "/../../config.php";

header("Content-Type: application/json; charset=utf-8");

if (session_status() === PHP_SESSION_NONE) session_start();

$user = $_GET["user"] ?? ($_SESSION["user"] ?? null);
if (!$user) {
  echo json_encode(["success" => false, "error" => "Usuario no especificado."]);
  exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$user]);
$user_id = $stmt->fetchColumn();

if (!$user_id) {
  echo json_encode(["success" => false, "error" => "Usuario no encontrado."]);
  exit;
}

try {
  $stmt = $pdo->prepare("
    SELECT 
      fp.id,
      fp.user_id,
      fp.content,
      fp.image,
      fp.created_at,
      fp.visibility,
      u.username,
      COALESCE(u.profile_pic, 'default.png') AS profile_pic,
      (SELECT COUNT(*) FROM feed_likes WHERE post_id = fp.id) AS like_count,
      (SELECT COUNT(*) FROM feed_replies WHERE post_id = fp.id) AS reply_count
    FROM feed_posts fp
    JOIN users u ON u.id = fp.user_id
    WHERE fp.user_id = ?
    ORDER BY fp.created_at DESC
  ");
  $stmt->execute([$user_id]);
  $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($posts as &$p) {
    $p["profile_pic"] = "/assets/uploads/users/" . htmlspecialchars($p["profile_pic"]);
    $p["content"] = nl2br(htmlspecialchars($p["content"] ?? ""));
    $p["image"] = $p["image"] ? json_decode($p["image"], true) : [];
    $p["created_at"] = date("d M, H:i", strtotime($p["created_at"]));
  }

  echo json_encode([
    "success" => true,
    "user" => $user,
    "count" => count($posts),
    "posts" => $posts
  ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
  echo json_encode(["success" => false, "error" => "Error SQL: " . $e->getMessage()]);
}
