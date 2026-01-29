<?php
require_once __DIR__ . "/../config.php"; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Sobre RogeX — Conocenos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Conoce la historia y visión detrás de RogeX. IA para el aprendizaje inteligente.">
  <link rel="icon" href="favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" href="apple-touch-icon.png">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    html, body {
      height: 100%;
      background-color: #0f0f0f;
      color: #f0f0f0;
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      scroll-behavior: smooth;
    }

    header {
      position: sticky;
      top: 0;
      z-index: 50;
      background: rgba(15, 15, 15, 0.85);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid #1a1a1a;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      font-size: 1.6rem;
      font-weight: 700;
      letter-spacing: 0.05em;
      display: inline-flex;
      gap: 0.02em;
      cursor: pointer;
      text-decoration: none;
      color: #f0f0f0;
    }
    .logo span {
      display: inline-block;
      animation: glowPulse 16s linear infinite both;
      animation-delay: calc(var(--i) * 0.1s);
    }

    @keyframes glowPulse {
      0%, 100% { color: #f0f0f0; text-shadow: none; }
      35% { color: #4fc549; text-shadow: 0 0 12px #00ff26aa; }
    }

    nav a {
      margin-left: 1.5rem;
      text-decoration: none;
      color: #bbb;
      font-weight: 500;
      transition: color 0.3s;
    }
    nav a:hover { color: #00ff66; }

    .hero {
      text-align: center;
      padding: 6rem 2rem 4rem;
      background: radial-gradient(circle at 50% 0%, rgba(0,255,100,0.05), transparent 70%);
      animation: fadeIn 1.4s ease forwards;
    }

    .hero h1 {
      font-size: 3rem;
      font-weight: 800;
      margin-bottom: 1rem;
      letter-spacing: 0.03em;
    }

    .hero p {
      font-size: 1.15rem;
      color: #aaa;
      max-width: 680px;
      margin: 0 auto;
      line-height: 1.7;
    }

    section {
      padding: 5rem 2rem;
      max-width: 1000px;
      margin: 0 auto;
    }

    h2 {
      font-size: 2rem;
      margin-bottom: 1rem;
      color: #00ff66;
    }

    p {
      line-height: 1.7;
      color: #ccc;
      font-size: 1.05rem;
      margin-bottom: 1rem;
    }

    .team {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 2rem;
      margin-top: 3rem;
    }

    .member {
      background: rgba(18,18,18,0.9);
      border: 1px solid #1e1e1e;
      border-radius: 14px;
      padding: 2rem;
      max-width: 280px;
      text-align: center;
      box-shadow: 0 0 24px rgba(0,255,100,0.08);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .member:hover {
      transform: translateY(-6px);
      box-shadow: 0 0 24px rgba(0,255,100,0.25);
    }

    .member img {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 1rem;
      border: 2px solid #00ff66;
    }

    .member h3 {
      color: #f0f0f0;
      font-size: 1.2rem;
      margin-bottom: 0.3rem;
    }

    .member p.role {
      color: #00ff66;
      font-size: 0.9rem;
      margin-bottom: 1rem;
    }

    .member .socials a {
      color: #bbb;
      margin: 0 0.4rem;
      font-size: 1.1rem;
      text-decoration: none;
      transition: color 0.3s;
    }

    .member .socials a:hover {
      color: #00ff66;
    }

    footer {
      background: #0b0b0b;
      text-align: center;
      padding: 2rem;
      border-top: 1px solid #1a1a1a;
      color: #888;
      font-size: 0.95rem;
    }

    footer a {
      color: #00ff66;
      text-decoration: none;
      font-weight: 500;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 700px) {
      .hero h1 { font-size: 2.4rem; }
      .member { max-width: 90%; }
    }
  </style>
</head>
<body>

<header>
  <a href="/" class="logo">
    <span style="--i:0">R</span>
    <span style="--i:1">o</span>
    <span style="--i:2">g</span>
    <span style="--i:3">e</span>
    <span style="--i:4">X</span>
  </a>
  <nav>
    <a href="https://rogex.net">Inicio</a>
        <a href="https://rogex.net/register">Registrate hoy</a>

  </nav>
</header>

<section class="hero">
  <h1>Impulsando el aprendizaje con IA</h1>
  <p>RogeX transforma tus apuntes y documentos PDF en experiencias de aprendizaje interactivas, impulsadas por inteligencia artificial. Diseñado para estudiantes, educadores y autodidactas que buscan aprender de forma más eficiente.</p>
</section>

<section>
  <h2>Nuestra misión</h2>
  <p>En RogeX creemos que el conocimiento debe ser accesible, interactivo y personalizado. Nuestra meta es reinventar la manera en que las personas estudian, transformando la lectura pasiva en práctica activa con IA.</p>
  <p>Estamos construyendo una plataforma donde tus materiales se convierten automáticamente en cuestionarios, flashcards y herramientas de repaso, adaptadas a tu estilo de aprendizaje.</p>
</section>

<section>
  <h2>El equipo</h2>
  <div class="team">
    <div class="member">
      <img src="https://app.rogex.net/assets/uploads/users/profile_pic_68f3c1baa13cb.jpg" alt="pfp">
      <a href="https://app.rogex.net/u/navarro"><h3>Roger</h3></a>
      <p class="role">Fundador y desarrollador principal</p>
      <p>Apasionado por la ciberseguridad, la inteligencia artificial y el aprendizaje automatizado. RogeX nace del deseo de fusionar tecnología y educación.</p>
      <div class="socials">
        <a href="https://github.com/n4vv4r"><i class="fab fa-github"></i></a>
      </div>
    </div>

    
  </div>
</section>

<footer>
  <p>&copy; <?= date('Y') ?> RogeX — Todos los derechos reservados.</p>
  <p><a href="mailto:admin@rogex.net">admin@rogex.net</a></p>
</footer>

<script src="https://kit.fontawesome.com/a2d9d6cfd7.js" crossorigin="anonymous"></script>

</body>
</html>