<?php
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../includes/auth_check.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: /u/settings/index.php");
  exit;
}

$username = $_SESSION['user'];

$new_fullname = trim($_POST['fullname'] ?? '');
$new_username = trim($_POST['username'] ?? '');
$new_bio = trim($_POST['bio'] ?? '');
$insta = trim($_POST['link_instagram'] ?? '');
$twitter = trim($_POST['link_twitter'] ?? '');
$linkedin = trim($_POST['link_linkedin'] ?? '');
$github = trim($_POST['link_github'] ?? '');
$website = trim($_POST['link_website'] ?? '');

if (!preg_match('/^(?!.*\.\.)[A-Za-z0-9._]{4,}$/', $new_username)) {
  $_SESSION['flash_msg'] = [
    'type' => 'error',
    'text' => 'El nombre de usuario contiene caracteres inválidos o es demasiado corto.'
  ];
  header("Location: /u/settings/");
  exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND username != ?");
$stmt->execute([$new_username, $username]);
if ($stmt->fetchColumn() > 0) {
  $_SESSION['flash_msg'] = [
    'type' => 'error',
    'text' => 'El nombre de usuario ya está en uso.'
  ];
  header("Location: /u/settings/");
  exit;
}

$stmt = $pdo->prepare("
  UPDATE users 
  SET fullname = ?, username = ?, bio = ?, 
      link_instagram = ?, link_twitter = ?, link_linkedin = ?, link_github = ?, link_website = ?
  WHERE username = ?
");
$stmt->execute([
  $new_fullname,
  $new_username,
  $new_bio,
  $insta,
  $twitter,
  $linkedin,
  $github,
  $website,
  $username
]);

if ($new_username !== $username) {
  $_SESSION['user'] = $new_username;
}

$_SESSION['flash_msg'] = [
  'type' => 'success',
  'text' => 'Perfil actualizado correctamente ✅'
];

header("Location: /u/$new_username");
exit;