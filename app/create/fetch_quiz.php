<?php
require_once __DIR__ . "/../../config.php";

header("Content-Type: application/json");

if (!isset($_GET['id'])) {
    echo json_encode(["success" => false, "error" => "Falta el ID"]);
    exit;
}

$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT q.*, u.username FROM quizzes q JOIN users u ON q.user_id = u.id WHERE q.id = ?");
$stmt->execute([$id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    echo json_encode(["success" => false, "error" => "Quiz no encontrado"]);
    exit;
}

echo json_encode([
    "success" => true,
    "quiz" => [
        "id" => $quiz['id'],
        "title" => $quiz['title'],
        "username" => $quiz['username'],
        "questions" => json_decode($quiz['questions_json'], true),
        "visibility" => $quiz['visibility'],
        "created_at" => $quiz['created_at']
    ]
]);
