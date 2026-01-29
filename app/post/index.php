<?php
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../includes/auth_check.php";

$post_id = $_GET['id'] ?? null;
if (!$post_id || !is_numeric($post_id)) {
  header("Location: /../");
  exit;
}

$stmt = $pdo->prepare("
  SELECT 
    p.id,
    p.user_id,
    p.content,
    p.image,
    p.created_at,
    u.username,
    COALESCE(u.profile_pic, 'default.png') AS profile_pic,
    (SELECT COUNT(*) FROM feed_likes WHERE post_id = p.id) AS like_count,
    (SELECT COUNT(*) FROM feed_replies WHERE post_id = p.id) AS reply_count
  FROM feed_posts p
  JOIN users u ON u.id = p.user_id
  WHERE p.id = ?
  LIMIT 1
");
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
  echo "<h2 style='color:white;text-align:center;margin-top:3rem;'>publicación no encontrada.</h2>";
  exit;
}

$stmt = $pdo->prepare("SELECT id, username, profile_pic FROM users WHERE username = ?");
$stmt->execute([$_SESSION['user']]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

$userId = (int)$userData['id'];
$username = htmlspecialchars($userData['username']);
$profile_pic = !empty($userData['profile_pic']) && $userData['profile_pic'] !== 'default.png'
  ? "/../assets/uploads/users/" . htmlspecialchars($userData['profile_pic'])
  : "/../assets/default.png";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Post de @<?= htmlspecialchars($post['username']) ?> — RogeX</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="/apple-touch-icon.png">
  <script src="https://kit.fontawesome.com/a2d04d4e4e.js" crossorigin="anonymous"></script>
  <style>
    body {
      margin: 0;
      font-family: 'Inter', system-ui, sans-serif;
      background: #0a0a0a;
      color: #f0f0f0;
      display: flex;
      justify-content: center;
      min-height: 100vh;
    }
    .layout {
      display: grid;
      grid-template-columns: 90px 1fr 320px;
      max-width: 1400px;
      width: 100%;
      margin-top: 20px;
      gap: 1rem;
    }
    .sidebar {
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 1rem 0;
      position: sticky;
      top: 0;
      height: 100vh;
      background: #0c0c0c;
      border-right: 1px solid #1a1a1a;
    }
    .sidebar a {
      width: 60px; height: 60px;
      display: flex; align-items: center; justify-content: center;
      margin: 10px 0;
      border-radius: 16px;
      background: #121212;
      border: 1px solid #1f1f1f;
      transition: all .25s ease;
    }
    .sidebar a:hover {
      background: #0f2617;
      border-color: #00ff66;
      box-shadow: 0 0 15px #00ff6633;
    }
    .feed {
      padding: 1rem;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .post {
      background: #101010;
      border: 1px solid #1a1a1a;
      border-radius: 18px;
      padding: 1rem;
      width: 100%;
      max-width: 600px;
      margin-bottom: 1rem;
    }
    .post-header {
      display: flex; align-items: center; gap: 10px;
    }
    .post-header img {
      width: 40px; height: 40px;
      border-radius: 50%; border: 1px solid #00ff66;
    }
    .username {
      color: #00ff66; text-decoration: none; font-weight: 600;
    }
    .post-content { margin: 0.8rem 0; white-space: pre-line; }
    .post-image { width: 100%; border-radius: 12px; margin-top: 0.5rem; }
    .reply-section {
      margin-top: 1.5rem;
      width: 100%;
      max-width: 600px;
    }
    .reply-form textarea {
      width: 100%; background: #121212;
      border: 1px solid #1a1a1a; border-radius: 10px;
      color: #fff; padding: 0.7rem;
      resize: none; min-height: 60px;
    }
    .reply-form button {
      background: #00ff66; color: #0a0a0a;
      border: none; border-radius: 12px;
      padding: .6rem 1.2rem; margin-top: .6rem;
      font-weight: 700; cursor: pointer;
      transition: 0.2s;
    }
    .reply-form button:hover {
      background: #22ff77;
    }
    .reply {
      background: #111; border: 1px solid #1a1a1a;
      border-radius: 14px; padding: .8rem;
      margin-top: .8rem;
    }
    .reply img {
      width: 30px; height: 30px; border-radius: 50%;
      border: 1px solid #00ff66;
    }
    .reply .username { font-weight: 600; color: #00ff66; }
  </style>
</head>
<body>
<div class="layout">
  <aside class="sidebar">
    <a href="/../"><img src="../apple-touch-icon.png" width="40"></a>
    <a href="/../explore/"><img src="../explore.png" width="40"></i></a>
    <a href="/../u/<?= $username ?>"><img src="../user.png" width="40"></a>
  </aside>

  <main class="feed">
    <article class="post">
      <div class="post-header">
        <img src="<?= htmlspecialchars($post['profile_pic']) ?>" alt="@<?= htmlspecialchars($post['username']) ?>">
        <a class="username" href="/u/<?= htmlspecialchars($post['username']) ?>">@<?= htmlspecialchars($post['username']) ?></a>
      </div>
      <div class="post-content"><?= nl2br(htmlspecialchars($post['content'])) ?></div>
      <?php if (!empty($post['image'])): ?>
        <img src="<?= htmlspecialchars($post['image']) ?>" class="post-image">
      <?php endif; ?>
      <div class="post-time"><?= htmlspecialchars($post['created_at']) ?></div>
    </article>

    <section class="reply-section">
      <form class="reply-form" id="replyForm">
        <textarea id="replyContent" placeholder="Responde a esta pregunta... (esto todavia sigue en desarrollo, esperad errores, es normal)" required></textarea>
        <button type="submit">Responder</button>
      </form>

      <div id="repliesContainer">
        <p style="color:#777;">Cargando respuestas...</p>
      </div>
    </section>
  </main>

  <aside class="rightbar"></aside>
</div>

<script>
const POST_ID = <?= (int)$post_id ?>;
</script>
<script src="/../assets/js/replies.js" defer></script>
</body>
</html>
