<?php
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../includes/auth_check.php";

$stmt = $pdo->prepare("SELECT username, profile_pic FROM users WHERE username = ?");
$stmt->execute([$_SESSION['user']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  header("Location: ../../login");
  exit;
}

$current_pic = $user['profile_pic'] === 'default.png'
  ? "/assets/default.png"
  : "/assets/uploads/users/" . htmlspecialchars($user['profile_pic']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Cambiar foto de perfil — RogeX</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet">
  <script src="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.js"></script>

  <style>
    body {
      background: #0f0f0f;
      color: #f0f0f0;
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
    }

    .card {
      background: rgba(18,18,18,0.95);
      border: 1px solid #222;
      border-radius: 16px;
      padding: 2rem;
      box-shadow: 0 0 24px rgba(0,255,100,0.1);
      width: 420px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .card h2 {
      margin-bottom: 1rem;
      color: #00ff66;
    }

    .preview {
      width: 200px;
      height: 200px;
      margin: 1rem auto;
      border-radius: 50%;
      overflow: hidden;
      border: 3px solid #00ff66;
      box-shadow: 0 0 20px rgba(0,255,100,0.3);
    }

    .preview img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    input[type=file] {
      margin-top: 1rem;
      color: #ccc;
    }

    .btn {
      margin: 0.5rem;
      padding: 0.8rem 1.4rem;
      border-radius: 9999px;
      border: 2px solid #00ff66;
      background: transparent;
      color: #00ff66;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.25s ease;
    }

    .btn:hover {
      background: rgba(0,255,100,0.1);
      box-shadow: 0 0 10px rgba(0,255,100,0.4);
      transform: translateY(-2px);
    }

    #cropperContainer {
      display: none;
      margin-top: 1rem;
    }

    canvas {
      max-width: 100%;
      border-radius: 12px;
    }
  </style>
</head>
<body>

  <div class="card">
    <h2>Cambiar foto de perfil</h2>

    <div class="preview">
      <img id="preview" src="<?= $current_pic ?>" alt="Foto actual">
    </div>

    <input type="file" id="fileInput" accept="image/png, image/jpeg, image/webp">
    <div id="cropperContainer">
      <img id="imageToCrop">
      <div>
        <button class="btn" id="saveCrop">Guardar</button>
        <button class="btn" id="cancelCrop">Cancelar</button>
      </div>
    </div>

    <a href="/u/<?= htmlspecialchars($user['username']) ?>" class="btn" style="margin-top:1rem;">← Volver</a>
  </div>

  <script>
    let cropper;
    const fileInput = document.getElementById("fileInput");
    const cropperContainer = document.getElementById("cropperContainer");
    const imageToCrop = document.getElementById("imageToCrop");
    const preview = document.getElementById("preview");
    const saveBtn = document.getElementById("saveCrop");
    const cancelBtn = document.getElementById("cancelCrop");

    fileInput.addEventListener("change", e => {
      const file = e.target.files[0];
      if (!file) return;

      const reader = new FileReader();
      reader.onload = () => {
        imageToCrop.src = reader.result;
        cropperContainer.style.display = "block";
        preview.style.display = "none";

        if (cropper) cropper.destroy();
        cropper = new Cropper(imageToCrop, {
          aspectRatio: 1,
          viewMode: 2,
          background: false,
          zoomable: true,
          movable: true,
          responsive: true,
        });
      };
      reader.readAsDataURL(file);
    });

    cancelBtn.addEventListener("click", () => {
      cropperContainer.style.display = "none";
      preview.style.display = "block";
      fileInput.value = "";
      if (cropper) cropper.destroy();
    });

    saveBtn.addEventListener("click", async () => {
      if (!cropper) return;

      const canvas = cropper.getCroppedCanvas({ width: 400, height: 400 });
      const blob = await new Promise(resolve => canvas.toBlob(resolve, "image/webp"));
      const formData = new FormData();
      formData.append("profile_pic", blob, "profile.webp");

      const res = await fetch("/u/upload_image.php", { method: "POST", body: formData });
      const data = await res.json();

      if (data.success) {
        preview.src = data.url;
        cropperContainer.style.display = "none";
        preview.style.display = "block";
        alert("✅ Foto actualizada correctamente");
      } else {
        alert("⚠️ " + data.error);
      }
    });
  </script>
</body>
</html>
