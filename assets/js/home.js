/** Halaman depan: pop-up pembayaran, pop-up mata uang. Butuh site.js. */
(function () {
  'use strict';

  var $ = function (id) { return document.getElementById(id); };
  function on(id, event, handler) { var el = $(id); if (el) el.addEventListener(event, handler); return el; }

  /* ── hero: pengguna yang meminta gerak dikurangi melihat poster diam, bukan video berputar ── */
  (function () {
    var video = document.querySelector('.hero video');
    if (!video || !window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    video.removeAttribute('autoplay');
    video.pause();
  })();

  /* ── baris geser (Journal, tile kategori): seret dengan mouse, lepas dengan momentum ──
     Sentuhan memakai scroll bawaan browser. Untuk mouse: isi menempel 1:1 pada pointer (pointer capture), kecepatan
     dilepas dari beberapa titik terakhir lalu diteruskan sebagai peluruhan eksponensial — jaraknya = kecepatan × tau,
     sama seperti proyeksi momentum scroll. Klik yang menutup sebuah seretan dibatalkan supaya tidak membuka link. */
  var DRAG_TAU_MS = 500, DRAG_THRESHOLD = 4, DRAG_SAMPLE_WINDOW_MS = 100;
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function releaseVelocity(samples) {
    var first = samples[0], last = samples[samples.length - 1];
    var dt = last.t - first.t;
    return dt > 0 ? (first.x - last.x) / dt : 0;   // px/ms; positif = menggeser isi ke kanan
  }

  function enableDragScroll(strip) {
    var drag = null, frame = 0, dragged = false;

    function markScrollable() { strip.classList.toggle('is-scrollable', strip.scrollWidth > strip.clientWidth + 1); }
    markScrollable();
    window.addEventListener('resize', markScrollable);
    window.addEventListener('load', markScrollable);

    function glide(velocity) {
      var start = strip.scrollLeft, begin = performance.now();
      var distance = velocity * DRAG_TAU_MS;
      (function step(now) {
        var t = now - begin;
        strip.scrollLeft = start + distance * (1 - Math.exp(-t / DRAG_TAU_MS));
        if (t < DRAG_TAU_MS * 6 && Math.abs(distance * Math.exp(-t / DRAG_TAU_MS)) > 0.5) {
          frame = requestAnimationFrame(step);
        } else {
          strip.classList.remove('is-dragging');   // snap kembali aktif
        }
      })(begin);
    }

    strip.addEventListener('pointerdown', function (e) {
      if (e.pointerType !== 'mouse' || e.button !== 0) return;
      cancelAnimationFrame(frame);
      drag = { x: e.clientX, left: strip.scrollLeft, samples: [{ x: e.clientX, t: e.timeStamp }] };
      dragged = false;
      strip.setPointerCapture(e.pointerId);
    });

    strip.addEventListener('pointermove', function (e) {
      if (!drag) return;
      var dx = e.clientX - drag.x;
      if (!dragged && Math.abs(dx) < DRAG_THRESHOLD) return;
      dragged = true;
      strip.classList.add('is-dragging');
      strip.scrollLeft = drag.left - dx;
      drag.samples.push({ x: e.clientX, t: e.timeStamp });
      while (drag.samples.length > 2 && e.timeStamp - drag.samples[0].t > DRAG_SAMPLE_WINDOW_MS) drag.samples.shift();
    });

    function endDrag() {
      if (!drag) return;
      var velocity = releaseVelocity(drag.samples);
      drag = null;
      if (!dragged) return;
      if (reduceMotion || Math.abs(velocity) < 0.05) { strip.classList.remove('is-dragging'); return; }
      glide(velocity);
    }
    strip.addEventListener('pointerup', endDrag);
    strip.addEventListener('pointercancel', endDrag);

    strip.addEventListener('click', function (e) {
      if (dragged) { e.preventDefault(); e.stopPropagation(); dragged = false; }
    }, true);
    strip.addEventListener('dragstart', function (e) { e.preventDefault(); });
  }
  document.querySelectorAll('.journal-strip, .tiles').forEach(enableDragScroll);

  /* ── pop-up pembayaran berhasil ─────────────────────── */
  (function () {
    var popup = $('paymentPopup');
    if (!popup) return;

    function clearUrlParams() {
      var url = new URL(window.location.href);
      ['payment_uploaded', 'order', 'invoice_email'].forEach(function (p) { url.searchParams.delete(p); });
      var query = url.searchParams.toString();
      history.replaceState({}, document.title, url.pathname + (query ? '?' + query : ''));
    }
    function closePopup() {
      popup.classList.remove('modal--open');
      popup.classList.add('modal--closing');
      setTimeout(function () { popup.remove(); clearUrlParams(); }, 250);
    }
    on('closePaymentPopup', 'click', closePopup);
    on('closePaymentPopupSecondary', 'click', closePopup);
    popup.querySelector('.modal-backdrop').addEventListener('click', closePopup);
  })();

  /* ── pop-up mata uang ───────────────────────────────── */
  (function () {
    var KEY = 'ddCurrencyPicked', root = document.documentElement, gate = $('currencyGate');
    try {
      if (location.search.indexOf('choose_currency') !== -1) {
        sessionStorage.removeItem(KEY);
        // Buang parameter supaya refresh / kembali tidak membuka pop-up lagi.
        var clean = location.search.replace(/([?&])choose_currency(=[^&]*)?(&|$)/, '$1').replace(/[?&]$/, '');
        history.replaceState(null, '', location.pathname + clean + location.hash);
      }
      if (sessionStorage.getItem(KEY) === '1') root.classList.add('cg-skip');
    } catch (e) {}
    if (!gate || root.classList.contains('cg-skip')) return;

    function picked() { try { sessionStorage.setItem(KEY, '1'); } catch (e) {} }
    document.body.classList.add('cg-lock');
    gate.querySelectorAll('a.cg-opt').forEach(function (a) { a.addEventListener('click', picked); });

    var other = $('cgOther');
    if (other) other.addEventListener('change', function () {
      if (!other.value) return;
      picked();
      location.href = 'set-currency.php?to=' + encodeURIComponent(other.value);
    });

    var focusTarget = gate.querySelector('a.cg-rec') || $('cgDialog');
    if (focusTarget) focusTarget.focus({ preventScroll: true });
  })();
})();
