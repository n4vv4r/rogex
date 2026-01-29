<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/includes/ai_helper.php";
echo "Clave OpenAI: " . substr(OPENAI_API_KEY, 0, 10) . "...";
$result = generateQuestionsFromText("La fotosíntesis es el proceso por el cual las plantas transforman la energía solar en energía química.", 3);
echo "<pre>" . htmlspecialchars($result) . "</pre>";
