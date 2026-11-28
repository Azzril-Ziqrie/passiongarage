<?php
session_start();
require __DIR__ . '/includes/site_gate.php';

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/db_schema.php';
require_once __DIR__ . '/includes/currency_helper.php';
require_once __DIR__ . '/includes/layout.php';

siteHeadOpen([
    'title'       => 'FAQ',
    'description' => 'Taxes, contact details and garment care at ' . BRAND_NAME . '.',
    'css'         => ['assets/css/pages.css'],
    'jsonLd'      => [[
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => [
            ['@type' => 'Question', 'name' => 'Are taxes included in the price?', 'acceptedAnswer' => ['@type' => 'Answer',
                'text' => 'Our prices exclude tax. You pay any tax or import duty at the rate your country sets.']],
            ['@type' => 'Question', 'name' => 'How do I contact ' . BRAND_NAME . '?', 'acceptedAnswer' => ['@type' => 'Answer',
                'text' => 'Email us at ' . BRAND_EMAIL . ' or message us on Instagram (' . BRAND_INSTAGRAM . '). Questions, feedback and collaboration ideas are all welcome.']],
            ['@type' => 'Question', 'name' => 'How should I wash my ' . BRAND_NAME . ' products?', 'acceptedAnswer' => ['@type' => 'Answer',
                'text' => 'Unless the label says otherwise: turn it inside out, machine wash cold, skip the bleach, and tumble dry low or hang it to dry.']],
        ],
    ]],
]);
siteBodyOpen(['bodyClass' => 'page-faq', 'searchAction' => 'shop.php']);
?>
  <main class="page-main">
    <article class="doc-body">
      <header class="doc-head reveal">
        <h1 class="doc-title">FAQ</h1>
      </header>

      <section class="doc-sec reveal">
        <h2>Taxes</h2>
        <p>Our prices exclude tax. You pay any tax or import duty at the rate your country sets.</p>
      </section>

      <section class="doc-sec reveal">
        <h2>Contact Us</h2>
        <p>Questions, feedback or a collaboration idea? Send us an
          <?php if (BRAND_EMAIL !== ''): ?><a href="mailto:<?= esc(BRAND_EMAIL) ?>">email</a><?php else: ?>email<?php endif; ?>
          or a message on
          <?php if (BRAND_INSTAGRAM !== ''): ?><a href="<?= esc(BRAND_INSTAGRAM) ?>" target="_blank" rel="noopener">Instagram</a><?php else: ?>Instagram<?php endif; ?>.</p>
      </section>

      <section class="doc-sec reveal">
        <h2>Product Care</h2>
        <p>Vintage pieces last longer with gentle care. Unless the label says otherwise:</p>
        <ul>
          <li>Turn it inside out</li>
          <li>Machine wash cold</li>
          <li>Skip the bleach</li>
          <li>Tumble dry low, or hang it to dry</li>
        </ul>
      </section>
    </article>
  </main>
<?php
siteBodyClose();
siteDocClose();
