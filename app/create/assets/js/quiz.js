document.addEventListener("DOMContentLoaded", () => {
  const progressBar      = document.getElementById("progressBar");
  const loadingSection   = document.getElementById("loadingSection");
  const quizForm         = document.getElementById("quizForm");
  const questionsContainer = document.getElementById("questionsContainer");
  const checkBtn         = document.getElementById("checkAnswersBtn");
  const resultModal      = document.getElementById("resultModal");
  const resultTitle      = document.getElementById("resultTitle");
  const resultSubtitle   = document.getElementById("resultSubtitle");
  const retryQuiz        = document.getElementById("retryQuiz");
  const viewCorrection   = document.getElementById("viewCorrection");
  const shareResult      = document.getElementById("shareResult");
  const publishBtn       = document.getElementById("publishQuiz");
  const publishModal     = document.getElementById("publishModal");
  const quizTitleInput   = document.getElementById("quizTitleInput");
  const confirmPublish   = document.getElementById("confirmPublish");
  const cancelPublish    = document.getElementById("cancelPublish");

  let questions   = [];
  let userAnswers = [];
  let score       = 0;
  let correction  = false;

  fetch(`/create/generate_quiz.php?file=${encodeURIComponent(FILENAME)}`)
    .then(r => r.json())
    .then(data => {
      if (!data.success) {
        loadingSection.innerHTML = `<p style="color:#ff4444">${data.error}</p>`;
        return;
      }

      questions = data.questions;
      renderQuiz(questions);
      loadingSection.classList.add("hidden");
      quizForm.classList.remove("hidden");
      updateProgress();
    })
    .catch(() => {
      loadingSection.innerHTML = `<p style="color:#ff4444">Error al conectar con el servidor.</p>`;
    });

  function renderQuiz(list) {
    questionsContainer.innerHTML = "";
    userAnswers = new Array(list.length).fill(null);

    list.forEach((q, i) => {
      const block = document.createElement("div");
      block.className = "question-block";
      block.innerHTML = `
        <h3>${i + 1}. ${escapeHTML(q.question)}</h3>
        <div class="options">
          ${q.options.map((opt, j) => `
            <label class="option">
              <input type="radio" name="q${i}" value="${j}">
              <span>${String.fromCharCode(65+j)}. ${escapeHTML(opt)}</span>
            </label>
          `).join("")}
        </div>
      `;
      questionsContainer.appendChild(block);
    });
  }

  quizForm.addEventListener("change", e => {
    if (e.target.matches('input[type="radio"]')) {
      const idx = parseInt(e.target.name.replace("q",""), 10);
      userAnswers[idx] = parseInt(e.target.value, 10);
      updateProgress();
    }
  });

  function updateProgress() {
    const answered = userAnswers.filter(v => v !== null).length;
    const pct = Math.round((answered / (questions.length || 1)) * 100);
    progressBar.style.width = `${pct}%`;
    checkBtn.disabled = answered !== questions.length;
  }

  checkBtn.addEventListener("click", () => {
    score = 0;
    questions.forEach((q, i) => {
      if (userAnswers[i] === q.correct) score++;
    });

    resultTitle.textContent = `Tu resultado: ${score}/${questions.length}`;
    resultSubtitle.textContent = `¡Buen trabajo!`;
    resultModal.classList.add("active");
  });

  retryQuiz.addEventListener("click", () => {
    resultModal.classList.remove("active");
    correction = false;
    renderQuiz(questions);
    progressBar.style.width = "0%";
  });

  viewCorrection.addEventListener("click", () => {
    resultModal.classList.remove("active");
    correction = true;
    renderCorrection();
  });

  shareResult.addEventListener("click", async () => {
    const text = `He obtenido ${score}/${questions.length} en mi quiz de RogeX 🔥`;
    if (navigator.share) {
      try { await navigator.share({ title: "RogeX — Resultado", text }); }
      catch {}
    } else {
      await navigator.clipboard.writeText(text);
      alert("Resultado copiado al portapapeles.");
    }
  });

  publishBtn.addEventListener("click", () => {
    resultModal.classList.remove("active");
    quizTitleInput.value = ""; // limpiar
    publishModal.classList.add("active");
  });

  cancelPublish.addEventListener("click", () => {
    publishModal.classList.remove("active");
  });

  confirmPublish.addEventListener("click", async () => {
    const title = quizTitleInput.value.trim();
    if (!title) {
      alert("Por favor, escribe un título para el quiz.");
      return;
    }

    const res = await fetch("/create/publish_quiz.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ filename: FILENAME, title })
    });

    const data = await res.json();
    if (data.success) {
      publishModal.classList.remove("active");
      alert("¡Tu quiz ha sido publicado correctamente!");
      window.location.href = `/quiz/${data.quiz_id}`;
    } else {
      alert("Error al publicar el quiz: " + data.error);
    }
  });

  function renderCorrection() {
    questionsContainer.innerHTML = "";
    questions.forEach((q, i) => {
      const block = document.createElement("div");
      block.className = "question-block";
      const opts = q.options.map((opt, j) => {
        const isCorrect = j === q.correct;
        const isChosen  = userAnswers[i] === j;
        const cls = isCorrect
          ? 'style="border-color:#1e8f4d;background:#102d1e"'
          : (isChosen ? 'style="border-color:#8f1e1e;background:#2d1010"' : '');
        return `<div class="option" ${cls}><span>${String.fromCharCode(65+j)}. ${escapeHTML(opt)}</span></div>`;
      }).join("");
      block.innerHTML = `<h3>${i + 1}. ${escapeHTML(q.question)}</h3><div class="options">${opts}</div>`;
      questionsContainer.appendChild(block);
    });
  }

  function escapeHTML(s) {
    return String(s).replace(/[&<>"']/g, m => (
      { '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]
    ));
  }
});
