<?php
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../includes/auth_check.php";

$stmt = $pdo->prepare("SELECT username, fullname, bio, profile_pic, banner, link_instagram, link_twitter, link_linkedin, link_github, link_website FROM users WHERE username = ?");
$stmt->execute([$_SESSION['user']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

foreach ($user as $k => $v) {
  if (is_null($v)) $user[$k] = '';
}
if (!$user) {
  header("Location: /login");
  exit;
}

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
  <title>Configuración de perfil — RogeX</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" href="apple-touch-icon.png">
  <link href="https://unpkg.com/cropperjs@1.5.13/dist/cropper.min.css" rel="stylesheet" />
  <script src="https://unpkg.com/cropperjs@1.5.13/dist/cropper.min.js"></script>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: #0f0f0f;
      color: #f0f0f0;
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      min-height: 100vh;
      padding: 3rem 1rem;
    }

    .settings-card {
      width: 100%;
      max-width: 700px;
      background: #121212;
      border: 1px solid #222;
      border-radius: 16px;
      padding: 2rem;
      box-shadow: 0 0 30px rgba(0,255,100,0.08);
      animation: fadeInUp 0.6s ease;
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .header-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
    }

    .header-row h2 {
      color: #00ff66;
      font-size: 1.7rem;
    }

    .back-link {
      color: #888;
      text-decoration: none;
      font-size: 0.95rem;
      font-weight: 500;
      transition: color 0.25s ease;
    }

    .back-link:hover { color: #00ff66; }

    .banner-container {
      position: relative;
      margin-bottom: 1.5rem;
    }

    #banner {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 12px;
      cursor: pointer;
      transition: filter 0.3s;
    }

    #banner:hover {
      filter: brightness(0.8);
    }

    .banner-container::after {
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
      pointer-events: none;
    }

    .banner-container:hover::after { opacity: 1; }

    .top-info {
      display: flex;
      align-items: center;
      gap: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .avatar-wrapper {
      position: relative;
      width: 110px;
      height: 110px;
    }

    #avatar {
      width: 100%;
      height: 100%;
      border-radius: 14px;
      object-fit: cover;
      border: 2px solid #00ff66;
      cursor: pointer;
      transition: filter 0.3s;
    }

    #avatar:hover {
      filter: brightness(0.8);
    }

    .avatar-wrapper::after {
      content: "✎";
      position: absolute;
      bottom: 6px;
      right: 6px;
      font-size: 0.9rem;
      background: rgba(0,0,0,0.7);
      color: #00ff66;
      padding: 0.3rem 0.4rem;
      border-radius: 6px;
      opacity: 0;
      transition: opacity 0.3s;
      pointer-events: none;
    }

    .avatar-wrapper:hover::after { opacity: 1; }

    .basic-fields {
      flex: 1;
    }

    label {
      display: block;
      margin-top: 0.7rem;
      color: #bbb;
      font-weight: 500;
      font-size: 0.95rem;
    }

    input[type="text"], textarea {
      width: 100%;
      margin-top: .4rem;
      background: #1a1a1a;
      border: 1px solid #333;
      border-radius: 10px;
      color: #eee;
      font-size: 1rem;
      padding: .7rem;
      transition: border .2s;
    }

    input:focus, textarea:focus {
      border-color: #00ff66;
      outline: none;
    }

    textarea {
      resize: vertical;
      min-height: 80px;
    }

    .btn {
      width: 100%;
      margin-top: 1.6rem;
      padding: 0.9rem;
      border-radius: 9999px;
      border: none;
      background: #00ff66;
      color: #0f0f0f;
      font-weight: 700;
      font-size: 1.05rem;
      cursor: pointer;
      transition: filter .25s ease, transform .25s ease;
    }

    .btn:hover { filter: brightness(1.1); transform: translateY(-2px); }

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

    .modal-buttons .btn {
      width: auto;
      padding: .7rem 1.4rem;
      background: transparent;
      border: 2px solid #00ff66;
      color: #00ff66;
    }

    .modal-buttons .btn:hover {
      background: rgba(0,255,100,0.1);
      box-shadow: 0 0 12px rgba(0,255,100,0.4);
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

    .notif.success {
      background: rgba(20,20,20,0.95);
      border: 1px solid #00ff66;
      color: #00ff66;
    }

    .notif.error {
      background: rgba(20,0,0,0.95);
      border: 1px solid #ff4444;
      color: #ff4444;
    }

    @keyframes fadeInOut {
      0% { opacity: 0; transform: translate(-50%, -10px); }
      10% { opacity: 1; transform: translate(-50%, 0); }
      80% { opacity: 1; }
      100% { opacity: 0; transform: translate(-50%, -10px); }
    }
  </style>
</head>
<body>

  <?php if ($flash): ?>
    <div class="notif <?= $flash['type'] ?>" id="notif"><?= htmlspecialchars($flash['text']) ?></div>
  <?php endif; ?>

  <div class="settings-card">
    <div class="header-row">
      <a href="/u/<?= htmlspecialchars($user['username']) ?>" class="back-link">← Volver a mi perfil</a>
      <h2>Editar perfil</h2>
    </div>

    <div class="banner-container">
      <img src="<?= $banner ?>" id="banner" alt="Banner actual">
    </div>

    <div class="top-info">
      <div class="avatar-wrapper">
        <img src="<?= $profile_pic ?>" id="avatar" alt="Foto actual">
      </div>
      <div class="basic-fields">
        <label>Nombre completo</label>
        <input type="text" name="fullname" form="profileForm" value="<?= htmlspecialchars($user['fullname']) ?>">

        <label>Nombre de usuario</label>
        <input type="text" name="username" form="profileForm" value="<?= htmlspecialchars($user['username']) ?>">
      </div>
    </div>

    <form id="profileForm" method="POST" action="/u/update_profile.php">
      <label>Biografía</label>
      <textarea name="bio"><?= htmlspecialchars($user['bio']) ?></textarea>

      <label>Instagram</label>
      <input type="text" name="link_instagram" value="<?= htmlspecialchars($user['link_instagram']) ?>">

      <label>Twitter / X</label>
      <input type="text" name="link_twitter" value="<?= htmlspecialchars($user['link_twitter']) ?>">

      <label>LinkedIn</label>
      <input type="text" name="link_linkedin" value="<?= htmlspecialchars($user['link_linkedin']) ?>">

      <label>GitHub</label>
      <input type="text" name="link_github" value="<?= htmlspecialchars($user['link_github']) ?>">

      <label>Sitio web</label>
      <input type="text" name="link_website" value="<?= htmlspecialchars($user['link_website']) ?>">

      <button type="submit" class="btn">Guardar cambios</button>
    </form>
  </div>

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
    const banner = document.getElementById('banner');
    const avatar = document.getElementById('avatar');
    const input = document.getElementById('imageInput');
    const modal = document.getElementById('cropperModal');
    const cropImg = document.getElementById('cropperImage');
    let cropper = null;
    let uploadType = null;

    banner.addEventListener('click', () => { uploadType = 'banner'; input.click(); });
    avatar.addEventListener('click', () => { uploadType = 'profile_pic'; input.click(); });

    input.addEventListener('change', e => {
      const file = e.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = ev => {
        cropImg.src = ev.target.result;
        modal.classList.add('active');
        if (cropper) cropper.destroy();
        cropper = new Cropper(cropImg, {
          aspectRatio: uploadType === 'banner' ? 3.2 : 1,
          viewMode: 1,
          background: false,
          autoCropArea: 1
        });
      };
      reader.readAsDataURL(file);
    });

    document.getElementById('cropCancel').addEventListener('click', () => {
      if (cropper) cropper.destroy();
      cropper = null;
      modal.classList.remove('active');
    });

    document.getElementById('cropAccept').addEventListener('click', () => {
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

    window.addEventListener('load', () => {
      const n = document.getElementById('notif');
      if (n) setTimeout(() => n.remove(), 5000);
    });
  </script>
</body>
</html>
