<?php
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../includes/auth_check.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['questions']) || empty($data['filename'])) {
    echo json_encode(["success" => false, "error" => "Datos incompletos"]);
    exit;
}

$title = $data['title'] ?? 'Quiz sin título';
$filename = basename($data['filename']);
$questions_json = json_encode($data['questions']);
$visibility = in_array($data['visibility'], ['public','unlisted','private'])
    ? $data['visibility'] : 'private';

try {
    $stmt = $pdo->prepare("
        INSERT INTO quizzes (user_id, title, filename, questions_json, visibility)
        VALUES ((SELECT id FROM users WHERE username = ?), ?, ?, ?, ?)
    ");
    $stmt->execute([$_SESSION['user'], $title, $filename, $questions_json, $visibility]);

    $quiz_id = $pdo->lastInsertId();
    echo json_encode(["success" => true, "quiz_id" => $quiz_id, "redirect" => "/u/" . $_SESSION['user'] . "/quiz/$quiz_id"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
