<?php
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../includes/auth_check.php";

$q = trim($_GET['q'] ?? '');
if (!$q) exit;

$search = "%$q%";
$stmt = $pdo->prepare("SELECT username, fullname, profile_pic FROM users WHERE username LIKE ? OR fullname LIKE ? LIMIT 5");
$stmt->execute([$search, $search]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$users) {
  echo "<p class='no-results'>No se encontraron resultados.</p>";
  exit;
}

foreach ($users as $u):
  $pic = !empty($u['profile_pic']) && $u['profile_pic'] !== 'default.png'
    ? '/assets/uploads/users/' . htmlspecialchars($u['profile_pic'])
    : '/assets/default.png';
?>
  <a href="/u/<?= urlencode($u['username']) ?>" class="result-item">
    <img src="<?= $pic ?>" alt="">
    <div class="info">
      <span class="fullname"><?= htmlspecialchars($u['fullname'] ?: $u['username']) ?></span>
      <span class="username">@<?= htmlspecialchars($u['username']) ?></span>
    </div>
  </a>
<?php endforeach; ?>
