<?php
require_once __DIR__ . "/../../config.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
  SELECT q.*, u.username 
  FROM quizzes q
  JOIN users u ON u.id = q.user_id
  WHERE q.id = ? AND q.visibility IN ('public', 'unlisted')
");
$stmt->execute([$id]);
$quiz = $stmt->fetch();

if (!$quiz) {
  http_response_code(404);
  echo "<h1 style='color:white;text-align:center;margin-top:40vh;'>Quiz no encontrado.</h1>";
  exit;
}

$questions = json_decode($quiz['questions_json'], true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($quiz['title']) ?> — RogeX Quiz</title>
  <link rel="icon" href="/apple-touch-icon.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    :root {
      --green:#00ff66;
      --bg:#0f0f0f;
      --card:#121212;
      --border:#1a1a1a;
      --text:#f0f0f0;
      --muted:#9b9b9b;
      --red:#ff4444;
    }

    body {
      background: var(--bg);
      color: var(--text);
      font-family: "Inter", Arial, sans-serif;
      margin: 0;
      min-height: 100vh;
    }

    .quiz-navbar {
      position: sticky;
      top: 0;
      z-index: 50;
      background: rgba(15,15,15,0.9);
      backdrop-filter: blur(8px);
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.8rem 1.2rem;
    }

    .quiz-navbar img { width: 42px; }

    .progress-container {
      width: 100%;
      height: 8px;
      background: #1a1a1a;
      border-radius: 9999px;
      overflow: hidden;
      border: 1px solid #222;
    }

    .progress-bar {
      height: 100%;
      width: 0;
      background: var(--green);
      transition: width 0.25s ease;
    }

    .quiz-container {
      max-width: 900px;
      margin: 2rem auto;
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 2rem;
      box-shadow: 0 10px 40px rgba(0,0,0,0.4);
    }

    h1 { color: var(--green); margin-bottom: 0.3rem; }
    p.meta { color: var(--muted); font-size: 0.95rem; margin-bottom: 1.5rem; }

    .question-block {
      margin: 1.2rem 0;
      padding: 1rem;
      background: #1a1a1a;
      border-radius: 12px;
      border: 1px solid #222;
    }

    .question-block h3 {
      font-size: 1.05rem;
      margin-bottom: 0.7rem;
    }

    .options {
      display: grid;
      gap: 0.6rem;
    }

    .option {
      padding: 10px 14px;
      background: #151515;
      border-radius: 10px;
      border: 1px solid #222;
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      gap: 8px;
      user-select: none;
    }

    .option:hover {
      border-color: var(--green);
      background: rgba(0,255,102,0.05);
    }

    .option.selected {
      border-color: var(--green);
      background: rgba(0,255,102,0.1);
    }

    .option.correct {
      background: #122e1a !important;
      border-color: var(--green) !important;
    }

    .option.wrong {
      background: #2e1212 !important;
      border-color: var(--red) !important;
    }

    button {
      background: var(--green);
      color: #0b0b0b;
      border: none;
      border-radius: 12px;
      padding: 12px 20px;
      font-weight: 700;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.25s ease;
    }

    button:hover:not(:disabled) {
      transform: scale(1.05);
      background: #22ff77;
    }

    button:disabled {
      opacity: 0.4;
      cursor: not-allowed;
    }

    .quiz-modal {
      position: fixed;
      inset: 0;
      display: none;
      justify-content: center;
      align-items: center;
      background: rgba(0,0,0,0.7);
      z-index: 100;
    }

    .quiz-modal.active { display: flex; }

    .quiz-modal-content {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 1.8rem;
      width: 90%;
      max-width: 440px;
      text-align: center;
      box-shadow: 0 0 30px rgba(0,255,100,0.25);
    }

    .quiz-modal-content h2 { color: var(--green); margin-bottom: 0.5rem; }
    .quiz-modal-content p { color: var(--muted); margin-bottom: 1rem; }

    .modal-buttons {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      justify-content: center;
    }

    .modal-buttons button {
      background: transparent;
      border: 2px solid var(--green);
      color: var(--green);
      border-radius: 9999px;
      padding: 10px 18px;
    }

    .modal-buttons button:hover {
      background: rgba(0,255,100,0.1);
    }
  </style>
</head>
<body>

  <nav class="quiz-navbar">
    <img src="/apple-touch-icon.png" alt="RogeX" />
    <div class="progress-container">
      <div class="progress-bar" id="progressBar"></div>
    </div>
  </nav>

  <main class="quiz-container">
    <h1><?= htmlspecialchars($quiz['title']) ?></h1>
    <p class="meta">Creado por <b>@<?= htmlspecialchars($quiz['username']) ?></b></p>

    <form id="quizForm">
      <?php foreach ($questions as $i => $q): ?>
        <div class="question-block" data-index="<?= $i ?>">
          <h3><?= ($i + 1) ?>. <?= htmlspecialchars($q['question']) ?></h3>
          <div class="options">
            <?php foreach ($q['options'] as $j => $opt): ?>
              <div class="option" data-value="<?= $j ?>">
                <?= chr(65 + $j) ?>. <?= htmlspecialchars($opt) ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <button id="checkBtn" type="button" disabled>Comprobar</button>
    </form>
  </main>

  <div class="quiz-modal" id="resultModal">
    <div class="quiz-modal-content">
      <h2 id="resultTitle"></h2>
      <p id="resultSubtitle"></p>
      <div class="modal-buttons">
        <button id="retryQuiz">Repetir</button>
        <button id="viewCorrection">Ver corrección</button>
        <button id="shareResult">Compartir</button>
      </div>
    </div>
  </div>

<script>
  const blocks = document.querySelectorAll(".question-block");
  const progressBar = document.getElementById("progressBar");
  const checkBtn = document.getElementById("checkBtn");
  const resultModal = document.getElementById("resultModal");
  const resultTitle = document.getElementById("resultTitle");
  const resultSubtitle = document.getElementById("resultSubtitle");
  const retryQuiz = document.getElementById("retryQuiz");
  const viewCorrection = document.getElementById("viewCorrection");
  const shareResult = document.getElementById("shareResult");

  const correctAnswers = <?= json_encode(array_column($questions, 'correct')) ?>;
  let answers = new Array(correctAnswers.length).fill(null);
  let score = 0;
  let correctionMode = false;

  // Seleccionar opciones
  blocks.forEach(block => {
    const options = block.querySelectorAll(".option");
    const idx = parseInt(block.dataset.index);
    options.forEach(opt => {
      opt.addEventListener("click", () => {
        if (correctionMode) return; // no se puede modificar en modo corrección
        options.forEach(o => o.classList.remove("selected"));
        opt.classList.add("selected");
        answers[idx] = parseInt(opt.dataset.value);
        updateProgress();
      });
    });
  });

  function updateProgress() {
    const answered = answers.filter(a => a !== null).length;
    const pct = (answered / correctAnswers.length) * 100;
    progressBar.style.width = pct + "%";
    checkBtn.disabled = answered !== correctAnswers.length;
  }

  checkBtn.addEventListener("click", () => {
    score = 0;
    blocks.forEach((block, i) => {
      const chosen = answers[i];
      const correct = correctAnswers[i];
      const opts = block.querySelectorAll(".option");
      opts.forEach((opt, j) => {
        opt.classList.remove("selected");
        if (j === correct) opt.classList.add("correct");
        else if (j === chosen) opt.classList.add("wrong");
      });
      if (chosen === correct) score++;
    });

    resultTitle.textContent = `Tu resultado: ${score}/${correctAnswers.length}`;
    resultSubtitle.textContent = score >= correctAnswers.length * 0.7
      ? "¡Excelente trabajo!"
      : "Sigue practicando 💪";
    resultModal.classList.add("active");
    checkBtn.disabled = true;
  });

  retryQuiz.addEventListener("click", () => {
    resultModal.classList.remove("active");
    correctionMode = false;
    answers.fill(null);
    blocks.forEach(block => {
      block.querySelectorAll(".option").forEach(o => {
        o.classList.remove("correct", "wrong", "selected");
      });
    });
    progressBar.style.width = "0%";
    checkBtn.disabled = true;
  });

  viewCorrection.addEventListener("click", () => {
    resultModal.classList.remove("active");
    correctionMode = true;
    blocks.forEach((block, i) => {
      const correct = correctAnswers[i];
      const chosen = answers[i];
      const opts = block.querySelectorAll(".option");
      opts.forEach((opt, j) => {
        opt.classList.remove("selected");
        if (j === correct) opt.classList.add("correct");
        else if (j === chosen) opt.classList.add("wrong");
      });
    });
  });

  shareResult.addEventListener("click", async () => {
    const text = `He sacado ${score}/${correctAnswers.length} en el quiz “<?= addslashes($quiz['title']) ?>” en RogeX 🔥`;
    if (navigator.share) {
      await navigator.share({ title: "RogeX Quiz", text });
    } else {
      await navigator.clipboard.writeText(text);
      alert("Resultado copiado al portapapeles.");
    }
  });
</script>

</body>
</html>
