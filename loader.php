<?php
/**
 * Loading screen: partikel abstrak memudar masuk lalu mengumpul (garis lurus, tanpa rotasi) membentuk logo (BRAND_MARK, logo tengah navbar),
 * kemudian logo asli muncul tajam di atasnya. Layar baru dibuka setelah halaman siap dan logo selesai terbentuk.
 * Hanya untuk beranda: di-include oleh siteBodyOpen() (includes/layout.php) bila opsi 'loader' => true.
 */
?>
<style>
  #global-loader {
    position: fixed;
    inset: 0;
    z-index: 100001; /* di atas pop-up mata uang (#currencyGate, 100000): intro dulu, baru pop-up */
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fafafa;
  }
  #global-loader.hidden {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity .6s cubic-bezier(.32, .72, 0, 1), visibility 0s .6s;
  }

  .loader-stack {
    display: flex;
    align-items: center;
    transition: transform .6s cubic-bezier(.32, .72, 0, 1), opacity .45s ease, filter .6s ease;
  }
  #global-loader.hidden .loader-stack { transform: scale(1.1); opacity: 0; filter: blur(8px); }

  /* Panggung: kanvas partikel di bawah, logo asli di atas (tepat di posisi yang sama). */
  .loader-stage { position: relative; width: 280px; height: 280px; }
  .loader-particles { position: absolute; inset: 0; width: 100%; height: 100%; transition: opacity .5s ease; }
  .loader-logo {
    position: absolute;
    left: 50%;
    top: 50%;
    width: 96px;
    height: auto;
    transform: translate(-50%, -50%);
    opacity: 0;
    transition: opacity .45s ease;
  }
  #global-loader.is-formed .loader-particles { opacity: 0; }
  #global-loader.is-formed .loader-logo { opacity: 1; animation: ldBreath 2.6s ease-in-out .5s infinite; }

  @keyframes ldBreath {
    0%, 100% { transform: translate(-50%, -50%) scale(1);    opacity: .92; }
    50%      { transform: translate(-50%, -50%) scale(1.04); opacity: 1; }
  }

  /* Reduced motion: tanpa partikel & tanpa napas — logo cukup muncul lewat fade. */
  @media (prefers-reduced-motion: reduce) {
    .loader-particles { display: none; }
    #global-loader.is-formed .loader-logo { animation: none; }
    #global-loader.hidden .loader-stack { transform: none; filter: none; }
    #global-loader.hidden { transition: opacity .2s ease, visibility 0s .2s; }
  }
</style>

<div id="global-loader" role="status" aria-live="polite" aria-label="Loading">
  <div class="loader-stack">
    <div class="loader-stage">
      <canvas class="loader-particles" id="loaderParticles" aria-hidden="true"></canvas>
      <img src="<?= htmlspecialchars(BRAND_MARK, ENT_QUOTES, 'UTF-8') ?>" alt="" class="loader-logo" id="loaderLogo" decoding="sync">
    </div>
  </div>
</div>

<script>
(function () {
  'use strict';
  var loader = document.getElementById('global-loader');
  var canvas = document.getElementById('loaderParticles');
  var logo   = document.getElementById('loaderLogo');
  if (!loader || !canvas || !logo) return;

  /* ── Intro logo: partikel → logo ─────────────────────────── */

  var TIMING       = { duration: 1050, stagger: 380 }; // ms; total ±1,4 detik sampai logo utuh
  var PARTICLE_GAP = 2.6;  // jarak sampel (px CSS) di dalam bentuk logo
  var DOT_SIZE     = 1.7;
  var PARTICLE_SCATTER = 70; // seberapa jauh (px) partikel tersebar dari tempatnya di awal
  var INK          = '#111';

  var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /** Mirip cubic-bezier(.32,.72,0,1): cepat lalu mendarat halus, tanpa memantul. */
  function easeOutQuart(t) { return 1 - Math.pow(1 - t, 4); }

  /** Titik-titik di dalam area logo (alpha > 50%), relatif terhadap pusat logo. */
  function sampleLogoPoints(img, width, height) {
    var off = document.createElement('canvas');
    off.width = Math.round(width);
    off.height = Math.round(height);
    var octx = off.getContext('2d');
    octx.drawImage(img, 0, 0, off.width, off.height);
    var data = octx.getImageData(0, 0, off.width, off.height).data;
    var points = [];
    for (var y = 0; y < off.height; y += PARTICLE_GAP) {
      for (var x = 0; x < off.width; x += PARTICLE_GAP) {
        if (data[(Math.floor(y) * off.width + Math.floor(x)) * 4 + 3] > 128) {
          points.push({ x: x - off.width / 2, y: y - off.height / 2 });
        }
      }
    }
    return points;
  }

  /**
   * Tiap partikel muncul di posisi acak di sekitar titik tujuannya (awan abstrak), memudar masuk,
   * lalu bergeser lurus ke tempatnya — tanpa rotasi. Urutan muncul acak supaya terasa organik.
   */
  function createParticles(points, scatter, stagger) {
    return points.map(function (p) {
      var angle    = Math.random() * Math.PI * 2;
      var distance = scatter * (0.25 + 0.75 * Math.random());
      return {
        x0: p.x + Math.cos(angle) * distance,
        y0: p.y + Math.sin(angle) * distance,
        x1: p.x,
        y1: p.y,
        delay: Math.random() * stagger
      };
    });
  }

  function createLogoIntro() {
    var ctx = canvas.getContext('2d');
    var particles = [];
    var raf = null;

    function resizeCanvas() {
      var dpr = Math.min(window.devicePixelRatio || 1, 2);
      canvas.width = canvas.clientWidth * dpr;
      canvas.height = canvas.clientHeight * dpr;
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    function drawFrame(elapsed, timing) {
      var cx = canvas.clientWidth / 2, cy = canvas.clientHeight / 2;
      ctx.clearRect(0, 0, canvas.clientWidth, canvas.clientHeight);
      ctx.fillStyle = INK;
      for (var i = 0; i < particles.length; i++) {
        var p = particles[i];
        var t = Math.min(1, Math.max(0, (elapsed - p.delay) / timing.duration));
        var e = easeOutQuart(t);
        var x = p.x0 + (p.x1 - p.x0) * e;
        var y = p.y0 + (p.y1 - p.y0) * e;
        var size = DOT_SIZE * (1 + (1 - e) * 0.9);   // sedikit lebih besar saat masih jauh
        ctx.globalAlpha = Math.min(1, t * 1.6);        // memudar masuk perlahan
        ctx.fillRect(cx + x - size / 2, cy + y - size / 2, size, size);
      }
      ctx.globalAlpha = 1;
    }

    /** Mainkan intro; onDone dipanggil sekali saat logo selesai terbentuk. */
    function play(timing, onDone) {
      cancelAnimationFrame(raf);
      loader.classList.remove('is-formed');
      var finish = function () { loader.classList.add('is-formed'); onDone(); };

      if (reducedMotion) { finish(); return; }
      try {
        resizeCanvas();
        var points = sampleLogoPoints(logo, logo.clientWidth, logo.clientHeight);
        particles = createParticles(points, PARTICLE_SCATTER, timing.stagger);
      } catch (err) {
        finish(); // kanvas tidak bisa membaca gambar (mis. dibuka via file://): langsung tampilkan logo
        return;
      }

      var start = performance.now();
      var total = timing.duration + timing.stagger;
      (function tick(now) {
        var elapsed = now - start;
        drawFrame(elapsed, timing);
        if (elapsed < total) { raf = requestAnimationFrame(tick); } else { finish(); }
      })(start);
    }

    return { play: play };
  }

  /* ── Tampil / sembunyi: tunggu halaman siap DAN logo selesai terbentuk ───── */

  var MAX_WAIT_MS = 8000; // jaring pengaman: jangan pernah mengunci layar
  var pageReady = false, introDone = false;
  var failsafe = setTimeout(hide, MAX_WAIT_MS);

  function hide() {
    clearTimeout(failsafe);
    loader.classList.add('hidden');
  }

  function hideWhenBothReady() {
    if (pageReady && introDone) hide();
  }

  function onIntroDone() { introDone = true; hideWhenBothReady(); }
  function onPageReady() { pageReady = true; hideWhenBothReady(); }

  function whenLogoReady(fn) {
    if (logo.complete && logo.naturalWidth) { fn(); return; }
    logo.addEventListener('load', fn, { once: true });
    logo.addEventListener('error', function () { loader.classList.add('is-formed'); onIntroDone(); }, { once: true });
  }

  var intro = createLogoIntro();
  whenLogoReady(function () { intro.play(TIMING, onIntroDone); });

  if (document.readyState === 'complete') onPageReady();
  else window.addEventListener('load', onPageReady);

  // Kembali ke beranda dengan tombol Back (bfcache): jangan tertinggal di layar loading
  window.addEventListener('pageshow', function (e) { if (e.persisted) hide(); });
})();
</script>
