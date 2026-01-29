window.addEventListener("load", () => {
  console.log("[RogeX Feed] JS inicializado ✅");

  const $  = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));
  const safeOn = (el, ev, cb) => { if (el) el.addEventListener(ev, cb); };

  const feedContainer = $("#feedContainer");
  const postForm      = $("#postForm");
  const postContent   = $("#postContent");
  const postImages    = $("#postImages");
  const imagePreview  = $("#imagePreview");

  const modal         = $("#createModal");
  const modalContent  = $("#modalContent");
  const createBtn     = $("#createQuiz");
  const closeBtn      = $("#closeModal");
  const dropZone      = $("#dropZone");
  const pdfInput      = $("#pdfInput");
  const dragOverlay   = $("#dragOverlay");
  const progressBar   = $("#progress");
  const uploadPercent = $("#uploadPercent");

  if (postImages) {
    postImages.addEventListener("change", () => {
      const files = Array.from(postImages.files);
      imagePreview.innerHTML = "";
      if (!files.length) return;

      const layoutClass = ["one", "two", "three", "four"][Math.min(files.length, 4) - 1];
      imagePreview.className = `post-preview ${layoutClass}`;

      files.slice(0, 4).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
          const img = document.createElement("img");
          img.src = e.target.result;
          imagePreview.appendChild(img);
        };
        reader.readAsDataURL(file);
      });
    });
  }

  async function loadPosts() {
    if (!feedContainer) return;
    feedContainer.innerHTML = `<p class="loading-text">Cargando publicaciones...</p>`;

    try {
      const res = await fetch("/post_list.php", { credentials: "include" });
      const data = await res.json();
    console.log("✅ Posts recibidos:", data);

      feedContainer.innerHTML = "";

      if (!data.success || !data.posts || !data.posts.length) {
        feedContainer.innerHTML = `<p class="loading-text">No hay publicaciones aún. ¡Sé el primero!</p>`;
        return;
      }

      data.posts.forEach(post => insertPost(post, false));
    } catch (err) {
      console.error("[RogeX Feed] Error al cargar posts:", err);
      feedContainer.innerHTML = `<p class="loading-text" style="color:#ff5555;">Error al cargar publicaciones.</p>`;
    }
  }

  function insertPost(post, prepend = true) {
    if (!feedContainer) return;

    const el = document.createElement("article");
    el.className = "post";
    el.id = `post-${post.id}`;
console.log("🧩 Insertando post:", post.username, post.id);

    let imageHTML = "";
    if (post.image) {
      try {
        const images = Array.isArray(post.image) ? post.image : JSON.parse(post.image);
        if (images && images.length) {
          const layoutClass = ["one", "two", "three", "four"][Math.min(images.length, 4) - 1];
          imageHTML = `
            <div class="post-images ${layoutClass}">
              ${images.slice(0, 4).map(src => `<img src="${src}" alt="imagen">`).join("")}
            </div>`;
        }
      } catch {}
    }

    const menuHTML = post.user_id === USER_ID
      ? `
        <div class="post-menu">
          <button class="menu-btn">
            <img src="/assets/icons/opciones.png" alt="⋮" width="10px">
          </button>
          <div class="menu-options hidden">
            <button class="delete-post" data-id="${post.id}">Eliminar</button>
          </div>
        </div>`
      : "";

    const likedClass = post.liked ? "liked" : "";

  el.innerHTML = `
  <div class="post-header">
    <img src="${post.profile_pic}" alt="${post.username}" class="avatar">
    <a href="/u/${encodeURIComponent(post.username)}" class="username">@${post.username}</a>
    ${post.is_verified ? '<img src="/u/verified.png" alt="Verificado" class="badge verified">' : ''}
    ${post.is_premium ? '<img src="/u/premium.png" alt="Premium" class="badge premium">' : ''}
    ${menuHTML}
  </div>
  <div class="post-content">${escapeHTML(post.content || "").replace(/\n/g, "<br>")}</div>
  ${imageHTML}
  <div class="post-time">${formatDate(post.created_at || Date.now())}</div>
  <div class="post-actions">
    <button class="like-btn ${likedClass}" data-id="${post.id}">
      ❤️ <span class="like-count">${post.like_count || 0}</span>
    </button>
    <button class="reply-btn" data-id="${post.id}">
      💬 <span class="reply-count">${post.reply_count || 0}</span>
    </button>
  </div>`;


    if (prepend) feedContainer.prepend(el);
    else feedContainer.appendChild(el);
  }

  if (postForm && postContent) {
    postForm.addEventListener("submit", async e => {
      e.preventDefault();
      const content = postContent.value.trim();
      const files = postImages?.files || [];

      if (!content && !files.length) {
        alert("No puedes publicar vacío.");
        return;
      }

      const formData = new FormData();
      formData.append("content", content);
      for (let i = 0; i < Math.min(files.length, 4); i++) {
        formData.append("images[]", files[i]);
      }

      postContent.disabled = true;

      try {
        const res = await fetch("/post_create.php", {
          method: "POST",
          credentials: "include",
          body: formData
        });
        const data = await res.json();

        if (data.success) {
          postContent.value = "";
          postImages.value = "";
          imagePreview.innerHTML = "";
          insertPost(data.post, true);
        } else {
          alert("Error al publicar: " + (data.error || "desconocido"));
        }
      } catch (err) {
        console.error("[RogeX Feed] Error al publicar:", err);
        alert("No se pudo publicar tu mensaje.");
      } finally {
        postContent.disabled = false;
      }
    });
  }

  document.addEventListener("click", async e => {
    const delBtn = e.target.closest(".delete-post");
    if (!delBtn) return;

    const id = delBtn.dataset.id;
    if (!confirm("¿Eliminar esta publicación?")) return;

    try {
      const res = await fetch("/delete_post.php", {
        method: "POST",
        credentials: "include",
        body: new URLSearchParams({ post_id: id })
      });
      const data = await res.json();

      if (data.success) {
        $(`#post-${id}`)?.remove();
      } else {
        alert(data.error || "No se pudo eliminar la publicación.");
      }
    } catch (err) {
      console.error("[RogeX Feed] Error al eliminar:", err);
      alert("Error de conexión al eliminar el post.");
    }
  });

  document.addEventListener("click", e => {
    const btn = e.target.closest(".menu-btn");
    const menus = $$(".menu-options");

    if (btn) {
      const menu = btn.closest(".post-menu").querySelector(".menu-options");
      menus.forEach(m => m.classList.add("hidden"));
      menu.classList.toggle("hidden");
    } else {
      menus.forEach(m => m.classList.add("hidden"));
    }
  });

  document.addEventListener("click", async e => {
    const btn = e.target.closest(".like-btn");
    if (!btn) return;

    const postId = btn.dataset.id;
    try {
      const res = await fetch("/toggle_like.php", {
        method: "POST",
        credentials: "include",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ post_id: postId })
      });
      const data = await res.json();
      if (!data.success) throw new Error(data.error || "Error al hacer like");

      btn.classList.toggle("liked", data.liked);
      btn.querySelector(".like-count").textContent = data.like_count;
    } catch (err) {
      console.error(err);
      alert("No se pudo actualizar el like.");
    }
  });

  document.addEventListener("click", e => {
    const replyBtn = e.target.closest(".reply-btn");
    if (!replyBtn) return;
    const postId = replyBtn.dataset.id;
    window.location.href = `/post/${postId}`;
  });

  function escapeHTML(str) {
    return String(str).replace(/[&<>"']/g, m => ({
      "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"
    }[m]));
  }

  function formatDate(ts) {
    const d = new Date(ts);
    if (Number.isNaN(d.getTime())) return "";
    return d.toLocaleString("es-ES", {
      day: "2-digit",
      month: "short",
      hour: "2-digit",
      minute: "2-digit"
    });
  }

  loadPosts();
});
