<?php
require_once __DIR__ . "/../../config.php";

$user_id = $_GET['user_id'] ?? null;
if (!$user_id) exit('Error.');

$stmt = $pdo->prepare("
  SELECT u.username, u.fullname, u.profile_pic
  FROM follows f
  JOIN users u ON f.followed_id = u.id
  WHERE f.follower_id = ?
");
$stmt->execute([$user_id]);
$following = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$following) {
  echo "<p style='text-align:center;color:#777;'>No sigue a nadie.</p>";
  exit;
}

foreach ($following as $f) {
  $pic = $f['profile_pic'] && $f['profile_pic'] !== 'default.png'
    ? '/assets/uploads/users/' . htmlspecialchars($f['profile_pic'])
    : '/assets/default.png';
  echo "<div class='user-item'>
          <img src='$pic'>
          <a href='/u/" . htmlspecialchars($f['username']) . "'>" . htmlspecialchars($f['fullname'] ?: $f['username']) . "</a>
        </div>";
}
