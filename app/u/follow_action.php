<?php
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../includes/auth_check.php";

$follower_username = $_SESSION['user'];
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$follower_username]);
$follower_id = $stmt->fetchColumn();

$followed_id = $_POST['user_id'] ?? null;

if (!$followed_id || $follower_id == $followed_id) {
  echo json_encode(['error' => 'Invalid request']);
  exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ? AND followed_id = ?");
$stmt->execute([$follower_id, $followed_id]);
$is_following = $stmt->fetchColumn() > 0;

if ($is_following) {
  $pdo->prepare("DELETE FROM follows WHERE follower_id = ? AND followed_id = ?")->execute([$follower_id, $followed_id]);
  echo json_encode(['status' => 'unfollowed']);
} else {
  $pdo->prepare("INSERT INTO follows (follower_id, followed_id) VALUES (?, ?)")->execute([$follower_id, $followed_id]);
  echo json_encode(['status' => 'followed']);
}
