<?php
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../includes/auth_check.php";

$username = $_SESSION['user'] ?? null;
if (!$username) { header("Location: /login"); exit; }

$stmt = $pdo->prepare("
  SELECT username, fullname, bio,
         link_twitter, link_instagram, link_linkedin, link_github, link_website,
         profile_pic, banner
  FROM users WHERE username = ?
");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { header("Location: /login"); exit; }

$profile_pic = ($user['profile_pic'] === 'default.png')
  ? "/assets/default.png"
  : "/assets/uploads/users/" . htmlspecialchars($user['profile_pic']);

$banner = (empty($user['banner']) || $user['banner'] === 'default_banner.png')
  ? "/assets/default_banner.png"
  : "/assets/uploads/users/" . htmlspecialchars($user['banner']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ajustes de perfil — RogeX</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" href="apple-touch-icon.png">
  <link href="https://unpkg.com/cropperjs/dist/cropper.min.css" rel="stylesheet">
  <script src="https://unpkg.com/cropperjs/dist/cropper.min.js"></script>

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    html, body {
      background: #0f0f0f;
      color: #f0f0f0;
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(12px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInOut {
      0% { opacity: 0; transform: translate(-50%, -10px); }
      10% { opacity: 1; transform: translate(-50%, 0); }
      80% { opacity: 1; transform: translate(-50%, 0); }
      100% { opacity: 0; transform: translate(-50%, -10px); }
    }

    .wrap {
      max-width: 820px;
      margin: 64px auto 80px;
      padding: 0 16px;
      animation: fadeInUp .7s ease forwards;
    }

    .card {
      background: rgba(18,18,18,0.95);
      border: 1px solid #222;
      border-radius: 16px;
      box-shadow: 0 10px 40px rgba(0,0,0,.45);
      overflow: hidden;
    }

    .header {
      position: relative;
      width: 100%;
      height: 220px;
      background: #121212;
      cursor: pointer;
    }
    .banner {
      width: 100%; height: 100%;
      background-size: cover;
      background-position: center;
      transition: opacity .25s ease, filter .25s ease;
    }
    .banner:hover { opacity: .9; filter: brightness(1.05); }

    .avatar {
      position: absolute;
      left: 24px;
      bottom: -46px;
      width: 112px; height: 112px;
      border-radius: 18px;
      border: 3px solid #0f0f0f;
      overflow: hidden;
      box-shadow: 0 0 18px rgba(0,255,100,0.25);
      background: #101010;
      cursor: pointer;
    }
    .avatar img {
      width: 100%; height: 100%; object-fit: cover;
      display: block;
      transition: opacity .25s ease, filter .25s ease;
    }
    .avatar:hover img { opacity: .9; filter: saturate(1.05); }

    .body {
      padding: 70px 20px 22px;
    }

    form#profileForm {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px 14px;
    }
    form#profileForm .full { grid-column: 1 / -1; }

    label {
      display: block;
      color: #bdbdbd;
      font-size: .9rem;
      margin-bottom: .26rem;
    }
    input, textarea {
      width: 100%;
      background: #1a1a1a;
      color: #f1f1f1;
      border: 1px solid #2a2a2a;
      border-radius: 10px;
      padding: .56rem .8rem;
      font-size: .96rem;
      transition: border-color .18s ease, background .18s ease;
    }
    input:focus, textarea:focus {
      outline: none; border-color: #00ff66; background: #161616;
    }
    textarea { min-height: 86px; resize: vertical; }

    .actions {
      grid-column: 1 / -1;
      display: flex; justify-content: flex-end; gap: 10px; margin-top: 2px;
    }

    .btn {
      display: inline-block;
      padding: .8rem 1.4rem;
      border-radius: 9999px;
      font-weight: 800;
      border: none; cursor: pointer;
      transition: all .22s ease;
    }
    .btn.save {
      background: linear-gradient(135deg, #00ff66, #00cc55);
      color: #0b0b0b;
    }
    .btn.save:hover {
      filter: brightness(1.08); transform: translateY(-2px);
      box-shadow: 0 0 16px rgba(0,255,100,0.4);
    }
    .btn.back {
      background: transparent;
      color: #00ff66;
      border: 2px solid #00ff66;
    }
    .btn.back:hover {
      background: rgba(0,255,100,0.08);
      box-shadow: 0 0 12px rgba(0,255,100,0.35);
      transform: translateY(-2px);
    }

    .modal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,.8);
      justify-content: center;
      align-items: center;
      z-index: 999;
    }
    .modal.active { display: flex; }
    .crop-box {
      background: #111;
      padding: 1rem;
      border-radius: 12px;
      text-align: center;
      max-width: 90vw;
      max-height: 80vh;
    }
    .crop-box img {
      max-width: 100%;
      max-height: 60vh;
    }
    .crop-actions {
      margin-top: 1rem;
      display: flex;
      justify-content: center;
      gap: 10px;
    }

    .notif {
      position: fixed;
      top: 20px; left: 50%; transform: translateX(-50%);
      padding: 1rem 1.4rem;
      border-radius: 10px;
      font-weight: 600;
      box-shadow: 0 0 20px rgba(0,255,100,0.3);
      opacity: 0; animation: fadeInOut 4.5s ease forwards;
      z-index: 1000;
    }
    .notif.success { background: rgba(20,20,20,0.95); border: 1px solid #00ff66; color: #00ff66; }
    .notif.error   { background: rgba(20,0,0,0.95);  border: 1px solid #ff4444; color: #ff4444; }

    @media (max-width: 720px) {
      form#profileForm { grid-template-columns: 1fr; }
      .avatar { left: 16px; }
    }
  </style>
</head>
<body>

  <div class="wrap">
    <div class="card">
      <div class="header" id="bannerArea">
        <div class="banner" id="bannerPreview" style="background-image:url('<?= $banner ?>')"></div>
        <div class="avatar" id="avatarArea">
          <img id="avatarPreview" src="<?= $profile_pic ?>" alt="Foto de perfil">
        </div>
      </div>

      <div class="body">
        <form id="profileForm">
          <div class="full">
            <label>Nombre completo</label>
            <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname'] ?? '') ?>" required>
          </div>
          <div>
            <label>Nombre de usuario</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required pattern="[A-Za-z0-9._]{4,30}">
          </div>
          <div class="full">
            <label>Biografía</label>
            <textarea name="bio"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
          </div>
          <div><label>Twitter</label><input type="url" name="link_twitter" value="<?= htmlspecialchars($user['link_twitter'] ?? '') ?>"></div>
          <div><label>Instagram</label><input type="url" name="link_instagram" value="<?= htmlspecialchars($user['link_instagram'] ?? '') ?>"></div>
          <div><label>LinkedIn</label><input type="url" name="link_linkedin" value="<?= htmlspecialchars($user['link_linkedin'] ?? '') ?>"></div>
          <div><label>GitHub</label><input type="url" name="link_github" value="<?= htmlspecialchars($user['link_github'] ?? '') ?>"></div>
          <div class="full"><label>Website</label><input type="url" name="link_website" value="<?= htmlspecialchars($user['link_website'] ?? '') ?>"></div>
          <div class="actions">
            <a class="btn back" href="/u/<?= htmlspecialchars($user['username']) ?>">Cancelar</a>
            <button type="submit" class="btn save">Guardar cambios</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal" id="cropModal">
    <div class="crop-box">
      <img id="cropImage" src="">
      <div class="crop-actions">
        <button class="btn back" id="cancelCrop">Cancelar</button>
        <button class="btn save" id="saveCrop">Guardar imagen</button>
      </div>
    </div>
  </div>

  <script>
    let currentType = null;
    let cropper = null;

    const modal = document.getElementById('cropModal');
    const cropImg = document.getElementById('cropImage');
    const bannerArea = document.getElementById('bannerArea');
    const avatarArea = document.getElementById('avatarArea');
    const bannerPreview = document.getElementById('bannerPreview');
    const avatarPreview = document.getElementById('avatarPreview');

    bannerArea.onclick = () => openCropper('banner');
    avatarArea.onclick = () => openCropper('profile_pic');

    function openCropper(type) {
      currentType = type;
      const input = document.createElement('input');
      input.type = 'file';
      input.accept = 'image/*';
      input.onchange = e => {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = ev => {
          cropImg.src = ev.target.result;
          modal.classList.add('active');
          if (cropper) cropper.destroy();
          cropper = new Cropper(cropImg, {
            aspectRatio: type === 'banner' ? 16/5 : 1,
            viewMode: 1,
            background: false,
            movable: false,
            zoomable: true,
          });
        };
        reader.readAsDataURL(file);
      };
      input.click();
    }

    document.getElementById('saveCrop').onclick = async () => {
      if (!cropper || !currentType) return;
      const canvas = cropper.getCroppedCanvas({ 
        width: currentType === 'banner' ? 1600 : 400,
        height: currentType === 'banner' ? 500 : 400
      });
      canvas.toBlob(async (blob) => {
        const formData = new FormData();
        formData.append('image', blob, 'crop.jpg');
        formData.append('type', currentType);
        try {
          const res = await fetch('/u/upload_image.php', { method: 'POST', body: formData });
          const data = await res.json();
          if (data.success) {
            showNotif("Imagen actualizada correctamente ✅");
            if (currentType === 'banner') bannerPreview.style.backgroundImage = `url(${data.url})`;
            else avatarPreview.src = data.url;
          } else showNotif(data.error, 'error');
        } catch {
          showNotif("Error de red", 'error');
        }
        modal.classList.remove('active');
        cropper.destroy();
      }, 'image/jpeg', 0.9);
    };

    document.getElementById('cancelCrop').onclick = () => {
      modal.classList.remove('active');
      if (cropper) cropper.destroy();
    };

    document.getElementById("profileForm").addEventListener("submit", async (e) => {
      e.preventDefault();
      const formData = new FormData(e.target);
      try {
        const res = await fetch("/u/update_profile.php", { method: "POST", body: formData });
        const data = await res.json();
        if (data.success) {
          sessionStorage.setItem("rogex_flash", JSON.stringify({ type: "success", text: "Perfil actualizado correctamente ✅" }));
          window.location.href = `/u/${formData.get("username")}`;
        } else showNotif(data.error, "error");
      } catch { showNotif("Error de red", "error"); }
    });

    function showNotif(text, type='success') {
      const n = document.createElement('div');
      n.className = `notif ${type}`;
      n.textContent = text;
      document.body.appendChild(n);
      setTimeout(() => n.remove(), 4500);
    }
  </script>
</body>
</html>