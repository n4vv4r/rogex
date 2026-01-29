<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/includes/auth_check.php";

if (session_status() === PHP_SESSION_NONE) session_start();

header("Content-Type: application/json; charset=utf-8");

$username = $_SESSION["user"] ?? null;
if (!$username) {
  echo json_encode(["success" => false, "error" => "Usuario no autenticado."]);
  exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
$user_id = $stmt->fetchColumn();

if (!$user_id) {
  echo json_encode(["success" => false, "error" => "Usuario no encontrado."]);
  exit;
}

$content = trim($_POST["content"] ?? "");
if ($content === "" && empty($_FILES["images"])) {
  echo json_encode(["success" => false, "error" => "No puedes publicar vacío."]);
  exit;
}

$uploadDir = $_SERVER["DOCUMENT_ROOT"] . "/feedimgs/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$uploadedPaths = [];

if (!empty($_FILES["images"]["tmp_name"])) {
  foreach ($_FILES["images"]["tmp_name"] as $i => $tmp) {
    $name  = basename($_FILES["images"]["name"][$i]);
    $type  = mime_content_type($tmp);
    $error = $_FILES["images"]["error"][$i];

    if ($error !== UPLOAD_ERR_OK) {
      error_log("warn. Error al subir $name (código $error)");
      continue;
    }

    $allowed = ["image/jpeg", "image/png", "image/webp", "image/gif"];
    if (!in_array($type, $allowed)) {
      error_log("⚠️ Tipo no permitido: $type ($name)");
      continue;
    }

    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $filename = "feed_" . uniqid("", true) . "." . strtolower($ext);
    $dest = $uploadDir . $filename;

    if (!move_uploaded_file($tmp, $dest)) {
      if (!copy($tmp, $dest)) {
        error_log("❌ No se pudo mover/copiar $name a $dest");
        continue;
      }
    }

    if (file_exists($dest)) {
      $uploadedPaths[] = "/feedimgs/" . $filename;
      error_log("ok! Subido correctamente: $filename");
    } else {
      error_log("warn. Archivo no encontrado después de mover: $dest");
    }
  }
}

try {
  $stmt = $pdo->prepare("
    INSERT INTO feed_posts (user_id, content, image, visibility, created_at)
    VALUES (?, ?, ?, 'public', NOW())
  ");
  $stmt->execute([
    $user_id,
    $content,
    json_encode($uploadedPaths, JSON_UNESCAPED_SLASHES)
  ]);

  $post_id = $pdo->lastInsertId();

  $stmt = $pdo->prepare("
    SELECT fp.id, fp.content, fp.image, fp.created_at, fp.visibility,
           u.username,
           COALESCE(u.profile_pic, '/assets/default.png') AS profile_pic
    FROM feed_posts fp
    JOIN users u ON u.id = fp.user_id
    WHERE fp.id = ?
  ");
  $stmt->execute([$post_id]);
  $post = $stmt->fetch(PDO::FETCH_ASSOC);

  $post["image"] = json_decode($post["image"], true);

  echo json_encode(["success" => true, "post" => $post]);
} catch (Exception $e) {
  echo json_encode(["success" => false, "error" => "Error BD: " . $e->getMessage()]);
}
