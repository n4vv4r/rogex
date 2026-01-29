<?php
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../includes/auth_check.php";
require_once __DIR__ . "/../includes/ai_helper.php";

header("Content-Type: application/json");

$filename = isset($_GET['file']) ? basename($_GET['file']) : null;
if (!$filename) {
  echo json_encode(['success' => false, 'error' => 'Archivo no especificado.']); exit;
}

$pdfPath = realpath(__DIR__ . "/../uploads/pdfs/" . $filename);
if (!$pdfPath || !file_exists($pdfPath)) {
  echo json_encode(['success' => false, 'error' => 'El PDF no existe en el servidor.']); exit;
}

$pdfText = '';
try {
  if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    $parser = new \Smalot\PdfParser\Parser();
    $pdf    = $parser->parseFile($pdfPath);
    $pdfText = trim($pdf->getText());
  }
} catch (Throwable $e) {
}

if (!$pdfText) {
  $pdfText = "Contenido del PDF no pudo extraerse completamente. Genera preguntas genéricas de comprensión (tema académico) basadas en títulos/secciones posibles.";
}

$apiKey = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';
if (!$apiKey) {
  echo json_encode(['success' => false, 'error' => 'Falta la clave de OpenAI en el servidor.']); exit;
}

$model = 'gpt-4o-mini'; 

$instructions = [
  "role" => "system",
  "content" => "Eres un generador de quizzes educativos en español. Devuelves SOLO JSON válido sin texto adicional."
];

$userPrompt = [
  "role" => "user",
  "content" => 
    "Analiza el siguiente texto y crea EXACTAMENTE 10 preguntas tipo test. " .
    "Cada pregunta debe tener 4 opciones (A,B,C,D). Devuelve JSON con este formato estricto:\n\n" .
    "{\n  \"questions\": [\n    {\"question\": \"...\", \"options\": [\"...\",\"...\",\"...\",\"...\"], \"correct\": 0},\n    ... (x10)\n  ]\n}\n\n" .
    "Reglas:\n- 'correct' es el índice correcto (0-3).\n- No incluyas explicaciones, ni texto fuera del JSON.\n- Preguntas claras y concisas.\n\n" .
    "Texto fuente:\n---\n" . mb_substr($pdfText, 0, 8000, 'UTF-8') . "\n---"
];

$payload = [
  "model" => $model,
  "messages" => [$instructions, $userPrompt],
  "temperature" => 0.3,
  "max_tokens" => 1400,
];

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt_array($ch, [
  CURLOPT_HTTPHEADER => [
    "Authorization: Bearer {$apiKey}",
    "Content-Type: application/json",
  ],
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => json_encode($payload),
]);
$response = curl_exec($ch);
if (curl_errno($ch)) {
  echo json_encode(['success' => false, 'error' => 'Error cURL: ' . curl_error($ch)]); exit;
}
curl_close($ch);

$data = json_decode($response, true);
if (isset($data['error'])) {
  echo json_encode(['success' => false, 'error' => 'Error API: ' . ($data['error']['message'] ?? 'desconocido')]); exit;
}

$content = $data['choices'][0]['message']['content'] ?? '';
$jsonStart = strpos($content, '{');
$jsonEnd   = strrpos($content, '}');
if ($jsonStart !== false && $jsonEnd !== false) {
  $content = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
}

$decoded = json_decode($content, true);
if (!$decoded || !isset($decoded['questions']) || !is_array($decoded['questions'])) {
  echo json_encode(['success' => false, 'error' => 'No se pudo parsear el JSON de preguntas.']); exit;
}

$questions = [];
foreach ($decoded['questions'] as $q) {
  if (
    isset($q['question'], $q['options'], $q['correct']) &&
    is_array($q['options']) && count($q['options']) === 4 &&
    is_int($q['correct']) && $q['correct'] >= 0 && $q['correct'] <= 3
  ) {
    $questions[] = [
      'question' => trim((string)$q['question']),
      'options'  => array_values(array_map('strval', $q['options'])),
      'correct'  => (int)$q['correct'],
    ];
  }
}
if (count($questions) < 5) {
  echo json_encode(['success' => false, 'error' => 'Preguntas insuficientes generadas.']); exit;
}

try {
  $title = pathinfo($filename, PATHINFO_FILENAME);
  $visibility = 'private';
  $stmt = $pdo->prepare("
    INSERT INTO quizzes (user_id, title, filename, questions_json, visibility, created_at)
    VALUES ((SELECT id FROM users WHERE username = ?), ?, ?, ?, ?, NOW())
  ");
  $stmt->execute([
    $_SESSION['user'],
    $title,
    $filename,
    json_encode($questions, JSON_UNESCAPED_UNICODE),
    $visibility
  ]);
  $quizId = (int)$pdo->lastInsertId();
} catch (Throwable $e) {
  echo json_encode(['success' => false, 'error' => 'Error BD: ' . $e->getMessage()]); exit;
}

echo json_encode([
  'success'  => true,
  'quiz_id'  => $quizId,
  'filename' => $filename,
  'questions'=> $questions,
]);
