<?php
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../includes/auth_check.php";

session_start();
$username = $_SESSION["user"] ?? null;

$search = trim($_GET["q"] ?? "");
$params = [];

$sql = "
  SELECT q.id, q.title, q.created_at, q.filename, u.username, u.profile_pic
  FROM quizzes q
  JOIN users u ON q.user_id = u.id
  WHERE q.visibility = 'public'
";
if ($search !== "") {
  $sql .= " AND (q.title LIKE :search OR u.username LIKE :search)";
  $params[":search"] = "%$search%";
}
$sql .= " ORDER BY q.created_at DESC LIMIT 60";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Explorar Quizzes — RogeX</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="/favicon.ico">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">
  <script src="https://kit.fontawesome.com/a2d04d4e4e.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="styleexplore.css">
</head>
<body>

<div class="layout">
  <aside class="sidebar">
    <img src="../apple-touch-icon.png" alt="RogeX" class="logo" width="60px">
    <a href="/"><img src="../home.png" alt="Inicio"></a>
    <a href="#" id="createQuiz"><img src="../quiz.png" alt="Crear Quiz"></a>
    <a href="#" class="active"><img src="../explore.png" alt="Explorar"></a>
    <a href="/u/<?= urlencode($username) ?>"><img src="../user.png" alt="Perfil"></a>
    <a href="/ajustes/"><img src="../settings.png" alt="Ajustes"></a>
    <a href="<?= BASE_URL ?>/logout.php"><img src="../logout.png" alt="Salir"></a>
  </aside>

  <main class="explore-main">
    <header class="explore-header">
      <form class="search-form" method="GET" action="">
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar quizzes..." autocomplete="off">
        <button type="submit"><i class="fa fa-search"></i></button>
      </form>
      <h2>Explorar Quizzes públicos</h2>
    </header>

    <?php if (empty($quizzes)): ?>
      <p class="no-quizzes">Aún no hay quizzes públicos disponibles.</p>
    <?php else: ?>
      <div class="quiz-grid">
        <?php foreach ($quizzes as $q): ?>
          <article class="quiz-card" onclick="window.location.href='/quiz/<?= $q['id'] ?>'">
            <div class="quiz-thumb">
              <?php
                $thumbPath = "/assets/thumbnails/" . $q['id'] . ".jpg";
                if (file_exists($_SERVER["DOCUMENT_ROOT"] . $thumbPath)) {
                  echo "<img src='$thumbPath' alt='Vista previa'>";
                } else {
                  echo "<div class='pdf-placeholder'><i class='fa-solid fa-file-pdf'></i></div>";
                }
              ?>
            </div>
            <div class="quiz-info">
              <h3><?= htmlspecialchars($q['title'] ?: 'Quiz sin título') ?></h3>
              <div class="meta">
                <img src="<?= !empty($q['profile_pic']) ? '/assets/uploads/users/' . htmlspecialchars($q['profile_pic']) : '/assets/default.png' ?>" alt="@<?= htmlspecialchars($q['username']) ?>">
                <span class="user">@<?= htmlspecialchars($q['username']) ?></span>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <footer>&copy; RogeX 2025 — Aprende, comparte y mejora.</footer>
  </main>
</div>

<div class="modal" id="createModal">
  <div class="modal-content" id="modalContent">
    <img src="../apple-touch-icon.png" alt="RogeX" width="60px" style="margin-bottom:10px;">
    <h2>Sube tu PDF para crear un Quiz</h2>
    <div class="drop-zone" id="dropZone">
      <i class="fa-solid fa-file-pdf" style="font-size:2rem;margin-bottom:8px;"></i>
      <p id="dropText">Arrastra tu PDF aquí o haz clic</p>
      <input type="file" id="pdfInput" accept="application/pdf" hidden>
      <div class="upload-status" id="uploadStatus" style="display:none;">
        <div id="fileName" class="file-name"></div>
        <div class="progress-container"><div class="progress-bar" id="progressBar"></div></div>
        <div id="uploadPercent" class="upload-percent">0%</div>
      </div>
    </div>
    <button class="close-modal" id="closeModal">Cancelar</button>
  </div>
</div>

<div class="drag-overlay" id="dragOverlay"></div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const createQuizBtn = document.getElementById("createQuiz");
  const modal = document.getElementById("createModal");
  const closeBtn = document.getElementById("closeModal");
  const dropZone = document.getElementById("dropZone");
  const pdfInput = document.getElementById("pdfInput");
  const uploadStatus = document.getElementById("uploadStatus");
  const fileName = document.getElementById("fileName");
  const progressBar = document.getElementById("progressBar");
  const uploadPercent = document.getElementById("uploadPercent");
  const dropText = document.getElementById("dropText");
  const dragOverlay = document.getElementById("dragOverlay");

  if (!createQuizBtn) return;

  createQuizBtn.addEventListener("click", (e) => {
    e.preventDefault();
    modal.classList.add("active");
  });

  closeBtn.addEventListener("click", () => {
    modal.classList.remove("active");
    resetUpload();
  });

  modal.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.classList.remove("active");
      resetUpload();
    }
  });

  dropZone.addEventListener("click", () => pdfInput.click());

  pdfInput.addEventListener("change", e => {
    const file = e.target.files[0];
    if (file) handleFile(file);
  });

  dropZone.addEventListener("dragover", e => {
    e.preventDefault();
    dropZone.classList.add("dragging");
    dragOverlay.classList.add("active");
  });
  dropZone.addEventListener("dragleave", e => {
    e.preventDefault();
    dropZone.classList.remove("dragging");
    dragOverlay.classList.remove("active");
  });
  dropZone.addEventListener("drop", e => {
    e.preventDefault();
    dropZone.classList.remove("dragging");
    dragOverlay.classList.remove("active");
    const file = e.dataTransfer.files[0];
    if (file) handleFile(file);
  });

  // simulacion
  function handleFile(file) {
    if (file.type !== "application/pdf") {
      alert("Solo se permiten archivos PDF.");
      return;
    }

    fileName.textContent = file.name;
    uploadStatus.style.display = "block";

    // Simulacion de progreso
    let progress = 0;
    const interval = setInterval(() => {
      progress += 5;
      progressBar.style.width = progress + "%";
      uploadPercent.textContent = progress + "%";
      if (progress >= 100) {
        clearInterval(interval);
        dropText.textContent = "✅ PDF cargado correctamente";
        setTimeout(() => {
          modal.classList.remove("active");
          resetUpload();
          window.location.href = "/create/?file=" + encodeURIComponent(file.name);
        }, 700);
      }
    }, 100);
  }

  function resetUpload() {
    progressBar.style.width = "0%";
    uploadPercent.textContent = "0%";
    uploadStatus.style.display = "none";
    dropText.textContent = "Arrastra tu PDF aquí o haz clic";
  }
});
</script>
</body>
</html>
