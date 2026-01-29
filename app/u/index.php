<?php
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../includes/auth_check_optional.php";


$username = $_GET['user'] ?? ($_SESSION['user'] ?? null);
if (!$username) {
  header("Location: /login");
  exit;
}

$stmt = $pdo->prepare("SELECT username, fullname, bio, profile_pic, banner, link_instagram, link_twitter, link_linkedin, link_github, link_website, is_premium, is_verified FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  http_response_code(404);
  echo "<h1>Usuario no encontrado</h1>";
  exit;
}

$is_own_profile = isset($_SESSION['user']) && $_SESSION['user'] === $user['username'];

$user_id_stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$user_id_stmt->execute([$username]);
$user_data = $user_id_stmt->fetch(PDO::FETCH_ASSOC);
$user_id = $user_data['id'] ?? null;

$my_id = null;
if (isset($_SESSION['user'])) {
  $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
  $stmt->execute([$_SESSION['user']]);
  $my_id = $stmt->fetchColumn();
}

$followers_count = $pdo->query("SELECT COUNT(*) FROM follows WHERE followed_id = $user_id")->fetchColumn();
$following_count = $pdo->query("SELECT COUNT(*) FROM follows WHERE follower_id = $user_id")->fetchColumn();
$quiz_count = $pdo->query("SELECT COUNT(*) FROM quizzes WHERE user_id = $user_id")->fetchColumn();


$profile_pic = $user['profile_pic'] && $user['profile_pic'] !== 'default.png'
  ? "/assets/uploads/users/" . htmlspecialchars($user['profile_pic'])
  : "/assets/default.png";

$banner = $user['banner'] && $user['banner'] !== 'default-banner.jpg'
  ? "/assets/uploads/users/" . htmlspecialchars($user['banner'])
  : "/assets/default-banner.jpg";

$flash = $_SESSION['flash_msg'] ?? null;
unset($_SESSION['flash_msg']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>@<?= htmlspecialchars($user['username']) ?> — RogeX</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" href="apple-touch-icon.png">
  <link href="https://unpkg.com/cropperjs@1.5.13/dist/cropper.min.css" rel="stylesheet" />
  <script src="https://unpkg.com/cropperjs@1.5.13/dist/cropper.min.js"></script>

  <script src="https://kit.fontawesome.com/a2d04d4e4e.js" crossorigin="anonymous"></script>

  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      background: #0f0f0f;
      color: #f0f0f0;
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      overflow-x: hidden;
    }

    .profile-container {
  width: 100%;
  max-width: 1000px; 
  margin: 2rem auto;
  background: #121212;
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 0 25px rgba(0,255,100,0.15);
  position: relative;
  border: 1px solid #1a1a1a;
  padding-bottom: 1rem;
}


    .banner {
      width: 100%;
      height: 220px;
      background-size: cover;
      background-position: center;
      position: relative;
      transition: filter 0.3s ease, opacity 0.3s ease;
      <?= $is_own_profile ? 'cursor: pointer;' : '' ?>
    }

    <?php if ($is_own_profile): ?>
    .banner::after {
      content: "✎";
      position: absolute;
      top: 10px;
      right: 10px;
      font-size: 1.2rem;
      background: rgba(0,0,0,0.6);
      color: #00ff66;
      padding: .3rem .5rem;
      border-radius: 6px;
      opacity: 0;
      transition: opacity 0.3s;
    }
    .banner:hover::after { opacity: 1; }
    <?php endif; ?>

    .profile-info {
      display: flex;
      align-items: flex-start;
      gap: 1.5rem;
      padding: 1.5rem;
      position: relative;
    }

    .avatar {
      width: 120px;
      height: 120px;
      border-radius: 18px;
      border: 3px solid #0f0f0f;
      overflow: hidden;
      box-shadow: 0 0 16px rgba(0,255,100,0.25);
      background: #101010;
      transition: transform 0.2s ease;
      margin-top: -60px;
      position: relative;
      <?= $is_own_profile ? 'cursor: pointer;' : '' ?>
    }

    <?php if ($is_own_profile): ?>
    .avatar:hover { transform: scale(1.03); }
    .avatar::after {
      content: "✎";
      position: absolute;
      bottom: 6px;
      right: 6px;
      background: rgba(0,0,0,0.7);
      border-radius: 6px;
      color: #00ff66;
      padding: 0.3rem;
      font-size: 0.9rem;
      opacity: 0;
      transition: opacity 0.3s;
    }
    .avatar:hover::after { opacity: 1; }
    <?php endif; ?>

    .avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .details { flex: 1; }

    .details h2 {
      margin: 0;
      font-size: 1.6rem;
      color: #00ff66;
      font-weight: 700;
    }

    .details .username {
      color: #bbb;
      margin-top: 0.2rem;
    }

    .details .bio {
      color: #ccc;
      margin-top: 0.8rem;
      line-height: 1.5;
    }

    .settings-btn {
      position: absolute;
      top: 16px;
      right: 16px;
      background: transparent;
      border: 2px solid #00ff66;
      color: #00ff66;
      border-radius: 9999px;
      padding: .4rem .9rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.25s ease;
    }

    .settings-btn:hover {
      background: rgba(0,255,100,0.1);
      box-shadow: 0 0 12px rgba(0,255,100,0.4);
      transform: translateY(-2px);
    }
    
    .back-btn {
  position: absolute;
  top: 16px;
  left: 16px;
  background: transparent;
  border: 2px solid #00ff66;
  color: #00ff66;
  border-radius: 9999px;
  padding: .4rem 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.25s ease;
  z-index: 10;
}

.back-btn:hover {
  background: rgba(0,255,100,0.1);
  box-shadow: 0 0 12px rgba(0,255,100,0.4);
  transform: translateY(-2px);
}


.social-links {
  margin-top: 1rem;
  display: flex;
  gap: 0.8rem;
  align-items: center;
  flex-wrap: wrap;
}

.social-links a {
  display: inline-block;
  transition: transform 0.3s ease, filter 0.3s ease;
}

.social-icon {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  filter: drop-shadow(0 0 5px #00ff66) brightness(1);
  transition: transform 0.3s ease, filter 0.3s ease;
}

.social-icon:hover {
  transform: scale(1.15);
  filter: drop-shadow(0 0 12px #00ff66) brightness(1.2);
}



    .modal {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.8);
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .modal.active { display: flex; }

    .modal-content {
      background: #121212;
      padding: 1.5rem;
      border-radius: 12px;
      text-align: center;
      box-shadow: 0 0 25px rgba(0,255,100,0.2);
    }

    .modal-content img {
      max-width: 400px;
      max-height: 400px;
    }

    .modal-buttons {
      margin-top: 1rem;
      display: flex;
      gap: 1rem;
      justify-content: center;
    }

    .btn {
      padding: .7rem 1.3rem;
      border-radius: 9999px;
      border: 2px solid #00ff66;
      color: #00ff66;
      background: transparent;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.25s ease;
    }

    .btn:hover {
      background: rgba(0,255,100,0.1);
      box-shadow: 0 0 12px rgba(0,255,100,0.4);
      transform: translateY(-2px);
    }

    .notif {
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      padding: 1rem 1.4rem;
      border-radius: 10px;
      font-weight: 600;
      box-shadow: 0 0 20px rgba(0,255,100,0.3);
      opacity: 0;
      animation: fadeInOut 4.5s ease forwards;
      z-index: 2000;
    }
    .notif.success { background: rgba(20,20,20,0.95); border: 1px solid #00ff66; color: #00ff66; }
    .notif.error { background: rgba(20,0,0,0.95); border: 1px solid #ff4444; color: #ff4444; }

    @keyframes fadeInOut {
      0% { opacity: 0; transform: translate(-50%, -10px); }
      10% { opacity: 1; transform: translate(-50%, 0); }
      80% { opacity: 1; }
      100% { opacity: 0; transform: translate(-50%, -10px); }
    }
    
    .stats {
  display: flex;
  gap: 1.5rem;
  margin-top: 1rem;
  color: #ccc;
  font-size: 0.95rem;
}

.stats .stat {
  cursor: pointer;
  transition: color 0.25s ease;
}

.stats .stat:hover {
  color: #00ff66;
}

.follow-btn {
  position: absolute;
  top: 16px;
  right: 70px;
  background: transparent;
  border: 2px solid #00ff66;
  color: #00ff66;
  border-radius: 9999px;
  padding: .4rem 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.25s ease;
}

.follow-btn:hover {
  background: rgba(0,255,100,0.1);
  box-shadow: 0 0 12px rgba(0,255,100,0.4);
  transform: translateY(-2px);
}

.modal-follow {
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.8);
  justify-content: center;
  align-items: center;
  z-index: 3000;
}
.modal-follow.active { display: flex; }

.modal-follow .content {
  background: #121212;
  padding: 1.5rem;
  border-radius: 12px;
  width: 350px;
  max-height: 500px;
  overflow-y: auto;
  box-shadow: 0 0 25px rgba(0,255,100,0.2);
}

.modal-follow .user-item {
  display: flex;
  align-items: center;
  gap: .8rem;
  padding: .4rem 0;
  border-bottom: 1px solid #222;
}

.modal-follow .user-item img {
  width: 38px; height: 38px; border-radius: 50%;
}

.modal-follow .user-item a {
  color: #f0f0f0; text-decoration: none; font-weight: 500;
}
.modal-follow .user-item a:hover { color: #00ff66; }

.btn-close-follow {
  display: block;
  margin: 1rem auto 0;
  border: 2px solid #00ff66;
  background: transparent;
  color: #00ff66;
  border-radius: 8px;
  padding: .4rem 1rem;
  cursor: pointer;
  transition: all 0.25s ease;
}

.btn-close-follow:hover { background: rgba(0,255,100,0.1); }

.toast {
  position: fixed;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(15, 15, 15, 0.95);
  border: 1px solid #00ff66;
  color: #00ff66;
  padding: .8rem 1.2rem;
  border-radius: 10px;
  font-weight: 600;
  box-shadow: 0 0 15px rgba(0,255,100,0.2);
  opacity: 0;
  animation: toastFade 3.5s ease forwards;
  z-index: 5000;
}
@keyframes toastFade {
  0% { opacity: 0; transform: translate(-50%, 20px); }
  10% { opacity: 1; transform: translate(-50%, 0); }
  80% { opacity: 1; }
  100% { opacity: 0; transform: translate(-50%, 20px); }
}
.details h2 img {
  filter: drop-shadow(0 0 4px rgba(0,255,100,0.3));
  transition: transform 0.25s ease, filter 0.25s ease;
}

.details h2 img:hover {
  transform: scale(1.15);
  filter: drop-shadow(0 0 8px rgba(0,255,100,0.6));
}
.my-quizzes .quiz-list a:hover {
  text-decoration: underline;
}
.my-quizzes h3 {
  letter-spacing: 0.02em;
}
.layout {
  display: grid;
  grid-template-columns: 90px minmax(0, 1fr);
  gap: 2rem;
  max-width: 1400px;
  margin: 2rem 0;      
  padding: 0;          
}

.sidebar{
  display:flex;flex-direction:column;align-items:center;
  position:sticky;top:0;height:100vh;
  background:#0c0c0c;border-right:1px solid #1a1a1a;
  padding:1rem 0; z-index:5;
}
.sidebar img.logo{
  margin-bottom:1.2rem; width:58px; border-radius:12px;
  filter:drop-shadow(0 0 10px #00ff6699);
}
.sidebar a{
  width:60px;height:60px;margin:10px 0;
  display:flex;align-items:center;justify-content:center;
  border-radius:16px;background:#121212;border:1px solid #1f1f1f;
  transition:all .25s ease;
}
.sidebar a img{
  width:28px;height:28px;
  filter:brightness(0) saturate(100%) invert(74%) sepia(92%) saturate(446%) hue-rotate(73deg) brightness(100%) contrast(95%);
  transition:transform .2s ease;
}
.sidebar a:hover,.sidebar a.active{ background:#0f2617;border-color:#00ff66;box-shadow:0 0 15px #00ff6633; }
.sidebar a:hover img{ transform:scale(1.1); }

.main-profile{ display:flex; justify-content:center; }

.profile-container{ position:relative; z-index:1; }

.modal {
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.8);
  backdrop-filter: blur(8px);
  justify-content: center;
  align-items: center;
  z-index: 9999;
}
.modal.active { display: flex; }

.modal-content {
  background: #121212;
  border: 2px solid #00ff66;
  border-radius: 18px;
  padding: 2.5rem;
  text-align: center;
  width: 460px;
  max-width: 95%;
  box-shadow: 0 0 25px rgba(0,255,100,0.2);
}
.drop-zone {
  border: 2px dashed #00ff66;
  border-radius: 14px;
  padding: 2rem;
  color: #00ff66;
  cursor: pointer;
  background: #0f0f0f;
  transition: all 0.25s ease;
}
.drop-zone:hover { background: rgba(0,255,100,0.05); }
.close-btn {
  background: transparent;
  border: 2px solid #00ff66;
  color: #00ff66;
  border-radius: 9999px;
  padding: 0.5rem 1.4rem;
  margin-top: 1.4rem;
  cursor: pointer;
  font-weight: bold;
  transition: all 0.3s ease;
}
.close-btn:hover { background: rgba(0,255,100,0.1); }
.drag-overlay {
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  border: 5px solid #00ff66;
  box-shadow: 0 0 30px rgba(0,255,100,0.5);
  z-index: 2000;
  pointer-events: none;
}
.drag-overlay.active { display: block; }


#createModal {
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.85);
  backdrop-filter: blur(5px);
  justify-content: center;
  align-items: center;
  z-index: 4000;
}

#createModal.active {
  display: flex;
  animation: fadeInModal 0.3s ease forwards;
}

@keyframes fadeInModal {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

.modal-content {
  background: #121212;
  border-radius: 16px;
  padding: 2rem;
  text-align: center;
  box-shadow: 0 0 25px rgba(0,255,100,0.15);
  width: 90%;
  max-width: 420px;
  color: #f0f0f0;
  position: relative;
}

.modal-content h2 {
  color: #00ff66;
  margin-bottom: 1rem;
  letter-spacing: 0.5px;
}

.drop-zone {
  border: 2px dashed #00ff66;
  border-radius: 12px;
  padding: 2rem;
  cursor: pointer;
  background: #181818;
  transition: all 0.25s ease;
}

.drop-zone:hover {
  background: #1b1b1b;
  box-shadow: 0 0 15px rgba(0,255,100,0.15);
}

.drop-zone p {
  color: #ccc;
  margin: 0;
  font-size: 0.95rem;
}

.drag-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,255,100,0.05);
  border: 2px dashed #00ff66;
  display: none;
  z-index: 5000;
}

.drag-overlay.active {
  display: block;
  animation: pulseGlow 1.2s ease-in-out infinite;
}

@keyframes pulseGlow {
  0%, 100% { background: rgba(0,255,100,0.05); }
  50% { background: rgba(0,255,100,0.1); }
}

.upload-status {
  display: none;
  margin-top: 1.2rem;
  text-align: left;
}

.file-name {
  font-size: 0.9rem;
  color: #ccc;
  margin-bottom: 0.3rem;
}

.progress-container {
  height: 10px;
  background: #1a1a1a;
  border-radius: 10px;
  overflow: hidden;
  position: relative;
}

.progress-bar {
  height: 100%;
  width: 0%;
  background: linear-gradient(90deg, #00ff66, #00cc55);
  box-shadow: 0 0 12px rgba(0,255,100,0.4);
  border-radius: 10px;
  transition: width 0.2s ease-in-out;
}

.progress-bar::after {
  content: "";
  position: absolute;
  top: 0; left: 0;
  height: 100%;
  width: 100%;
  background: linear-gradient(
    120deg,
    transparent 0%,
    rgba(255,255,255,0.2) 50%,
    transparent 100%
  );
  animation: shimmer 1.5s infinite;
  opacity: 0.3;
}

@keyframes shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

.upload-percent {
  text-align: right;
  margin-top: 0.4rem;
  color: #00ff66;
  font-weight: 600;
  font-size: 0.9rem;
}

.close-modal {
  background: transparent;
  border: 2px solid #00ff66;
  border-radius: 9999px;
  color: #00ff66;
  padding: 0.5rem 1rem;
  margin-top: 1.5rem;
  cursor: pointer;
  transition: all 0.25s ease;
}

.close-modal:hover {
  background: rgba(0,255,100,0.1);
  box-shadow: 0 0 10px rgba(0,255,100,0.3);
}
.profile-tabs {
  display: flex;
  justify-content: center;
  gap: 2rem;
  border-bottom: 1px solid #1a1a1a;
  margin-top: 2rem;
  margin-bottom: 1rem;
}

.tab {
  position: relative;
  background: transparent;
  color: #ccc;
  border: none;
  font-weight: 600;
  padding: 1rem 0;
  cursor: pointer;
  font-size: 1rem;
  transition: color 0.25s ease;
}

.tab:hover {
  color: #00ff66;
}

.tab::after {
  content: "";
  position: absolute;
  bottom: 0;
  left: 0;
  width: 0%;
  height: 3px;
  background: #00ff66;
  border-radius: 10px;
  transition: width 0.25s ease;
}

.tab.active {
  color: #00ff66;
}

.tab.active::after {
  width: 100%;
}

.user-feed {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 1.2rem;
}

.user-post {
  background: #181818;
  padding: 1rem;
  border-radius: 12px;
  border: 1px solid #222;
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.user-post:hover {
  transform: translateY(-2px);
  box-shadow: 0 0 12px rgba(0,255,100,0.1);
}

  </style>
</head>
<body>

 <?php if ($flash): ?>
  <div class="notif <?= $flash['type'] ?>" id="notif"><?= htmlspecialchars($flash['text']) ?></div>
<?php endif; ?>

<div class="layout">
  <aside class="sidebar">
    <img src="/apple-touch-icon.png" alt="RogeX" class="logo">
    <a href="../"><img src="../home.png" alt="Inicio"></a>
    <a href="#" id="createQuiz"><img src="../quiz.png" alt="Crear Quiz"></a>
    <a href="/explore/"><img src="../explore.png" alt="Explorar"></a>
    <a href="/u/<?= urlencode($_SESSION['user'] ?? $username) ?>" class="active"><img src="../user.png" alt="Perfil"></a>
    <a href="/ajustes/"><img src="../settings.png" alt="Ajustes"></a>
    <a href="<?= BASE_URL ?>/logout.php"><img src="../logout.png" alt="Salir"></a>
  </aside>

  <main class="main-profile">
  <div class="profile-container">
<button class="back-btn" onclick="window.location.href='https://app.rogex.net'"><img src="back.png" width="18px"></button>

    <div class="banner" id="banner"
         style="background-image:url('<?= $banner ?>')"></div>

    <div class="profile-info">
      <div class="avatar" id="avatar">
        <img src="<?= $profile_pic ?>" alt="Foto de perfil">
      </div>

   <div class="details">
<h2>
  <?= htmlspecialchars($user['fullname'] ?: $user['username']) ?>

  <?php
  $is_premium = isset($user['is_premium']) ? (bool)$user['is_premium'] : false;
  $is_verified = isset($user['is_verified']) ? (bool)$user['is_verified'] : false;

  if ($is_verified): ?>
    <img src="verified.png" alt="Verificado" title="Usuario verificado"
         style="width:18px; height:18px; margin-left:6px; vertical-align:middle;">
  <?php endif; ?>

  <?php if ($is_premium): ?>
    <img src="premium.png" alt="Premium" title="Usuario premium"
         style="width:18px; height:18px; margin-left:4px; vertical-align:middle;">
  <?php endif; ?>
</h2>
  <p class="username">@<?= htmlspecialchars($user['username']) ?></p>
  <p class="bio"><?= htmlspecialchars($user['bio'] ?: 'Sin biografía todavía.') ?></p>
<div class="stats">
  <span class="stat" id="showFollowers"><strong><?= $followers_count ?></strong> seguidores</span>
  <span class="stat" id="showFollowing"><strong><?= $following_count ?></strong> seguidos</span>
  <span class="stat"><strong><?= $quiz_count ?></strong> quizzes</span>
</div>

  <?php
    $links = [
      'instagram' => $user['link_instagram'],
      'twitter'   => $user['link_twitter'],
      'linkedin'  => $user['link_linkedin'],
      'github'    => $user['link_github'],
      'website'   => $user['link_website']
    ];
  ?>

  <div class="social-links">
    <?php foreach ($links as $name => $url): ?>
      <?php if (!empty($url)): ?>
        <a href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener noreferrer">
          <img src="/assets/icons/<?= $name ?>.png" alt="<?= ucfirst($name) ?>" class="social-icon">
        </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
<div class="my-quizzes" style="margin-top:2rem;">
  <h3 style="color:#00ff66; font-size:1.2rem; margin-bottom:0.8rem;">Mis quizzes</h3>
  <div class="quiz-list" style="display:flex; flex-direction:column; gap:0.6rem;">
    <?php
    $stmt = $pdo->prepare("SELECT id, title, visibility, created_at FROM quizzes WHERE user_id = (SELECT id FROM users WHERE username = ?) ORDER BY created_at DESC");
    $stmt->execute([$username]);
    $quizzes = $stmt->fetchAll();

    if (!$quizzes) {
      echo "<p style='color:#888;'>Aún no has creado quizzes.</p>";
    } else {
      foreach ($quizzes as $q) {
        $title = htmlspecialchars($q['title']);
        $visibility = htmlspecialchars($q['visibility']);
        $date = date('d/m/Y', strtotime($q['created_at']));
        echo "
          <div style='background:#181818; padding:12px 16px; border-radius:10px; border:1px solid #222; display:flex; justify-content:space-between; align-items:center;'>
            <div>
              <b style='color:#00ff66;'>$title</b>
              <span style='color:#aaa; font-size:0.85rem;'> — $visibility</span><br>
              <small style='color:#666;'>$date</small>
            </div>
            <a href='/quiz/view.php?id={$q['id']}' style='color:#00ff66; font-weight:600; text-decoration:none;'>Ver</a>
          </div>
        ";
      }
    }
    ?>
  </div>
</div>
<div class="profile-tabs">
  <button id="tabPosts" class="tab active">Preguntas</button>
  <button id="tabReplies" class="tab">Respuestas</button>
</div>

<div id="userFeed" class="user-feed">
  <p style="color:#888;">Cargando...</p>
</div>


<?php if (!$is_own_profile && $my_id): ?>
  <?php
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ? AND followed_id = ?");
    $stmt->execute([$my_id, $user_id]);
    $is_following = $stmt->fetchColumn() > 0;
  ?>
  <button class="follow-btn" id="followBtn"
          data-following="<?= $is_following ? '1' : '0' ?>"
          data-userid="<?= $user_id ?>">
    <?= $is_following ? 'Siguiendo' : 'Seguir' ?>
  </button>
<?php endif; ?>



      <?php if ($is_own_profile): ?>
        <button class="settings-btn" onclick="window.location.href='/u/settings/'"><img src="setting.png" width="18px"></button>
      <?php endif; ?>
    </div>
  </div>
</main>
</div>
  <?php if ($is_own_profile): ?>
  <div class="modal" id="cropperModal">
    <div class="modal-content">
      <img id="cropperImage" src="">
      <div class="modal-buttons">
        <button class="btn" id="cropAccept">Guardar</button>
        <button class="btn" id="cropCancel">Cancelar</button>
      </div>
    </div>
  </div>

  <input type="file" id="imageInput" accept="image/*" hidden>

  <script>
  const avatar = document.getElementById('avatar');
  const banner = document.getElementById('banner');
  const input = document.getElementById('imageInput');
  const modal = document.getElementById('cropperModal');
  const cropImg = document.getElementById('cropperImage');
  let cropper = null;
  let uploadType = null;

  function openCropper(file, type) {
    const reader = new FileReader();
    reader.onload = e => {
      cropImg.src = e.target.result;
      modal.classList.add('active');
      uploadType = type;
      cropper = new Cropper(cropImg, {
        aspectRatio: type === 'banner' ? 3.2 : 1,
        viewMode: 1,
        background: false,
        autoCropArea: 1
      });
    };
    reader.readAsDataURL(file);
  }

  avatar.addEventListener('click', () => { input.click(); uploadType = 'profile_pic'; });
  banner.addEventListener('click', () => { input.click(); uploadType = 'banner'; });

  input.addEventListener('change', e => {
    const file = e.target.files[0];
    if (!file) return;
    openCropper(file, uploadType || 'profile_pic');
  });

  document.getElementById('cropCancel').addEventListener('click', () => {
    cropper.destroy();
    cropper = null;
    modal.classList.remove('active');
  });

  document.getElementById('cropAccept').addEventListener('click', async () => {
    if (!cropper) return;
    cropper.getCroppedCanvas().toBlob(async blob => {
      const form = new FormData();
      form.append('image', blob);
      form.append('type', uploadType);
      const res = await fetch('/u/upload_image.php', { method: 'POST', body: form });
      const data = await res.json();
      if (data.success) location.reload();
      else alert(data.error);
    }, 'image/jpeg', 0.9);
  });
  </script>
  <?php endif; ?>

  <script>
  window.addEventListener('load', () => {
    const n = document.getElementById('notif');
    if (n) setTimeout(() => n.remove(), 5000);
  });
  </script>
<div class="modal-follow" id="followModal">
  <div class="content">
    <h3 id="followModalTitle" style="color:#00ff66;text-align:center;margin-bottom:1rem;"></h3>
    <div id="followList"></div>
    <button class="btn-close-follow" id="closeFollowModal">Cerrar</button>
  </div>
</div>
<div class="modal" id="createModal">
  <div class="modal-content" id="modalContent">
    <img src="/apple-touch-icon.png" alt="RogeX Logo" width="60px">
    <h2>Selecciona o arrastra tu PDF para empezar<br></h2>
    <div class="drop-zone" id="dropZone">
      <i class="fa-solid fa-file-pdf" style="font-size:2rem;margin-bottom:8px;"></i>
      <div id="dropText">Arrastra tu PDF aquí o haz clic</div>
      <input type="file" id="pdfInput" accept="application/pdf" hidden>
      <div class="upload-status" id="uploadStatus" style="display:none;">
        <div id="fileName" class="file-name"></div>
        <div class="progress-bar"><div class="progress" id="progress"></div></div>
        <div id="uploadPercent" class="percent-text">0%</div>
      </div>
    </div>
    <button class="close-btn" id="closeModal">Cancelar</button>
  </div>
</div>
<div class="drag-overlay" id="dragOverlay"></div>

<script src="../assets/js/feed.js" defer></script>

<script>
const followBtn = document.getElementById('followBtn');
if (followBtn) {
  followBtn.addEventListener('click', async () => {
    const form = new FormData();
    form.append('user_id', followBtn.dataset.userid);
    const res = await fetch('/u/follow_action.php', { method: 'POST', body: form });
    const data = await res.json();
    if (data.status === 'followed') {
      followBtn.textContent = 'Siguiendo';
      showToast('Seguiste a @<?= htmlspecialchars($user['username']) ?>');
    } else if (data.status === 'unfollowed') {
      followBtn.textContent = 'Seguir';
      showToast('Dejaste de seguir a @<?= htmlspecialchars($user['username']) ?>');
    }
  });
}

function showToast(msg) {
  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.textContent = msg;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3500);
}

const followModal = document.getElementById('followModal');
const followModalTitle = document.getElementById('followModalTitle');
const followList = document.getElementById('followList');
const closeFollowModal = document.getElementById('closeFollowModal');

document.getElementById('showFollowers')?.addEventListener('click', () => openFollowModal('followers'));
document.getElementById('showFollowing')?.addEventListener('click', () => openFollowModal('following'));
closeFollowModal.addEventListener('click', () => followModal.classList.remove('active'));

function openFollowModal(type) {
  followModal.classList.add('active');
  followModalTitle.textContent = type === 'followers' ? 'Seguidores' : 'Seguidos';
  followList.innerHTML = '<p style="text-align:center;color:#777;">Cargando...</p>';
  fetch(`/u/get_${type}.php?user_id=<?= $user_id ?>`)
    .then(res => res.text())
    .then(html => { followList.innerHTML = html; });
}
</script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const createQuizBtn = document.getElementById("createQuiz");
  const modal = document.getElementById("createModal");
  const closeBtn = document.getElementById("closeModal");

  if (createQuizBtn && modal && closeBtn) {
    createQuizBtn.addEventListener("click", (e) => {
      e.preventDefault();
      modal.classList.add("active");
    });

    closeBtn.addEventListener("click", () => modal.classList.remove("active"));

    modal.addEventListener("click", (e) => {
      if (e.target === modal) modal.classList.remove("active");
    });
  }
});
</script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("createModal");
  const closeBtn = document.getElementById("closeModal");
  const dropZone = document.getElementById("dropZone");
  const pdfInput = document.getElementById("pdfInput");
  const dragOverlay = document.getElementById("dragOverlay");
  const uploadStatus = document.getElementById("uploadStatus");
  const fileNameEl = document.getElementById("fileName");
  const progressBar = document.getElementById("progress");
  const uploadPercent = document.getElementById("uploadPercent");

  document.getElementById("createQuiz")?.addEventListener("click", (e) => {
    e.preventDefault();
    modal.classList.add("active");
  });

  closeBtn?.addEventListener("click", () => modal.classList.remove("active"));
  modal?.addEventListener("click", (e) => {
    if (e.target === modal) modal.classList.remove("active");
  });

  dropZone?.addEventListener("click", () => pdfInput.click());
  pdfInput?.addEventListener("change", () => {
    if (pdfInput.files.length) handleFile(pdfInput.files[0]);
  });

  window.addEventListener("dragover", (e) => {
    e.preventDefault();
    dragOverlay.classList.add("active");
  });

  window.addEventListener("dragleave", (e) => {
    e.preventDefault();
    dragOverlay.classList.remove("active");
  });

  window.addEventListener("drop", (e) => {
    e.preventDefault();
    dragOverlay.classList.remove("active");
    if (e.dataTransfer.files.length) handleFile(e.dataTransfer.files[0]);
  });

  function handleFile(file) {
    if (file.type !== "application/pdf") {
      alert("Por favor, selecciona un archivo PDF válido.");
      return;
    }
    uploadFile(file);
  }

  function uploadFile(file) {
    const formData = new FormData();
    formData.append("pdf", file);
    uploadStatus.style.display = "block";
    fileNameEl.textContent = file.name;

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "/upload_pdf.php", true);

    xhr.upload.addEventListener("progress", (e) => {
      if (e.lengthComputable) {
        const percent = Math.floor((e.loaded / e.total) * 100);
        progressBar.style.width = percent + "%";
        uploadPercent.textContent = percent + "%";
      }
    });

    xhr.onload = () => {
      if (xhr.status === 200) {
        try {
          const res = JSON.parse(xhr.responseText);
          if (res.success) {
            const filename = encodeURIComponent(res.filename);
            window.location.href = `/create/?file=${filename}`;
          } else {
            alert(res.error);
          }
        } catch (err) {
          alert("Error inesperado en la respuesta del servidor.");
        }
      } else {
        alert("Error al subir el archivo.");
      }
    };

    xhr.send(formData);
  }
});
</script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const feed = document.getElementById("userFeed");
  const tabPosts = document.getElementById("tabPosts");
  const tabReplies = document.getElementById("tabReplies");
  const username = "<?= htmlspecialchars($user['username']) ?>";

  const params = new URLSearchParams(window.location.search);
  const defaultTab = params.get("tab") === "respuestas" ? "replies" : "posts";

  async function loadFeed(type = "posts") {
    feed.innerHTML = "<p style='color:#888;'>Cargando...</p>";
    try {
      const res = await fetch(`/u/user_${type}.php?user=${encodeURIComponent(username)}`);
      const data = await res.json();
      feed.innerHTML = "";
      if (!data.success || !data[type] || data[type].length === 0) {
        feed.innerHTML = "<p style='color:#777;'>No hay contenido todavía.</p>";
        return;
      }

      (data[type]).forEach(item => {
        const el = document.createElement("div");
        el.className = "user-post";

        if (type === "posts") {
          const images = item.image && item.image.length
            ? `<div style='display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:6px;margin-top:8px;'>
                 ${item.image.map(img => `<img src='${img}' style='width:100%;border-radius:8px;'>`).join("")}
               </div>`
            : "";

          el.innerHTML = `
            <div style="display:flex;align-items:center;gap:8px;">
              <img src="${item.profile_pic}" style="width:40px;height:40px;border-radius:8px;">
              <b>@${item.username}</b>
              <span style="color:#666;font-size:0.85rem;">${new Date(item.created_at).toLocaleString("es-ES")}</span>
            </div>
            <div style="margin-top:8px;white-space:pre-line;">${item.content}</div>
            ${images}
            <div style="margin-top:6px;color:#777;font-size:0.9rem;">❤️ ${item.like_count} 💬 ${item.reply_count}</div>
          `;
        } else {
          el.innerHTML = `
            <div style="display:flex;align-items:center;gap:8px;">
              <img src="${item.profile_pic}" style="width:40px;height:40px;border-radius:8px;">
              <b>@${item.username}</b>
              <span style="color:#666;font-size:0.85rem;">${new Date(item.created_at).toLocaleString("es-ES")}</span>
            </div>
            <div style="margin-top:8px;white-space:pre-line;">${item.content}</div>
            <a href="/post/${item.post_id}" style="color:#00ff66;font-size:0.9rem;">Ver publicación original →</a>
          `;
        }

        feed.appendChild(el);
      });
    } catch (err) {
      console.error(err);
      feed.innerHTML = "<p style='color:#ff6666;'>Error al cargar contenido.</p>";
    }
  }

  function activateTab(type) {
    document.querySelectorAll(".tab").forEach(t => t.classList.remove("active"));
    if (type === "posts") {
      tabPosts.classList.add("active");
      history.replaceState(null, "", `?user=${username}&tab=preguntas`);
      loadFeed("posts");
    } else {
      tabReplies.classList.add("active");
      history.replaceState(null, "", `?user=${username}&tab=respuestas`);
      loadFeed("replies");
    }
  }

  tabPosts.addEventListener("click", () => activateTab("posts"));
  tabReplies.addEventListener("click", () => activateTab("replies"));

  activateTab(defaultTab);
});
</script>

</body>
</html>
