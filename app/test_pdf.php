<?php
require_once __DIR__ . "/vendor/autoload.php";
use Smalot\PdfParser\Parser;

$parser = new Parser();
$pdf = $parser->parseFile(__DIR__ . '/uploads/pdfs/pdf_68fcc11bda5234.63844129_BASES_ESPECIFIQUES.pdf');
echo nl2br(htmlspecialchars($pdf->getText()));
