/* Interacciones públicas del catálogo. */
(function () {
  'use strict';

  function initCategoriaPage() {
    var grid = document.getElementById('icProdGrid');
    if (!grid) return;
    var chips = document.querySelectorAll('[data-filter]');
    var cards = Array.from(grid.querySelectorAll('.ic-prod-card'));
    var originalOrder = cards.slice();

    function applyFilter(filter) {
      chips.forEach(function (chip) {
        chip.classList.toggle('ic-chip-filter-active', chip.dataset.filter === filter);
      });
      if (filter === 'all') {
        cards.forEach(function (card) { card.hidden = false; });
        originalOrder.forEach(function (card) { grid.appendChild(card); });
      } else if (filter === 'credito') {
        cards.forEach(function (card) { card.hidden = card.dataset.tieneCredito !== '1'; });
      } else {
        cards.forEach(function (card) { card.hidden = false; });
        cards.slice().sort(function (a, b) {
          return (parseFloat(a.dataset.contado) || Infinity) - (parseFloat(b.dataset.contado) || Infinity);
        }).forEach(function (card) { grid.appendChild(card); });
      }
    }

    chips.forEach(function (chip) {
      chip.addEventListener('click', function () { applyFilter(chip.dataset.filter); });
    });
  }

  function initPromoCarousel() {
    var carousel = document.querySelector('[data-promo-carousel]');
    if (!carousel) return;
    var slides = Array.from(carousel.querySelectorAll('[data-promo-slide]'));
    var indicators = Array.from(carousel.querySelectorAll('[data-promo-indicator]'));
    var prev = carousel.querySelector('[data-promo-prev]');
    var next = carousel.querySelector('[data-promo-next]');
    if (slides.length < 2) return;

    var current = 0;
    var timer;
    var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    function show(index) {
      current = (index + slides.length) % slides.length;
      slides.forEach(function (slide, i) {
        var active = i === current;
        slide.classList.toggle('is-active', active);
        slide.setAttribute('aria-hidden', active ? 'false' : 'true');
      });
      indicators.forEach(function (indicator, i) {
        var active = i === current;
        indicator.classList.toggle('is-active', active);
        indicator.setAttribute('aria-selected', active ? 'true' : 'false');
      });
    }
    function stop() { window.clearInterval(timer); }
    function start() {
      stop();
      if (!reducedMotion) timer = window.setInterval(function () { show(current + 1); }, 6500);
    }
    indicators.forEach(function (indicator, i) {
      indicator.addEventListener('click', function () { show(i); start(); });
    });
    if (prev) prev.addEventListener('click', function () { show(current - 1); start(); });
    if (next) next.addEventListener('click', function () { show(current + 1); start(); });

    var touchStart = 0;
    carousel.addEventListener('touchstart', function (event) { touchStart = event.changedTouches[0].screenX; }, { passive: true });
    carousel.addEventListener('touchend', function (event) {
      var distance = event.changedTouches[0].screenX - touchStart;
      if (Math.abs(distance) > 45) { show(current + (distance < 0 ? 1 : -1)); start(); }
    }, { passive: true });
    carousel.addEventListener('mouseenter', stop);
    carousel.addEventListener('mouseleave', start);
    document.addEventListener('visibilitychange', function () { document.hidden ? stop() : start(); });
    start();
  }

  function initProductoPage() {
    var planOpts = document.querySelectorAll('.ic-plan-opt');
    planOpts.forEach(function (opt) {
      opt.addEventListener('click', function () {
        planOpts.forEach(function (item) {
          var active = item === opt;
          item.classList.toggle('ic-plan-opt-active', active);
          var radio = item.querySelector('.ic-radio');
          if (radio) radio.classList.toggle('ic-radio-checked', active);
        });
      });
    });
  }

  function initImageViewer() {
    var viewer = document.querySelector('[data-image-viewer]');
    if (!viewer) return;
    var image = viewer.querySelector('[data-image-viewer-image]');
    var closeButton = viewer.querySelector('[data-image-viewer-close]');

    function close() {
      viewer.classList.remove('is-open');
      viewer.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('ic-image-viewer-open');
      window.setTimeout(function () {
        viewer.hidden = true;
        image.src = '';
        image.alt = '';
      }, 180);
    }

    document.querySelectorAll('.ic-image-trigger').forEach(function (trigger) {
      trigger.addEventListener('click', function () {
        image.src = trigger.dataset.imageSrc;
        image.alt = trigger.dataset.imageAlt || '';
        viewer.hidden = false;
        viewer.setAttribute('aria-hidden', 'false');
        document.body.classList.add('ic-image-viewer-open');
        window.requestAnimationFrame(function () { viewer.classList.add('is-open'); });
        closeButton.focus();
      });
    });

    closeButton.addEventListener('click', close);
    viewer.addEventListener('click', function (event) { if (event.target === viewer) close(); });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && !viewer.hidden) close(); });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initCategoriaPage();
    initPromoCarousel();
    initProductoPage();
    initImageViewer();
  });
})();
