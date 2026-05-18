/* Catálogo — Vanilla JS */
'use strict';

// ── Preview de imagen antes de subir ────────────────────
(function () {
  const input   = document.getElementById('imagenInput');
  const preview = document.getElementById('imgPreview');
  if (!input || !preview) return;

  input.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) { preview.style.display = 'none'; return; }

    const reader = new FileReader();
    reader.onload = e => {
      preview.src = e.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
  });
})();

// ── Confirmar eliminación ────────────────────────────────
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', function (e) {
    if (!confirm(this.dataset.confirm || '¿Confirmar eliminación?')) {
      e.preventDefault();
    }
  });
});

// ── Auto-hide alertas después de 4 s ────────────────────
document.querySelectorAll('.alert-ios').forEach(el => {
  setTimeout(() => {
    el.style.transition = 'opacity .5s';
    el.style.opacity = '0';
    setTimeout(() => el.remove(), 500);
  }, 4000);
});

// ── Ripple touch en cards ────────────────────────────────
document.querySelectorAll('.card-ios').forEach(card => {
  card.addEventListener('touchstart', function () {
    this.style.transform = 'scale(.97)';
  }, { passive: true });
  card.addEventListener('touchend', function () {
    this.style.transform = '';
  }, { passive: true });
});

// ── Lightbox ─────────────────────────────────────────────
(function () {
  const overlay = document.getElementById('lightbox');
  const img     = document.getElementById('lightboxImg');
  const btnClose= document.getElementById('lightboxClose');
  if (!overlay || !img) return;

  function open(src, alt) {
    img.src = src;
    img.alt = alt || '';
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function close() {
    overlay.classList.remove('open');
    document.body.style.overflow = '';
    // Limpiar src con pequeño delay para evitar parpadeo
    setTimeout(() => { img.src = ''; }, 250);
  }

  // Abrir al hacer clic en la imagen
  document.querySelectorAll('.ratio-4-5.zoomable').forEach(div => {
    div.addEventListener('click', function () {
      open(this.dataset.lbSrc, this.dataset.lbAlt);
    });
  });

  // Cerrar con botón X, clic en overlay o tecla Escape
  btnClose.addEventListener('click', close);
  overlay.addEventListener('click', function (e) {
    if (e.target === overlay) close();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && overlay.classList.contains('open')) close();
  });
})();

// ── Botón WA: fallback para desktop (copiar texto) ──────
document.querySelectorAll('.btn-wa[data-wa-text]').forEach(btn => {
  btn.addEventListener('click', function (e) {
    // En desktop sin WhatsApp instalado, el navegador igual abre wa.me
    // No es necesario interceptar — dejamos que el href maneje todo.
  });
});

// ── Admin: slug automático desde nombre ──────────────────
(function () {
  const nombreInput = document.getElementById('nombreInput');
  const slugInput   = document.getElementById('slugInput');
  if (!nombreInput || !slugInput) return;

  nombreInput.addEventListener('input', function () {
    if (slugInput.dataset.manual === 'true') return;
    slugInput.value = slugify(this.value);
  });

  slugInput.addEventListener('input', function () {
    this.dataset.manual = this.value ? 'true' : 'false';
  });

  function slugify(str) {
    return str
      .toLowerCase()
      .normalize('NFD')
      .replace(/[̀-ͯ]/g, '')
      .replace(/[^a-z0-9\s-]/g, '')
      .trim()
      .replace(/[\s-]+/g, '-');
  }
})();
