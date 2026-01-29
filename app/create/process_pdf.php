<?php
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../includes/auth_check.php";
require_once __DIR__ . "/../includes/pdf_parser.php";
require_once __DIR__ . "/../includes/ai_helper.php";

header('Content-Type: application/json');

if (empty($_POST['filename'])) {
    echo json_encode(['success' => false, 'error' => 'Falta el archivo PDF.']);
    exit;
}

$filename = basename($_POST['filename']);
$filePath = __DIR__ . "/../uploads/pdfs/" . $filename;

if (!file_exists($filePath)) {
    echo json_encode(['success' => false, 'error' => 'Archivo no encontrado.']);
    exit;
}

try {
    $text = extractTextFromPDF($filePath);

    $questions = generateQuestionsFromText($text, 8);

    $stmt = $pdo->prepare("INSERT INTO quizzes (user_id, pdf_filename, questions) VALUES ((SELECT id FROM users WHERE username = ?), ?, ?)");
    $stmt->execute([$_SESSION['user'], $filename, $questions]);

    echo json_encode([
        'success' => true,
        'message' => 'Quiz generado correctamente',
        'questions' => $questions
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
