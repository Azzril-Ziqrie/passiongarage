<?php
session_start();
require __DIR__ . '/includes/site_gate.php';

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/db_schema.php';
require_once __DIR__ . '/includes/currency_helper.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/free_shipping.php';

siteHeadOpen([
    'title'       => 'Shipping & Returns',
    'description' => 'Shipping rates, dispatch times and the return policy of ' . BRAND_NAME . '.',
    'css'         => ['assets/css/pages.css'],
]);
siteBodyOpen(['bodyClass' => 'page-shipping', 'searchAction' => 'shop.php']);
?>
  <main class="page-main">
    <article class="doc-body">
      <header class="doc-head reveal">
        <h1 class="doc-title">Shipping &amp; Returns</h1>
      </header>

      <section class="doc-group reveal">
        <h2 class="doc-eyebrow">Shipping</h2>

        <h3 class="doc-label">Shipping cost</h3>
        <p class="doc-text">We ship across Indonesia and worldwide. Enter your address at checkout and you’ll see the courier options and the exact cost before you pay.</p>
        <?php if (($freeShippingNote = freeShippingSummary(freeShippingSettings($pdo))) !== ''): ?>
        <p class="doc-text"><strong><?= esc($freeShippingNote) ?></strong></p>
        <?php endif; ?>

        <h3 class="doc-label">International orders</h3>
        <p class="doc-text">Your country may charge tax or import duty on arrival. Read more in the <a href="faq.php">FAQ</a>.</p>

        <h3 class="doc-label">Dispatch</h3>
        <p class="doc-text">We pack and send your order from Yogyakarta within 1–2 working days after your payment clears.</p>
      </section>

      <section class="doc-group reveal">
        <h2 class="doc-eyebrow">Returns &amp; Exchanges</h2>

        <h3 class="doc-label">All sales are final</h3>
        <p class="doc-text">We sell vintage, so we can’t take returns for a change of mind or a size that doesn’t fit. Check the size guide on each product page before you order.</p>

        <h3 class="doc-label">Defective or wrong item</h3>
        <p class="doc-text">If your order arrives damaged, or we sent the wrong item, contact us within 3 days of delivery. Send photos of the item and your order number, and we’ll sort it out with you.</p>
      </section>
    </article>
  </main>
<?php
siteBodyClose();
siteDocClose();
