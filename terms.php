<?php
session_start();
require __DIR__ . '/includes/site_gate.php';

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/db_schema.php';
require_once __DIR__ . '/includes/currency_helper.php';
require_once __DIR__ . '/includes/layout.php';

siteHeadOpen([
    'title'       => 'Terms of Service',
    'description' => 'The terms that apply when you visit or purchase from ' . BRAND_NAME . '.',
    'css'         => ['assets/css/pages.css'],
]);
siteBodyOpen(['bodyClass' => 'page-terms', 'searchAction' => 'shop.php']);
?>
  <main class="page-main">
    <article class="doc-body">
      <header class="doc-head reveal">
        <p class="doc-eyebrow">Terms</p>
        <h1 class="doc-title">Terms of Service</h1>
      </header>

      <section class="doc-sec reveal">
        <h2>General</h2>
        <p><?= esc(BRAND_NAME) ?> runs this website. When you visit it or buy from us, you agree to these terms.</p>
      </section>
      <section class="doc-sec reveal">
        <h2>Payments and charges</h2>
        <p>We charge every order in USD. Prices in other currencies are estimates. Your shipping cost depends on where we deliver and the courier you choose.</p>
      </section>
      <section class="doc-sec reveal">
        <h2>Shipping</h2>
        <p>We pack your order within 1–2 working days after your payment clears, unless we tell you otherwise.</p>
      </section>
      <section class="doc-sec reveal">
        <h2>Exchange &amp; returns</h2>
        <p>All sales are final. If an item arrives defective, or we sent the wrong one, contact us within 3 days of delivery. See <a href="shipping.php">Shipping &amp; Returns</a> for details.</p>
      </section>
    </article>
  </main>
<?php
siteBodyClose();
siteDocClose();
