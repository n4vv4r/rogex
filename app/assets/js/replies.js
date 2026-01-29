window.addEventListener("DOMContentLoaded", () => {
  const repliesContainer = document.getElementById("repliesContainer");
  const replyForm = document.getElementById("replyForm");
  const replyContent = document.getElementById("replyContent");

  async function loadReplies() {
    repliesContainer.innerHTML = `<p style="color:#777;">Cargando respuestas...</p>`;
    try {
      const res = await fetch(`/post/get_replies.php?id=${POST_ID}`);
      const data = await res.json();

      if (!data.success || !data.replies.length) {
        repliesContainer.innerHTML = `<p style="color:#777;">Aún no hay respuestas.</p>`;
        return;
      }

      repliesContainer.innerHTML = "";
      data.replies.forEach(r => {
        const el = document.createElement("div");
        el.className = "reply";
        el.innerHTML = `
          <div class="reply-header">
            <img src="${r.profile_pic}" alt="@${r.username}">
            <span class="username">@${r.username}</span>
          </div>
          <div class="reply-content">${r.content}</div>
          <div class="reply-time">${r.created_at}</div>
        `;
        repliesContainer.appendChild(el);
      });
    } catch (err) {
      repliesContainer.innerHTML = `<p style="color:#f55;">Error al cargar respuestas.</p>`;
      console.error(err);
    }
  }

  replyForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const content = replyContent.value.trim();
    if (!content) return;

    replyContent.disabled = true;
    try {
      const res = await fetch("/post/add_reply.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ post_id: POST_ID, content })
      });
      const data = await res.json();
      if (data.success) {
        replyContent.value = "";
        loadReplies();
      } else {
        alert(data.error || "Error al enviar respuesta.");
      }
    } catch (err) {
      alert("Error de conexión.");
    } finally {
      replyContent.disabled = false;
    }
  });

  loadReplies();
});
