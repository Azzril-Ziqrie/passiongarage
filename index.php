<?php
session_start();

require __DIR__ . '/includes/site_gate.php';

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/kasera_client.php';
require_once __DIR__ . '/includes/db_schema.php';
require_once __DIR__ . '/includes/currency_helper.php';
require_once __DIR__ . '/includes/pricing.php';
require_once __DIR__ . '/includes/email_helpers.php';
require_once __DIR__ . '/includes/catalog.php';
require_once __DIR__ . '/includes/home_media.php';
require_once __DIR__ . '/includes/home_data.php';
require_once __DIR__ . '/includes/announcement.php';
require_once __DIR__ . '/includes/kasera_return.php';
require_once __DIR__ . '/includes/layout.php';

$composerAutoload = __DIR__ . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require_once $composerAutoload;
}

// Link "Currency" di menu membuka lagi pop-up pilih currency.
if (isset($_GET['choose_currency'])) {
    unset($_SESSION['currency_prompt_done']);
}

handleKaseraReturn($pdo);

/* ── view helpers ────────────────────────────────────── */
function slugToLabel(string $slug): string
{
    return strtoupper(str_replace('-', ' ', $slug));
}

/** Data untuk pop-up mata uang: saran berdasarkan IP + daftar mata uang lain. */
function currencyGateModel(): array
{
    $suggest = null;
    $options = [];
    $rates   = ddExchangeRates();
    $geo     = ddDetectVisitorCurrency();

    if ($geo && $geo['currency'] !== 'USD' && $rates && !empty($rates[$geo['currency']])) {
        $suggest = $geo + ['rate' => (float)$rates[$geo['currency']]];
    }
    if ($rates) {
        $codes = array_unique(array_values(ddCountryCurrencyMap()));
        sort($codes);
        foreach ($codes as $code) {
            if ($code !== 'USD' && !empty($rates[$code]) && (!$suggest || $code !== $suggest['currency'])) {
                $options[] = $code;
            }
        }
    }
    return ['suggest' => $suggest, 'options' => $options];
}

/* ── request ─────────────────────────────────────────── */
$search     = mb_substr(trim((string)($_GET['search'] ?? '')), 0, SEARCH_MAX_LENGTH);
$category   = trim((string)($_GET['category'] ?? ''));
$isFiltered = $search !== '' || $category !== '';

$paymentUploaded = ($_GET['payment_uploaded'] ?? '') === '1';
$orderUploaded   = trim((string)($_GET['order'] ?? ''));

/* ── data ────────────────────────────────────────────── */
$schema     = catalogSchema($pdo);
// Banner hero = slideshow gambar (assets/images/hero/banner-N.jpg + versi -960 untuk layar kecil).
// Gambar pertama dipakai juga untuk og:image. Video lama (assets/video/hero.mp4) kini jadi latar site lock.
const HERO_SLIDES   = 5;
const HERO_INTERVAL = 5000; // ms per gambar
const HERO_POSTER   = 'assets/images/hero/banner-1.jpg';
$categories = fetchCategories($pdo, $schema);
$products   = fetchInStockProducts($pdo, $schema, $search, $category);
$gate       = currencyGateModel();
$announce   = announcementForDisplay($pdo);
$rowBottom  = $isFiltered ? [] : fetchHomeMedia($pdo, 'bottom');
$captionId  = firstVideoId([], $rowBottom);
$journal    = $isFiltered ? [] : fetchHomeMedia($pdo, HOME_JOURNAL_PLACEMENT);
$tiles      = $isFiltered ? [] : buildCategoryTiles($products);
$mediaBottom = renderHomeMedia($rowBottom, BRAND_NAME, 'low', $captionId);

$origin  = BRAND_URL;
$jsonLd  = [
    '@context'   => 'https://schema.org',
    '@type'      => 'Store',
    'name'       => BRAND_NAME,
    'url'        => $origin,
    'image'      => $origin . '/' . ltrim(HERO_POSTER, '/'),
    'priceRange' => '$$',
];

/* ── render ──────────────────────────────────────────── */
siteHeadOpen([
    'description'  => BRAND_NAME . ' — ' . BRAND_TAGLINE,
    'image'        => HERO_POSTER,
    'css'          => ['assets/css/home.css'],
    'currencyGate' => true,
]);
?>
  <script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
<?php
siteBodyOpen([
    'bodyClass'    => 'home' . ($announce ? ' has-announce' : ''),
    'overlay'      => false,
    'searchAction' => 'index.php',
    'categoryBase' => 'index.php',
    'search'       => $search,
    'category'     => $category,
    'currencyGate' => $gate,
    'loader'       => !$isFiltered, // loading screen logo hanya di beranda, bukan saat filter kategori/pencarian
    'beforeNavbar' => function () use ($paymentUploaded, $orderUploaded, $announce) {
        if ($announce) { echo renderAnnouncement($announce); }
        if (!$paymentUploaded) { return; }
        ?>
  <div id="paymentPopup" class="modal modal--open" role="dialog" aria-modal="true" aria-labelledby="paymentPopupTitle">
    <div class="modal-backdrop"></div>
    <div class="modal-dialog">
      <button type="button" class="modal-close" id="closePaymentPopup" aria-label="Close">×</button>
      <div class="modal-eyebrow"><img src="<?= esc(BRAND_LOGO) ?>" alt="<?= esc(BRAND_NAME) ?>"></div>
      <h2 class="modal-title" id="paymentPopupTitle">Invoice sent to your email</h2>
      <p class="modal-text">Your payment was received. The invoice is on its way to your inbox and your order is now being processed.</p>
      <?php if ($orderUploaded !== ''): ?>
        <div class="modal-meta"><span>Order number</span><strong><?= esc($orderUploaded) ?></strong></div>
      <?php endif; ?>
      <div class="modal-actions">
        <button type="button" id="closePaymentPopupSecondary" class="button">Continue shopping</button>
      </div>
    </div>
  </div>
<?php
    },
]);
?>
  <main class="home-main">
    <section class="hero" aria-label="Featured">
      <div class="hero-slides">
        <?php for ($i = 1; $i <= HERO_SLIDES; $i++): ?>
        <img class="hero-slide<?= $i === 1 ? ' is-active' : '' ?>"
             src="assets/images/hero/banner-<?= $i ?>.jpg"
             srcset="assets/images/hero/banner-<?= $i ?>-960.jpg 960w, assets/images/hero/banner-<?= $i ?>.jpg 1920w"
             sizes="100vw" width="1920" height="1080"
             alt="<?= $i === 1 ? esc(BRAND_NAME) : '' ?>"
             <?= $i === 1 ? 'fetchpriority="high"' : 'loading="lazy"' ?> decoding="async">
        <?php endfor; ?>
      </div>
      <script>
      (function () {
        var slides = document.currentScript.previousElementSibling.children, cur = 0;
        if (slides.length < 2) return;
        setInterval(function () {
          if (document.hidden) return;
          slides[cur].classList.remove('is-active');
          cur = (cur + 1) % slides.length;
          slides[cur].classList.add('is-active');
        }, <?= HERO_INTERVAL ?>);
      })();
      </script>
      <?php if (!$isFiltered): ?>
      <a href="shop.php" class="hero-cta link-u">Shop now</a>
      <?php endif; ?>
    </section>

    <?php if ($journal) { include __DIR__ . '/includes/partials/home_journal.php'; } ?>

    <div class="category-bar">
      <nav class="category-nav" aria-label="Categories">
        <a href="index.php#products" class="<?= !$isFiltered ? 'active' : '' ?>">All</a>
        <?php foreach ($categories as $cat): ?>
          <a href="index.php?category=<?= urlencode((string)$cat['slug']) ?>#products" class="<?= $category === (string)$cat['slug'] ? 'active' : '' ?>"><?= esc(sentenceCase((string)$cat['name'])) ?></a>
        <?php endforeach; ?>
      </nav>
    </div>

    <section class="shelf" id="products" aria-label="Products">
      <div class="shelf-head">
        <?php if ($isFiltered): ?>
          <div class="shelf-title">
            <?php if ($category !== ''): ?>Category: <?= esc(slugToLabel($category)) ?><?php endif; ?>
            <?php if ($search !== ''): ?><?= $category !== '' ? ' · ' : '' ?>Search: <?= esc(strtoupper($search)) ?><?php endif; ?>
            — <a class="link-u" href="index.php">Clear</a>
          </div>
        <?php else: ?>
          <div class="shelf-title">Products (<?= count($products) ?>)</div>
        <?php endif; ?>
      </div>

      <?php if (!$products): ?>
        <p class="empty-text">
          <?php if ($isFiltered): ?>
            No products found<?= $search !== '' ? ' matching “' . esc($search) . '”' : '' ?><?= $category !== '' ? ' in ' . esc(slugToLabel($category)) : '' ?>.
          <?php else: ?>
            No products yet.
          <?php endif; ?>
        </p>
      <?php else: ?>
        <div class="product-grid">
          <?php foreach ($products as $product): ?>
            <?= renderProductCard($product) ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <?php if ($tiles) { include __DIR__ . '/includes/partials/home_tiles.php'; } ?>

    <?php if ($mediaBottom): ?>
    <section class="media media--bottom" aria-label="More"><?= $mediaBottom ?></section>
    <?php endif; ?>
  </main>

<?php
siteBodyClose(['scripts' => ['assets/js/home.js']]);
if (isset($_GET['notfound'])): ?>
  <script>
    // Halaman yang diketik tidak ada: beri tahu sekali, lalu bersihkan ?notfound dari URL supaya refresh tidak mengulang.
    Site.showToast('That page doesn’t exist, so we brought you home.');
    history.replaceState(null, '', location.pathname + location.search.replace(/[?&]notfound=1/, '').replace(/^&/, '?') + location.hash);
  </script>
<?php endif;
siteDocClose();
