/* ic.js — IC Design System — public pages */
(function () {
  'use strict';

  // ── Cart ────────────────────────────────────────────────────────────────────
  var CART_KEY = 'ic_cart';

  function getCart() {
    try { return JSON.parse(localStorage.getItem(CART_KEY) || '[]'); }
    catch (e) { return []; }
  }

  function saveCart(cart) {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
  }

  function addToCart(prod, plan) {
    var cart = getCart();
    var idx = cart.findIndex(function (i) { return i.id === prod.id; });
    var item = {
      id:              prod.id,
      nombre:          prod.nombre,
      imagen:          prod.imagen,
      plan:            plan,
      cuotas_sem_cant: prod.cuotas_sem_cant,
      cuotas_sem_monto:prod.cuotas_sem_monto,
      precio_contado:  prod.precio_contado
    };
    if (idx >= 0) cart[idx] = item;
    else cart.push(item);
    saveCart(cart);
    return cart;
  }

  function removeFromCart(id) {
    var cart = getCart().filter(function (i) { return i.id !== id; });
    saveCart(cart);
    return cart;
  }

  function fmtNum(n) {
    return Math.round(n).toLocaleString('es-AR');
  }

  function planLabel(item) {
    if (item.plan === 'semanal' && item.cuotas_sem_cant) {
      return item.cuotas_sem_cant + ' × $' + fmtNum(item.cuotas_sem_monto) + '/sem';
    }
    if (item.plan === 'contado' && item.precio_contado) {
      return 'Contado $' + fmtNum(item.precio_contado);
    }
    return item.plan;
  }

  function escHtml(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  // ── Cart badge ─────────────────────────────────────────────────────────────
  function updateCartBadges() {
    var count = getCart().length;
    var label = count > 0 ? '(' + count + ')' : '';
    document.querySelectorAll('[data-cart-count]').forEach(function (el) {
      el.textContent = label;
    });
  }

  // ── Home — category menu ──────────────────────────────────────────────────
  function initCategoryMenu() {
    var button = document.getElementById('icMenuButton');
    var menu = document.getElementById('icCategoryMenu');
    if (!button || !menu) return;

    function setOpen(isOpen) {
      menu.hidden = !isOpen;
      button.setAttribute('aria-expanded', String(isOpen));
    }

    button.addEventListener('click', function () {
      setOpen(menu.hidden);
    });

    menu.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () { setOpen(false); });
    });

    document.addEventListener('click', function (event) {
      if (!menu.hidden && !menu.contains(event.target) && !button.contains(event.target)) {
        setOpen(false);
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && !menu.hidden) {
        setOpen(false);
        button.focus();
      }
    });
  }

  // ── Producto — imagen ampliada ────────────────────────────────────────────
  function initProductImageLightbox() {
    var trigger = document.getElementById('icProductImage');
    var lightbox = document.getElementById('icImageLightbox');
    var closeButton = document.getElementById('icImageLightboxClose');
    if (!trigger || !lightbox || !closeButton) return;

    function close() {
      lightbox.hidden = true;
      lightbox.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('ic-image-lightbox-open');
      trigger.focus();
    }

    trigger.addEventListener('click', function (event) {
      event.preventDefault();
      lightbox.hidden = false;
      lightbox.setAttribute('aria-hidden', 'false');
      document.body.classList.add('ic-image-lightbox-open');
      closeButton.focus();
    });

    closeButton.addEventListener('click', close);
    lightbox.addEventListener('click', function (event) {
      if (event.target === lightbox) close();
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && !lightbox.hidden) close();
    });
  }

  // ── Categoria page — chip filters ──────────────────────────────────────────
  function initCategoriaPage() {
    var grid = document.getElementById('icProdGrid');
    if (!grid) return;

    var chips = document.querySelectorAll('[data-filter]');
    if (!chips.length) return;

    var cards = Array.from(grid.querySelectorAll('.ic-prod-card'));
    var originalOrder = cards.slice();

    function applyFilter(filter) {
      chips.forEach(function (c) {
        var isActive = c.dataset.filter === filter;
        c.classList.toggle('ic-chip-filter-active', isActive);
        c.classList.toggle('ic-chip-active', false);
      });

      if (filter === 'all') {
        cards.forEach(function (c) { c.hidden = false; });
        originalOrder.forEach(function (c) { grid.appendChild(c); });
      } else if (filter === 'credito') {
        cards.forEach(function (c) {
          c.hidden = c.dataset.tieneCredito !== '1';
        });
      } else if (filter === 'menor') {
        cards.forEach(function (c) { c.hidden = false; });
        var sorted = cards.slice().sort(function (a, b) {
          var av = parseFloat(a.dataset.contado) || Infinity;
          var bv = parseFloat(b.dataset.contado) || Infinity;
          return av - bv;
        });
        sorted.forEach(function (c) { grid.appendChild(c); });
      }
    }

    chips.forEach(function (chip) {
      chip.addEventListener('click', function () { applyFilter(chip.dataset.filter); });
    });
  }

  // ── Producto page — plan selector / share / agregar ────────────────────────
  function initProductoPage() {
    if (typeof IC_PROD === 'undefined') return;

    var btnCompartir = document.getElementById('btnCompartir');
    var btnAgregar   = document.getElementById('btnAgregar');
    var planOpts     = document.querySelectorAll('.ic-plan-opt');

    var selectedPlan = IC_PROD.selectedPlan || 'semanal';

    function updatePlanUI(plan) {
      selectedPlan = plan;
      planOpts.forEach(function (opt) {
        var active = opt.dataset.plan === plan;
        opt.classList.toggle('ic-plan-opt-active', active);
        var radio = opt.querySelector('.ic-radio');
        if (radio) radio.classList.toggle('ic-radio-checked', active);
      });
    }

    planOpts.forEach(function (opt) {
      opt.addEventListener('click', function () { updatePlanUI(opt.dataset.plan); });
    });

    if (btnCompartir) {
      btnCompartir.addEventListener('click', function () {
        var url = IC_PROD.pageUrl;
        var origText = btnCompartir.textContent;
        if (navigator.share) {
          navigator.share({ title: IC_PROD.nombre, url: url }).catch(function () {});
        } else if (navigator.clipboard) {
          navigator.clipboard.writeText(url).then(function () {
            btnCompartir.textContent = '✓ Enlace copiado';
            setTimeout(function () { btnCompartir.textContent = origText; }, 2200);
          }).catch(function () {});
        }
      });
    }

    if (btnAgregar) {
      btnAgregar.addEventListener('click', function () {
        addToCart(IC_PROD, selectedPlan);
        var orig = btnAgregar.textContent;
        btnAgregar.textContent = '✓ Agregado — ver Mi consulta';
        btnAgregar.disabled = true;
        updateCartBadges();
        setTimeout(function () {
          btnAgregar.textContent = orig;
          btnAgregar.disabled = false;
        }, 2400);
      });
    }
  }

  // ── Consulta page — render cart ────────────────────────────────────────────
  function initConsultaPage() {
    var cartItems = document.getElementById('cartItems');
    if (!cartItems) return;

    var cartCount = document.getElementById('cartCount');
    var btnEnviar = document.getElementById('btnEnviar');
    var waBase = (typeof IC_CONSULTA !== 'undefined') ? IC_CONSULTA.waBase : 'https://wa.me/';

    var pagoMode = 'credito';
    var pagoChips = document.querySelectorAll('[data-pago]');
    pagoChips.forEach(function (chip) {
      chip.addEventListener('click', function () {
        pagoMode = chip.dataset.pago;
        pagoChips.forEach(function (c) {
          c.classList.toggle('ic-chip-active', c.dataset.pago === pagoMode);
        });
      });
    });

    function renderCart() {
      var cart = getCart();
      if (cartCount) {
        cartCount.textContent = cart.length > 0 ? '(' + cart.length + ')' : '';
      }

      if (cart.length === 0) {
        cartItems.innerHTML = '<div class="ic-consulta-empty">Tu consulta está vacía.<br><a href="index.php">Ver catálogo →</a></div>';
        if (btnEnviar) btnEnviar.disabled = true;
        return;
      }

      if (btnEnviar) btnEnviar.disabled = false;

      cartItems.innerHTML = cart.map(function (item) {
        var imgTag = item.imagen
          ? '<img src="' + escHtml(item.imagen) + '" alt="' + escHtml(item.nombre) + '" loading="lazy">'
          : '';
        return [
          '<div class="ic-consulta-item">',
          '  <div class="ic-consulta-thumb">' + imgTag + '</div>',
          '  <div class="ic-consulta-info">',
          '    <p class="ic-consulta-nombre">' + escHtml(item.nombre) + '</p>',
          '    <p class="ic-consulta-plan">' + escHtml(planLabel(item)) + '</p>',
          '  </div>',
          '  <button class="ic-consulta-remove" data-id="' + item.id + '" type="button" aria-label="Quitar">✕</button>',
          '</div>'
        ].join('');
      }).join('');

      cartItems.querySelectorAll('.ic-consulta-remove').forEach(function (btn) {
        btn.addEventListener('click', function () {
          removeFromCart(parseInt(btn.dataset.id, 10));
          renderCart();
          updateCartBadges();
        });
      });
    }

    if (btnEnviar) {
      btnEnviar.addEventListener('click', function () {
        var cart = getCart();
        if (!cart.length) return;

        var nombre = (document.getElementById('inputNombre') || {}).value || '';
        var zona   = (document.getElementById('inputZona')   || {}).value || '';
        nombre = nombre.trim();
        zona   = zona.trim();

        var lines = ['Hola! Me interesa consultar por estos productos:\n'];
        cart.forEach(function (item) {
          lines.push('• ' + item.nombre + ' — ' + planLabel(item));
        });
        if (nombre) lines.push('\nNombre: ' + nombre);
        if (zona)   lines.push('Zona: ' + zona);
        lines.push('Forma de pago: ' + (pagoMode === 'credito' ? 'Quiero crédito' : 'Pago contado'));

        var text = lines.join('\n');
        window.open(waBase + '?text=' + encodeURIComponent(text), '_blank');
      });
    }

    renderCart();
  }

  // ── Init ───────────────────────────────────────────────────────────────────
  document.addEventListener('DOMContentLoaded', function () {
    updateCartBadges();
    initCategoryMenu();
    initProductImageLightbox();
    initCategoriaPage();
    initProductoPage();
    initConsultaPage();
  });

})();
