<?php
$dir = __DIR__ . "/assets/uploads/feed/";
echo "<pre>";
echo "Ruta: $dir\n";
echo "Existe: " . (is_dir($dir) ? "si" : "no") . "\n";
echo "Escribible: " . (is_writable($dir) ? "si" : "no") . "\n";
// es solo un debug simple