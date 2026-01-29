<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/includes/auth_check_optional.php";

if (empty($_GET['id'])) {
  exit("Falta el ID del quiz.");
}

$id = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT q.*, u.username FROM quizzes q JOIN users u ON q.user_id = u.id WHERE q.id = ?");
$stmt->execute([$id]);
$quiz = $stmt->fetch();

if (!$quiz) exit("Quiz no encontrado.");

$quizData = json_decode($quiz['quiz_data'], true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Quiz de <?= htmlspecialchars($quiz['username']) ?> | RogeX</title>
  <style>
    body { background:#0f0f0f; color:#fff; font-family:sans-serif; text-align:center; padding:2rem; }
    .question { background:#151515; border-radius:12px; padding:1rem; margin:1rem auto; width:80%; text-align:left; }
    label { display:block; margin:5px 0; }
    button { background:#00ff66; color:#000; border:none; border-radius:10px; padding:0.6rem 1rem; margin-top:1rem; cursor:pointer; }
    button:hover { background:#00ffaa; }
  </style>
</head>
<body>
  <h1>Quiz compartido por <?= htmlspecialchars($quiz['username']) ?></h1>

  <?php foreach ($quizData as $i => $q): ?>
    <div class="question">
      <p><?= htmlspecialchars($q['question']) ?></p>
      <?php foreach ($q['options'] as $opt): ?>
        <label><input type="radio" name="q<?= $i ?>" value="<?= htmlspecialchars($opt) ?>"> <?= htmlspecialchars($opt) ?></label>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>

  <button id="check">Comprobar</button>
  <div id="result" style="display:none;">
    <h2>Resultado: <span id="score"></span></h2>
  </div>

  <script>
    const quiz = <?= json_encode($quizData) ?>;
    document.getElementById("check").onclick = () => {
      let correct = 0;
      quiz.forEach((q, i) => {
        const selected = document.querySelector(`input[name=q${i}]:checked`);
        if (!selected) return;
        if (selected.value.trim() === q.correct.trim()) {
          correct++;
          selected.parentElement.style.color = "lime";
        } else {
          selected.parentElement.style.color = "red";
        }
      });
      document.getElementById("score").innerText = `${correct}/${quiz.length}`;
      document.getElementById("result").style.display = "block";
    };
  </script>
</body>
</html>
