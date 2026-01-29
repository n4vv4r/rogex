<?php
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../includes/auth_check_optional.php";

$id = $_GET['id'] ?? null;
if (!$id) {
  http_response_code(404);
  exit("Quiz no encontrado");
}

$stmt = $pdo->prepare("
  SELECT q.*, u.username, u.profile_pic
  FROM quizzes q
  JOIN users u ON q.user_id = u.id
  WHERE q.id = ?
");
$stmt->execute([$id]);
$quiz = $stmt->fetch();

if (!$quiz || $quiz['visibility'] === 'private') {
  http_response_code(403);
  exit("Este quiz es privado o no existe.");
}

$user = htmlspecialchars($quiz['username']);
$profile_pic = !empty($quiz['profile_pic'])
  ? "/assets/uploads/users/" . htmlspecialchars($quiz['profile_pic'])
  : "/assets/default.png";
$title = htmlspecialchars($quiz['title']);
$questions = json_decode($quiz['questions_json'], true);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= $title ?> — RogeX Quiz</title>
  <link rel="stylesheet" href="/assets/css/quiz.css">
</head>
<body>

<nav class="quiz-navbar">
  <div class="logo">
    <a href="https://app.rogex.net"><img src="/apple-touch-icon.png" width="50"></a>
  </div>
  <div class="progress-container"><div id="progressBar" class="progress-bar"></div></div>
</nav>

<div class="quiz-container">
  <h1 class="quiz-title"><?= $title ?></h1>
  <p class="quiz-subtitle">Creado por <strong>@<?= $user ?></strong></p>

  <form id="quizForm" class="quiz-form">
    <div id="questionsContainer">
      <?php foreach ($questions as $i => $q): ?>
        <div class="question" data-correct="<?= htmlspecialchars($q['correct']) ?>">
          <h3><?= ($i+1) ?>. <?= htmlspecialchars($q['question']) ?></h3>
          <?php foreach ($q['options'] as $j => $opt): ?>
            <div class="option">
              <input type="radio" name="q<?= $i ?>" value="<?= $j ?>" id="q<?= $i ?>opt<?= $j ?>">
              <label for="q<?= $i ?>opt<?= $j ?>"><?= chr(65+$j) ?>. <?= htmlspecialchars($opt) ?></label>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <button type="button" id="checkAnswersBtn" disabled>Comprobar</button>
  </form>
</div>

<div id="resultModal" class="quiz-modal">
  <div class="quiz-modal-content">
    <h2 id="resultTitle"></h2>
    <p id="resultSubtitle"></p>
    <div class="modal-buttons">
      <button id="retryQuiz">Repetir quiz</button>
      <button id="viewCorrection">Ver corrección</button>
      <button id="shareResult">Compartir</button>
    </div>
  </div>
</div>

<script src="/assets/js/quiz.js"></script>
</body>
</html>
