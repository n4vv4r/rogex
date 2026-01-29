<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/includes/auth_check.php";

$flash = $_SESSION['flash_msg'] ?? null;
unset($_SESSION['flash_msg']); 

$stmt = $pdo->prepare("SELECT id, username, profile_pic, is_verified, is_premium FROM users WHERE username = ?");
$stmt->execute([$_SESSION['user']]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userData) {
  header("Location: /logout.php");
  exit;
}

$userId = (int)$userData['id'];
$user = htmlspecialchars($userData['username']);
$profile_pic = (!empty($userData['profile_pic']) && $userData['profile_pic'] !== 'default.png')
  ? "/assets/uploads/users/" . htmlspecialchars($userData['profile_pic'])
  : "/assets/default.png";

$is_verified = !empty($userData['is_verified']);
$is_premium  = !empty($userData['is_premium']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Inicio — RogeX Feed</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="../favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" href="apple-touch-icon.png">  <script src="https://kit.fontawesome.com/a2d04d4e4e.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="stylefeed.css">
  <link rel="stylesheet" href="badges.css">
</head>
<body>
    <style>
        .drop-zone {
  cursor: pointer;
  position: relative;
  z-index: 10;
}

.drag-overlay {
  pointer-events: none;
}

    </style>
<?php if ($flash): ?>
  <div class="notif <?= htmlspecialchars($flash['type']) ?>" id="notif">
    <?= htmlspecialchars($flash['text']) ?>
  </div>
<?php endif; ?>

<div class="layout">
  <aside class="sidebar">
    <img src="apple-touch-icon.png" alt="RogeX" class="logo" width="60px">
    <a href="/" class="active"><img src="home.png" alt="Inicio"></a>
    <a href="#" id="createQuiz"><img src="quiz.png" alt="Crear Quiz"></a>
    <a href="/explore/"><img src="explore.png" alt="Explorar"></a>
    <a href="/u/<?= urlencode($user) ?>"><img src="user.png" alt="Perfil"></a>
    <a href="/ajustes"><img src="settings.png" alt="Ajustes" width="60px"></a>
    <a href="<?= BASE_URL ?>/logout.php"><img src="logout.png" alt="Salir"></a>
  </aside>

  <main class="feed">
    <header class="feed-header">
      <div class="search-wrapper">
        <form action="/search.php" method="GET" class="search-form">
          <input type="text" name="q" placeholder="Buscar en RogeX..." required>
          <button type="submit"><i class="fa fa-search"></i></button>
        </form>
      </div>
    </header>

    <section class="new-post">
      <img src="<?= $profile_pic ?>" alt="Perfil" class="avatar">
      <form id="postForm" enctype="multipart/form-data">
        <textarea id="postContent" placeholder="Haz una pregunta o comparte algo..." maxlength="500" required></textarea>
        <div class="post-preview" id="imagePreview"></div>
        <div class="actions">
          <label for="postImages" class="attach-btn">
            <img src="/attach.png" alt="Adjuntar imagen" width="20">
          </label>
          <input type="file" id="postImages" name="images[]" accept="image/*" multiple hidden>
          <button type="submit" id="publishPost">Publicar</button>
        </div>
      </form>
    </section>

    <section id="feedContainer" class="feed-container">
      <p class="loading-text">Cargando publicaciones...</p>
    </section>
  </main>

  <aside class="rightbar">
    <div class="rightbar-card">
      <h3>Categorías</h3>
      <p style="color:#777;">Próximamente...</p>
    </div>
  </aside>
</div>

<div class="modal" id="createModal">
  <div class="modal-content" id="modalContent">
    <img src="apple-touch-icon.png" alt="RogeX Logo" width="60px">
    <h2>Selecciona o arrastra tu PDF para empezar</h2>
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

<script>
const USER_ID = <?= $userId ?>;

window.addEventListener("load", () => {
  const n = document.getElementById("notif");
  if (n) setTimeout(() => n.remove(), 5000);
});

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

<script src="/assets/js/feed.js" defer></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const dropZone = document.getElementById("dropZone");
  const pdfInput = document.getElementById("pdfInput");
  const uploadStatus = document.getElementById("uploadStatus");
  const fileName = document.getElementById("fileName");
  const progressBar = document.getElementById("progress");
  const uploadPercent = document.getElementById("uploadPercent");
  const dragOverlay = document.getElementById("dragOverlay");
  const dropText = document.getElementById("dropText");

  if (!dropZone || !pdfInput) return;

  dropZone.addEventListener("click", () => pdfInput.click());

  pdfInput.addEventListener("change", (e) => {
    const file = e.target.files[0];
    if (!file) return;
    handleFile(file);
  });

  dropZone.addEventListener("dragover", (e) => {
    e.preventDefault();
    dropZone.classList.add("dragging");
    dragOverlay.classList.add("active");
  });

  dropZone.addEventListener("dragleave", (e) => {
    e.preventDefault();
    dropZone.classList.remove("dragging");
    dragOverlay.classList.remove("active");
  });

  dropZone.addEventListener("drop", (e) => {
    e.preventDefault();
    dropZone.classList.remove("dragging");
    dragOverlay.classList.remove("active");
    const file = e.dataTransfer.files[0];
    if (file && file.type === "application/pdf") {
      pdfInput.files = e.dataTransfer.files; // vincular
      handleFile(file);
    } else {
      alert("Por favor selecciona un archivo PDF válido.");
    }
  });

  function handleFile(file) {
    uploadStatus.style.display = "block";
    fileName.textContent = file.name;
    progressBar.style.width = "0%";
    uploadPercent.textContent = "0%";

    let progress = 0;
    const timer = setInterval(() => {
      progress += 10;
      progressBar.style.width = progress + "%";
      uploadPercent.textContent = progress + "%";
      if (progress >= 100) {
        clearInterval(timer);
        dropText.textContent = "✅ PDF listo para procesar";
      }
    }, 150);
  }
});
</script>

</body>
</html>
