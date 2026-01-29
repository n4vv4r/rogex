<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config.php";

header("Content-Type: application/json; charset=utf-8");


session_start();
$user = $_SESSION['user'] ?? null;
$current_user_id = null;

if ($user) {
  $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
  $stmt->execute([$user]);
  $current_user_id = $stmt->fetchColumn();
}

$sql = "
SELECT 
  p.id,
  p.user_id,
  p.content,
  p.image,
  p.created_at,
  p.visibility,
  u.username,
  COALESCE(u.profile_pic, 'default.png') AS profile_pic,
  u.is_verified,
  u.is_premium,
  (SELECT COUNT(*) FROM feed_likes WHERE post_id = p.id) AS like_count,
  (SELECT COUNT(*) FROM feed_replies WHERE post_id = p.id) AS reply_count,
  " . ($current_user_id
    ? "EXISTS(SELECT 1 FROM feed_likes WHERE user_id = $current_user_id AND post_id = p.id) AS liked"
    : "0 AS liked") . "
FROM feed_posts p
JOIN users u ON u.id = p.user_id
ORDER BY p.created_at DESC
LIMIT 100
";

$stmt = $pdo->query($sql);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// procesamiento de los datos
foreach ($posts as &$post) {
  if (!empty($post['profile_pic']) && $post['profile_pic'] !== 'default.png') {
    $post['profile_pic'] = "/assets/uploads/users/" . htmlspecialchars($post['profile_pic']);
  } else {
    $post['profile_pic'] = "/assets/default.png";
  }

  $post['username'] = htmlspecialchars($post['username']);
  $post['content'] = htmlspecialchars($post['content']);

  // convierte a booleanos
  $post['liked'] = (bool) $post['liked'];
  $post['is_verified'] = (bool) $post['is_verified'];
  $post['is_premium'] = (bool) $post['is_premium'];

  $timestamp = strtotime($post['created_at']);
  $post['created_at'] = date("d M, H:i", $timestamp);

  $post['username_html'] = '<div class="username-badges">'
    . '<a href="/u/' . $post['username'] . '" class="username">@' . $post['username'] . '</a>'
    . ($post['is_verified'] ? ' <img src="/u/verified.png" class="badge verified" alt="Verificado">' : '')
    . ($post['is_premium'] ? ' <img src="/u/premium.png" class="badge premium" alt="Premium">' : '')
    . '</div>';
}

echo json_encode([
  "success" => true,
  "posts" => $posts
], JSON_UNESCAPED_UNICODE);
