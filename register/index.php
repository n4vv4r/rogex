<?php
require_once __DIR__ . "/../config.php";

$flash = null;
if (!empty($_SESSION['flash_msg'])) {
  $flash = $_SESSION['flash_msg'];
  unset($_SESSION['flash_msg']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>RogeX — Pre-registro</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" href="apple-touch-icon.png">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    html, body {
      min-height: 100vh;
      background: #0f0f0f;
      color: #f0f0f0;
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      overflow-x: hidden;
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(10px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .wrap {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 2rem;
    }

    .card {
      width: 100%;
      max-width: 520px;
      background: #121212;
      border: 1px solid #222;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0,0,0,.6);
      padding: 2.4rem;
      animation: fadeInUp 1.2s ease forwards;
      opacity: 0;
      animation-delay: 0.4s;
    }

    h1 {
      margin: 0 0 1rem;
      font-size: 2rem;
      color: #00ff66;
      text-align: center;
    }

    .sub {
      color: #9a9a9a;
      text-align: center;
      margin-bottom: 1.6rem;
      animation: fadeInUp 1.2s ease forwards;
      opacity: 0;
      animation-delay: 0.6s;
    }

    label {
      display: block;
      margin-top: 1rem;
      margin-bottom: .3rem;
      color: #ccc;
      font-size: .95rem;
    }

    input {
      width: 100%;
      padding: .9rem 1rem;
      border-radius: 12px;
      border: 1px solid #222;
      background: #1b1b1b;
      color: #f0f0f0;
      font-size: 1rem;
      transition: all 0.25s ease;
    }

    input:focus {
      outline: none;
      border-color: #00ff66;
      box-shadow: 0 0 8px rgba(0,255,100,0.3);
    }

    .row {
      display: flex;
      gap: .8rem;
    }

    .btn-row {
      display: flex;
      gap: 1rem;
      margin-top: 1.4rem;
      align-items: center;
      justify-content: center;
      animation: fadeInUp 1.2s ease forwards;
      opacity: 0;
      animation-delay: 0.9s;
    }

    .btn {
      flex: 1;
      padding: 1rem 2rem;
      border: none;
      border-radius: 9999px;
      background: linear-gradient(135deg, #00ff66, #00cc55);
      color: #0b0b0b;
      font-weight: 800;
      font-size: 1.05rem;
      letter-spacing: 0.03em;
      cursor: pointer;
      transition: all 0.25s ease;
      box-shadow: 0 0 0 rgba(0,255,100,0);
    }

    .btn:hover {
      filter: brightness(1.1);
      box-shadow: 0 0 16px rgba(0,255,100,0.4);
      transform: translateY(-2px);
    }

    .btn:active {
      transform: translateY(1px);
      box-shadow: 0 0 24px rgba(0,255,100,0.6);
    }

    .back-btn {
      width: 48px;
      height: 48px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      border: 2px solid #00ff66;
      color: #00ff66;
      background: transparent;
      font-size: 1.4rem;
      text-decoration: none;
      transition: all 0.25s ease;
      box-shadow: 0 0 0 rgba(0,255,100,0);
    }

    .back-btn:hover {
      background: rgba(0,255,100,0.1);
      box-shadow: 0 0 16px rgba(0,255,100,0.4);
      transform: translateY(-2px);
    }

    .back-btn:active {
      transform: scale(0.95);
      box-shadow: 0 0 24px rgba(0,255,100,0.6);
    }

    .fine {
      font-size: .85rem;
      color: #8e8e8e;
      margin-top: .8rem;
      text-align: center;
      animation: fadeInUp 1.2s ease forwards;
      opacity: 0;
      animation-delay: 1.2s;
    }

    .g-recaptcha {
      margin-top: 1.2rem;
      transform: scale(0.95);
      transform-origin: center;
      animation: fadeInUp 1.2s ease forwards;
      opacity: 0;
      animation-delay: 0.8s;
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

    .footer {
      width: 100%;
      background: rgba(15, 15, 15, 0.96);
      color: #aaa;
      font-size: 0.98rem;
      padding: 0.7rem 0;
      text-align: center;
      border-top: 1px solid #222;
      box-shadow: 0 -2px 16px 0 #000a;
      animation: fadeInUp 1.2s ease forwards;
      opacity: 0;
      animation-delay: 1.4s;
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

    @media (max-width: 600px) {
      .card { padding: 1.8rem; }
      .btn { font-size: 1rem; padding: .9rem; }
      .footer { font-size: 0.85rem; padding: 0.5rem 0; }
      .btn-row { flex-direction: column; }
      .back-btn { width: 44px; height: 44px; margin-bottom: .6rem; }
    }
  </style>
</head>
<body>

  <?php if ($flash): ?>
    <div class="notif <?= $flash['type'] ?>" id="notif">
      <?= htmlspecialchars($flash['text']) ?>
    </div>
  <?php endif; ?>

  <div class="wrap">
    <form class="card" method="post" action="/register/submit.php" novalidate>
      <h1>Pre-registro</h1>
      <p class="sub">Regístrate ahora y tendrás <b>todas las funciones premium</b> gratis en el lanzamiento.</p>

      <label>Nombre de usuario</label>
      <input name="username" placeholder="ej: navarro" required>

      <label>Nombre completo</label>
      <input name="fullname" placeholder="Tu nombre para mostrar" required>

      <label>Correo electrónico</label>
      <input type="email" name="email" placeholder="tucorreo@ejemplo.com" required>

      <div class="row">
        <div style="flex:1">
          <label>Contraseña</label>
          <input type="password" name="password" placeholder="Mín. 8 caracteres" required>
        </div>
        <div style="flex:1">
          <label>Repite contraseña</label>
          <input type="password" name="confirm_password" required>
        </div>
      </div>

      <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars(RECAPTCHA_SITE_KEY) ?>"></div>

      <div class="btn-row">
        <a href="../" class="back-btn" title="Volver al inicio">&#x2039;</a>
        <button class="btn" type="submit">Crear cuenta</button>
      </div>

      <p class="fine">Al registrarte aceptas recibir un correo de verificación.</p>
    </form>
  </div>

  <footer class="footer">
    <div class="footer-content">
      <a href="mailto:admin@rogex.net" class="footer-mail">admin@rogex.net</a>
      <span class="footer-copy">&copy; RogeX 2025-2026</span>
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
