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
      fr.id AS reply_id,
      fr.post_id,
      fr.user_id,
      fr.content,
      fr.created_at,
      u.username,
      COALESCE(u.profile_pic, 'default.png') AS profile_pic,
      fp.content AS parent_content,
      fp.user_id AS parent_user_id,
      pu.username AS parent_username,
      (SELECT COUNT(*) FROM feed_likes WHERE post_id = fr.post_id) AS like_count
    FROM feed_replies fr
    JOIN users u ON u.id = fr.user_id
    JOIN feed_posts fp ON fp.id = fr.post_id
    JOIN users pu ON pu.id = fp.user_id
    WHERE fr.user_id = ?
    ORDER BY fr.created_at DESC
  ");
  $stmt->execute([$user_id]);
  $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($replies as &$r) {
    $r["profile_pic"] = "/assets/uploads/users/" . htmlspecialchars($r["profile_pic"]);
    $r["content"] = nl2br(htmlspecialchars($r["content"] ?? ""));
    $r["parent_content"] = nl2br(htmlspecialchars($r["parent_content"] ?? ""));
    $r["created_at"] = date("d M, H:i", strtotime($r["created_at"]));
  }

  echo json_encode([
    "success" => true,
    "user" => $user,
    "count" => count($replies),
    "replies" => $replies
  ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
  echo json_encode(["success" => false, "error" => "Error SQL: " . $e->getMessage()]);
}
