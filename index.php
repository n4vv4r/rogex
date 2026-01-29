<?php
require_once __DIR__ . "/config.php";
$flash = null;
if (!empty($_SESSION['flash_msg'])) {
  $flash = $_SESSION['flash_msg'];
  unset($_SESSION['flash_msg']);
}
$username = isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']) : null;

if ($username) {
  header("Location: " . APP_URL . "/");
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>RogeX — Coming Soon</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" href="apple-touch-icon.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="RogeX — Convierte tus PDFs en quizzes interactivos con IA. Coming soon.">
  <meta name="keywords" content="RogeX, IA, Inteligencia Artificial, PDFs, Quizzes, Interactivo, Educación, Aprendizaje">
  <meta name="author" content="RogeX">
  <meta name="robots" content="index, follow">
  <meta property="og:title" content="RogeX" />
  <meta property="og:description" content="RogeX — Convierte tus PDFs en quizzes interactivos con IA. Coming soon." />
  <meta property="og:url" content="https://www.rogex.net" />
  <meta property="og:type" content="website" />
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="RogeX" />
  <meta name="twitter:description" content="RogeX — Convierte tus PDFs en quizzes interactivos con IA. Coming soon." />
  <meta name="twitter:url" content="https://www.rogex.net" />
  <meta name="twitter:creator" content="@RogeXnet" />
  <link rel="icon" href="favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" href="apple-touch-icon.png">

  <style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

    html, body {
      height: 100%;
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      background-color: #0f0f0f;
      color: #f0f0f0;
      overflow: hidden;
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(10px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeBg {
      from { opacity: 0; }
      to   { opacity: 1; }
    }

    @keyframes glowPulse {
      0%   { color: #f0f0f0; text-shadow: none; }
      20%  { color: #bfffc1; text-shadow: 0 0 6px #00ff0066, 0 0 12px #00ff2688; }
      35%  { color: #4fc549; text-shadow: 0 0 12px #7ae178cc, 0 0 32px #00ff11cc, 0 0 48px #00ff26cc; }
      50%  { color: #bfffc9; text-shadow: 0 0 6px #00ff1166, 0 0 12px #00ff4088; }
      70%, 100% { color: #f0f0f0; text-shadow: none; }
    }

    .topbar {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 64px;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 1.4rem;
      background: rgba(15,15,15,0.6);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid #1a1a1a;
      z-index: 100;
      animation: fadeInUp 1.3s ease forwards;
      opacity: 0;
    }

    .welcome {
      color: #ccc;
      font-size: 1rem;
      font-weight: 500;
      letter-spacing: 0.02em;
    }

    .logout-btn {
      color: #00ff66;
      text-decoration: none;
      border: 2px solid #00ff66;
      padding: 0.5rem 1rem;
      border-radius: 9999px;
      font-weight: 700;
      transition: all 0.25s ease;
    }

    .logout-btn:hover {
      background: rgba(0,255,100,0.1);
      box-shadow: 0 0 12px rgba(0,255,100,0.4);
      transform: translateY(-2px);
    }

    .logout-btn:active {
      transform: scale(0.97);
      box-shadow: 0 0 20px rgba(0,255,100,0.6);
    }

    .container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100%;
      text-align: center;
      animation: fadeBg 2s ease forwards;
    }

    h1 {
      font-size: 4rem;
      font-weight: 700;
      letter-spacing: 0.06em;
      margin-bottom: 1rem;
      opacity: 0;
      animation: fadeInUp 1.4s ease forwards;
      animation-delay: 0.4s;
      display: inline-flex;
      gap: 0.02em;
    }

    h1 span {
      display: inline-block;
      will-change: color, text-shadow;
      animation: glowPulse 16s linear infinite both;
      animation-delay: calc(var(--i) * 0.1s);
    }

    p {
      font-size: 1.2rem;
      color: #bbb;
      max-width: 520px;
      line-height: 1.6;
      opacity: 0;
      animation: fadeInUp 1.4s ease forwards;
      animation-delay: 0.9s;
      padding: 0 1rem;
    }

    .cta-container {
      display: flex;
      gap: 1rem;
      margin-top: 1.8rem;
      opacity: 0;
      animation: fadeInUp 1.4s ease forwards;
      animation-delay: 1.3s;
    }

    .cta {
      display: inline-block;
      padding: 1rem 2.2rem;
      border-radius: 9999px;
      font-weight: 800;
      font-size: 1.05rem;
      letter-spacing: 0.03em;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.25s ease;
    }

    .cta.register {
      background: linear-gradient(135deg, #00ff66, #00cc55);
      color: #0b0b0b;
      border: none;
      box-shadow: 0 0 0 rgba(0,255,100,0);
    }

    .cta.login {
      background: transparent;
      color: #00ff66;
      border: 2px solid #00ff66;
      box-shadow: 0 0 0 rgba(0,255,100,0);
    }

    .cta.register:hover {
      filter: brightness(1.1);
      transform: translateY(-2px);
      box-shadow: 0 0 16px rgba(0,255,100,0.4);
    }

    .cta.login:hover {
      color: #00ff66;
      background: rgba(0,255,100,0.1);
      box-shadow: 0 0 16px rgba(0,255,100,0.4);
      transform: translateY(-2px);
    }

    .cta.register:active,
    .cta.login:active {
      transform: translateY(1px);
      box-shadow: 0 0 24px rgba(0,255,100,0.6);
    }

    .note {
      color: #9b9b9b;
      margin-top: .7rem;
      max-width: 520px;
    }

    .footer {
      width: 100vw;
      position: fixed;
      left: 0;
      bottom: 0;
      background: rgba(15, 15, 15, 0.96);
      color: #aaa;
      font-size: 0.98rem;
      padding: 0.7rem 0;
      text-align: center;
      z-index: 10;
      letter-spacing: 0.01em;
      border-top: 1px solid #222;
      box-shadow: 0 -2px 16px 0 #000a;
    }
    .footer-content {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.2em;
    }
    .footer-mail {
      color: #33d10f;
      text-decoration: none;
      font-weight: 500;
      transition: color 0.2s;
    }
    .footer-mail:hover {
      color: #fff;
      text-decoration: underline;
    }
    .footer-copy {
      font-size: 0.93em;
      color: #888;
      margin-top: 0.1em;
    }

    .notif {
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      padding: 1rem 1.4rem;
      border-radius: 10px;
      font-weight: 600;
      box-shadow: 0 0 20px rgba(0,255,100,0.3);
      opacity: 0;
      animation: fadeInOut 4.5s ease forwards;
      z-index: 1000;
    }
    .notif.success {
      background: rgba(20,20,20,0.95);
      border: 1px solid #00ff66;
      color: #00ff66;
    }
    .notif.error {
      background: rgba(20,0,0,0.95);
      border: 1px solid #ff4444;
      color: #ff4444;
      box-shadow: 0 0 20px rgba(255,68,68,0.3);
    }

    @keyframes fadeInOut {
      0% { opacity: 0; transform: translate(-50%, -10px); }
      10% { opacity: 1; transform: translate(-50%, 0); }
      80% { opacity: 1; transform: translate(-50%, 0); }
      100% { opacity: 0; transform: translate(-50%, -10px); }
    }

    @media (max-width: 600px) {
      h1 { font-size: 2.6rem; }
      p { font-size: 1rem; }
      .footer { font-size: 0.85rem; padding: 0.5rem 0; }
      .cta-container { flex-direction: column; }
      .cta { width: 100%; text-align: center; }
      .topbar { flex-direction: column; height: auto; padding: .8rem 0; gap: .4rem; }
    }
    a {
        color: green;
    }
    a.visited {
        color: green;
    }
  </style>
</head>
<body>
  <?php if ($username): ?>
  <header class="topbar">
    <p class="welcome">Hola, <b>@<?= $username ?></b></p>
    <a class="logout-btn" href="/logout.php">Cerrar sesión</a>
  </header>
  <?php endif; ?>

  <?php if ($flash): ?>
    <div class="notif <?= $flash['type'] ?>" id="notif">
      <?= htmlspecialchars($flash['text']) ?>
    </div>
  <?php endif; ?>

  <div class="container">
    <h1 aria-label="RogeX">
<img src="rogexpng.png" width="330px">
    </h1>
    <p>Convierte tus apuntes y PDFs en experiencias de aprendizaje interactivas impulsadas por IA.</p>

    <?php if (!$username): ?>
      <div class="cta-container">
        <a class="cta register" href="/register">💚 Pre-registrarme</a>
        <a class="cta login" href="/login">Iniciar sesión</a>
      </div>
      <p class="note">Los pre-registrados ahora tendrán <b>todas las funciones premium</b> gratis en el lanzamiento.</p>
    <?php else: ?>
      <p class="note">Gracias por unirte al pre-lanzamiento. Te avisaremos por email cuando abramos.</p>
    <?php endif; ?>
  </div>

  <footer class="footer">
    <div class="footer-content">
      <a href="mailto:admin@rogex.net" class="footer-mail">admin@rogex.net</a>
      <span class="footer-copy">&copy; RogeX 2025-2026</span><span><a href="https://about.rogex.net">Sobre Rogex</a></span>
    </div>
  </footer>

  <script>
    window.addEventListener("load", () => {
      const n = document.getElementById("notif");
      if (n) setTimeout(() => n.remove(), 5000);
    });
  </script>
</body>
</html>
