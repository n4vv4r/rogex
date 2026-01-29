document.addEventListener("DOMContentLoaded", () => {
  const popup = document.getElementById("popup");
  const openBtn = document.getElementById("openPopup");
  const closeBtn = document.getElementById("closePopup");

  if (openBtn && popup) {
    openBtn.addEventListener("click", () => {
      popup.style.display = "flex";
      setTimeout(() => popup.style.opacity = "1", 10);
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener("click", () => {
      popup.style.opacity = "0";
      setTimeout(() => popup.style.display = "none", 200);
    });
  }

  // cerrar al hacer click fuera del popup
  window.addEventListener("click", (e) => {
    if (e.target === popup) {
      popup.style.opacity = "0";
      setTimeout(() => popup.style.display = "none", 200);
    }
  });

  // Envio de AJAX del formulario
  const form = document.getElementById("registerForm");
  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const formData = new FormData(form);
      const res = await fetch("register.php", { method: "POST", body: formData });
      const html = await res.text();
      document.getElementById("formResponse").innerHTML = html;
    });
  }
});
