<?php
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../includes/auth_check.php";

if (empty($_GET['file'])) {
  header("Location: " . APP_URL);
  exit;
}
$filename = basename($_GET['file']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Crear Quiz — RogeX</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="/apple-touch-icon.png" />
  <link rel="apple-touch-icon" href="/apple-touch-icon.png" />
  <link rel="stylesheet" href="assets/css/quiz.css" />
  <style>
    :root {
      --green:#00ff66; --bg:#0f0f0f; --card:#121212;
      --border:#1a1a1a; --text:#f0f0f0; --muted:#9b9b9b;
    }
    body {
      margin:0; background:var(--bg); color:var(--text);
      font-family: Helvetica, Arial, sans-serif;
    }

    .quiz-navbar {
      position:sticky; top:0; z-index:50;
      display:flex; align-items:center; gap:16px;
      padding:10px 14px;
      background:rgba(15,15,15,.8);
      backdrop-filter:blur(8px);
      border-bottom:1px solid var(--border);
    }
    .quiz-navbar .logo img { width:42px; height:42px; }

    .progress-container {
      flex:1; height:8px; background:#1a1a1a;
      border-radius:9999px; overflow:hidden;
      border:1px solid #222;
    }
    .progress-bar {
      height:100%; width:0;
      background:var(--green);
      transition:width .25s ease;
    }

    .quiz-container {
      max-width:900px; margin:28px auto; padding:0 16px;
    }
    .quiz-title { margin:8px 0 6px; }
    .quiz-subtitle { color:var(--muted); margin:0 0 16px; }

    .card {
      background:var(--card);
      border:1px solid var(--border);
      border-radius:16px;
      box-shadow:0 10px 40px rgba(0,0,0,.35);
      padding:18px;
    }

    .loading {
      display:flex; gap:12px; align-items:center; color:var(--muted);
    }
    .spinner {
      width:18px; height:18px;
      border:3px solid #2a2a2a;
      border-top-color:var(--green);
      border-radius:50%;
      animation:spin 1s linear infinite;
    }
    @keyframes spin{to{transform:rotate(360deg)}}

    .hidden { display:none !important; }

    #checkAnswersBtn {
      margin-top:18px;
      background:var(--green);
      color:#0b0b0b;
      border:0;
      border-radius:12px;
      padding:12px 18px;
      font-weight:800;
      cursor:pointer;
    }
    #checkAnswersBtn:disabled {
      opacity:.4; cursor:not-allowed;
    }

    .question-block {
      background:var(--card);
      border:1px solid var(--border);
      border-radius:12px;
      padding:14px;
      margin:12px 0;
    }

    .options { display:grid; gap:8px; margin-top:8px; }

    .option {
      display:flex; gap:8px; align-items:center;
      background:#151515;
      border:1px solid #232323;
      border-radius:10px;
      padding:10px; cursor:pointer;
    }
    .option input { accent-color:#00ff66; }

    /* MODALES */
    .quiz-modal {
      position:fixed; inset:0;
      display:none;
      justify-content:center;
      align-items:center;
      background:rgba(0,0,0,.8);
      z-index:1000;
    }
    .quiz-modal.active { display:flex; }

    .quiz-modal-content {
      background:var(--card);
      border:1px solid var(--border);
      border-radius:16px;
      max-width:520px; width:92%;
      padding:24px;
      box-shadow:0 20px 60px rgba(0,0,0,.4);
      text-align:center;
    }

    .modal-buttons {
      display:flex; gap:10px; flex-wrap:wrap;
      justify-content:center; margin-top:14px;
    }

    .modal-buttons button {
      border:2px solid var(--green);
      background:transparent;
      color:var(--green);
      padding:10px 14px;
      border-radius:9999px;
      font-weight:700;
      cursor:pointer;
      transition:all .25s ease;
    }
    .modal-buttons button:hover {
      background:rgba(0,255,100,0.1);
      box-shadow:0 0 12px rgba(0,255,100,0.3);
    }

    input#quizTitleInput {
      width:90%;
      background:#0f0f0f;
      border:1px solid #222;
      border-radius:10px;
      padding:10px;
      color:#f0f0f0;
      outline:none;
      margin-top:12px;
    }
    input#quizTitleInput:focus {
      border-color:#00ff66;
      box-shadow:0 0 6px rgba(0,255,100,0.3);
    }
  </style>
</head>
<body>

  <nav class="quiz-navbar">
    <div class="logo">
      <img src="/apple-touch-icon.png" alt="RogeX" />
    </div>
    <div class="progress-container">
      <div class="progress-bar" id="progressBar"></div>
    </div>
  </nav>

  <main class="quiz-container">
    <div class="card">
      <h1 class="quiz-title">Generando tu quiz...</h1>
      <p class="quiz-subtitle">Analizando tu documento: <b><?= htmlspecialchars($filename) ?></b></p>

      <div id="loadingSection" class="loading">
        <div class="spinner"></div>
        <p>Procesando con inteligencia artificial...</p>
      </div>

      <form id="quizForm" class="hidden">
        <div id="questionsContainer"></div>
        <button type="button" id="checkAnswersBtn" disabled>Comprobar</button>
      </form>
    </div>
  </main>

  <div class="quiz-modal" id="resultModal">
    <div class="quiz-modal-content">
      <h2 id="resultTitle"></h2>
      <p id="resultSubtitle"></p>
      <div class="modal-buttons">
        <button id="retryQuiz">Repetir</button>
        <button id="viewCorrection">Ver corrección</button>
        <button id="shareResult">Compartir</button>
        <button id="publishQuiz">Publicar Quiz</button>
      </div>
    </div>
  </div>

  <div class="quiz-modal" id="publishModal">
    <div class="quiz-modal-content">
      <h2>Publicar Quiz</h2>
      <p>Asigna un título personalizado a tu quiz.</p>
      <input type="text" id="quizTitleInput" placeholder="Ej: Historia de Roma — Capítulo 3" maxlength="80" />
      <div class="modal-buttons">
        <button id="confirmPublish">Publicar</button>
        <button id="cancelPublish">Cancelar</button>
      </div>
    </div>
  </div>

  <script>
    const FILENAME = "<?= htmlspecialchars($filename) ?>";
  </script>
  <script src="assets/js/quiz.js"></script>
</body>
</html>
