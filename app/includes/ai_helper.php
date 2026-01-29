<?php
require_once __DIR__ . "/../../config.php";

function generateQuestionsFromText($text, $numQuestions = 5) {
    $apiKey = OPENAI_API_KEY;
    $endpoint = "https://api.openai.com/v1/chat/completions";
    $headers = [
        "Content-Type: application/json",
        "Authorization: " . "Bearer $apiKey"
    ];

    $prompt = "Genera $numQuestions preguntas de comprensión lectora en español, con este formato EXACTO:\n\n
1. [Pregunta]\n
A) Opción A\n
B) Opción B\n
C) Opción C\n
D) Opción D\n
Correcta: [letra de la opción correcta, A/B/C/D]\n\n
No incluyas explicaciones ni texto adicional. Basado en este texto:\n\n$text";

    $data = [
        "model" => "gpt-4o-mini",
        "messages" => [
            ["role" => "system", "content" => "Eres un generador de quizzes educativos estructurados en formato ABCD."],
            ["role" => "user", "content" => $prompt]
        ],
        "max_tokens" => 800,
        "temperature" => 0.7
    ];

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        return "Error cURL: " . curl_error($ch);
    }
    curl_close($ch);

    $result = json_decode($response, true);
    if (isset($result['error'])) {
        return "Error API: " . $result['error']['message'];
    }

    return $result['choices'][0]['message']['content'] ?? 'Error generando preguntas.';
}
