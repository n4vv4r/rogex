<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/includes/auth_check.php";

$stmt = $pdo->prepare("SELECT username, profile_pic FROM users WHERE username = ?");
$stmt->execute([$_SESSION['user']]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

$user = htmlspecialchars($userData['username']);
$profile_pic = (!empty($userData['profile_pic']) && $userData['profile_pic'] !== 'default.png')
  ? "/assets/uploads/users/" . htmlspecialchars($userData['profile_pic'])
  : "/assets/default.png";

$q = $_GET['q'] ?? '';
$results = [];

if ($q) {
  $searchTerm = '%' . $q . '%';
  $stmt = $pdo->prepare("SELECT username, fullname, profile_pic FROM users WHERE username LIKE ? OR fullname LIKE ? LIMIT 5");
  $stmt->execute([$searchTerm, $searchTerm]);
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Búsqueda — RogeX</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: #0f0f0f;
      color: #f0f0f0;
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      overflow-x: hidden;
      min-height: 100vh;
    }

    .navbar {
      position: fixed;
      top: 0; left: 0;
      width: 100%;
      height: 64px;
      background: rgba(15,15,15,0.7);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid #1a1a1a;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 1.5rem;
      z-index: 100;
    }

    .logo img {
      width: 60px;
      cursor: pointer;
    }

    .search-bar {
      flex: 1;
      max-width: 500px;
      margin: 0 auto;
      position: relative;
    }

    .search-bar input {
      width: 100%;
      padding: 0.7rem 1rem 0.7rem 2.5rem;
      border-radius: 9999px;
      border: 2px solid #00ff66;
      background: rgba(20,20,20,0.85);
      color: #f0f0f0;
      outline: none;
      transition: all 0.25s ease;
      font-size: 1rem;
    }

    .search-bar input:focus {
      background: rgba(25,25,25,0.9);
      box-shadow: 0 0 15px rgba(0,255,100,0.2);
    }

    .search-icon {
      position: absolute;
      top: 50%;
      left: 12px;
      transform: translateY(-50%);
      color: #00ff66;
    }

    .results-container {
      margin-top: 90px;
      max-width: 600px;
      margin-inline: auto;
      background: rgba(18,18,18,0.9);
      border: 1px solid #222;
      border-radius: 16px;
      padding: 1rem;
      box-shadow: 0 0 24px rgba(0,255,100,0.1);
    }

    .result-item {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 0.6rem 0.8rem;
      border-radius: 10px;
      transition: all 0.25s ease;
      text-decoration: none;
      color: #f0f0f0;
    }

    .result-item:hover {
      background: rgba(0,255,100,0.08);
    }

    .result-item img {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      object-fit: cover;
    }

    .result-item .info {
      display: flex;
      flex-direction: column;
    }

    .result-item .info .fullname {
      font-weight: 600;
      color: #00ff66;
    }

    .result-item .info .username {
      color: #aaa;
      font-size: 0.9rem;
    }

    .recent-container {
      position: absolute;
      top: 115%;
      left: 0;
      width: 100%;
      background: rgba(15,15,15,0.95);
      border: 1px solid #222;
      border-radius: 10px;
      box-shadow: 0 4px 18px rgba(0,255,100,0.1);
      overflow: hidden;
      display: none;
      z-index: 200;
    }

    .recent-container.active { display: block; }

    .recent-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.6rem 1rem;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .recent-item:hover {
      background: rgba(0,255,100,0.08);
    }

    .recent-item span {
      color: #ccc;
    }

    .recent-item .remove {
      color: #00ff66;
      cursor: pointer;
      font-weight: bold;
    }

    .no-results {
      text-align: center;
      color: #999;
      padding: 1rem;
      font-size: 1rem;
    }

    .menu {
      position: relative;
    }

    .menu-btn {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      border: 2px solid #00ff66;
      overflow: hidden;
      background: #0f0f0f;
      cursor: pointer;
      transition: all 0.25s ease;
    }

    .menu-btn img {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      object-fit: cover;
    }

    .menu-content {
      position: absolute;
      top: 110%;
      right: 0;
      background: rgba(15,15,15,0.95);
      border: 1px solid #1a1a1a;
      border-radius: 12px;
      display: none;
      flex-direction: column;
      overflow: hidden;
      min-width: 180px;
    }

    .menu.open .menu-content { display: flex; }

    .menu-content a {
      color: #ccc;
      text-decoration: none;
      padding: 0.8rem 1rem;
      transition: 0.25s;
    }

    .menu-content a:hover {
      background: rgba(0,255,100,0.08);
      color: #00ff66;
    }
  </style>
</head>
<body>

  <nav class="navbar">
    <div class="logo" onclick="window.location.href='https://app.rogex.net'">
      <img src="apple-touch-icon.png" alt="RogeX">
    </div>

    <div class="search-bar">
      <i class="fas fa-search search-icon"></i>
      <input type="text" id="searchInput" placeholder="Buscar usuarios..." value="<?= htmlspecialchars($q) ?>">
      <div class="recent-container" id="recentContainer"></div>
    </div>

    <div class="menu" id="menu">
      <button class="menu-btn"><img src="<?= $profile_pic ?>" alt="Perfil"></button>
      <div class="menu-content">
        <a href="/u/<?= urlencode($user) ?>">Perfil</a>
        <a href="/ajustes">Ajustes</a>
        <a href="https://about.rogex.net/">Sobre RogeX</a>
        <a href="<?= BASE_URL ?>/logout.php">Cerrar sesión</a>
      </div>
    </div>
  </nav>

  <main class="results-container">
    <?php if ($q && empty($results)): ?>
      <p class="no-results">No se encontraron resultados.</p>
    <?php elseif ($results): ?>
      <?php foreach ($results as $u): ?>
        <a href="/u/<?= urlencode($u['username']) ?>" class="result-item">
          <img src="<?= $u['profile_pic'] && $u['profile_pic'] !== 'default.png' ? '/assets/uploads/users/' . htmlspecialchars($u['profile_pic']) : '/assets/default.png' ?>" alt="">
          <div class="info">
            <span class="fullname"><?= htmlspecialchars($u['fullname'] ?: $u['username']) ?></span>
            <span class="username">@<?= htmlspecialchars($u['username']) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="no-results">Busca un usuario.</p>
    <?php endif; ?>
  </main>

  <script>
    const searchInput = document.getElementById('searchInput');
    const recentContainer = document.getElementById('recentContainer');

    function getHistory() {
      return JSON.parse(localStorage.getItem('searchHistory') || '[]');
    }
    function saveHistory(history) {
      localStorage.setItem('searchHistory', JSON.stringify(history.slice(0,5)));
    }
    function addToHistory(query) {
      if (!query.trim()) return;
      let history = getHistory();
      history = history.filter(q => q !== query);
      history.unshift(query);
      saveHistory(history);
    }
    function removeFromHistory(query) {
      let history = getHistory().filter(q => q !== query);
      saveHistory(history);
      renderHistory();
    }

    function renderHistory() {
      const history = getHistory();
      if (!history.length) { recentContainer.classList.remove('active'); return; }
      recentContainer.innerHTML = history.map(q => `
        <div class="recent-item">
          <span onclick="selectHistory('${q}')">${q}</span>
          <span class="remove" onclick="removeFromHistory('${q}')">×</span>
        </div>
      `).join('');
      recentContainer.classList.add('active');
    }

    function selectHistory(q) {
      searchInput.value = q;
      window.location.href = '/search.php?q=' + encodeURIComponent(q);
    }

    searchInput.addEventListener('focus', renderHistory);
    searchInput.addEventListener('blur', () => setTimeout(() => recentContainer.classList.remove('active'), 200));

    searchInput.addEventListener('keydown', e => {
      if (e.key === 'Enter') {
        e.preventDefault();
        const q = searchInput.value.trim();
        if (q) {
          addToHistory(q);
          window.location.href = '/search.php?q=' + encodeURIComponent(q);
        }
      }
    });

    const menu = document.getElementById("menu");
    const btn = menu.querySelector(".menu-btn");
    btn.addEventListener("click", () => menu.classList.toggle("open"));
    document.addEventListener("click", (e) => {
      if (!menu.contains(e.target)) menu.classList.remove("open");
    });
  </script>
</body>
</html>
