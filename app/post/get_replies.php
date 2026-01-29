<?php
require_once __DIR__ . "/../../config.php";

header("Content-Type: application/json; charset=utf-8");

$post_id = $_GET['id'] ?? null;
if (!$post_id || !is_numeric($post_id)) {
  echo json_encode(["success" => false, "error" => "Post inválido"]);
  exit;
}

$stmt = $pdo->prepare("
  SELECT r.id, r.user_id, r.content, r.created_at, u.username, COALESCE(u.profile_pic, 'default.png') AS profile_pic
  FROM feed_replies r
  JOIN users u ON u.id = r.user_id
  WHERE r.post_id = ?
  ORDER BY r.created_at ASC
");
$stmt->execute([$post_id]);
$replies = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($replies as &$r) {
  $r['username'] = htmlspecialchars($r['username']);
  $r['content'] = nl2br(htmlspecialchars($r['content']));
  $r['profile_pic'] = !empty($r['profile_pic']) && $r['profile_pic'] !== 'default.png'
    ? "/assets/uploads/users/" . htmlspecialchars($r['profile_pic'])
    : "/assets/default.png";
}

echo json_encode(["success" => true, "replies" => $replies]);
