<?php
// /llms.txt (lewat .htaccess): ringkasan brand untuk mesin jawaban AI (GEO).
require_once __DIR__ . '/includes/brand.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/plain; charset=utf-8');
$origin = BRAND_URL;

echo '# ' . BRAND_NAME . " (PassionGarage)\n\n";
echo '> ' . BRAND_TAGLINE . ' ' . BRAND_NAME . ' is a vintage fashion store founded in ' . BRAND_EST
   . ' in Yogyakarta, Indonesia. It sells vintage clothing from leading brands, follows current trends, and ships to thousands of customers in Indonesia and abroad. Prices are listed in USD and do not include taxes.' . "\n\n";

echo "## About\n";
foreach (brandAboutCopy() as $line) {
    echo $line . "\n\n";
}

echo "## Key pages\n";
echo "- [Shop]({$origin}/shop): all current products\n";
echo "- [Lookbook]({$origin}/lookbook): collections and photography\n";
echo "- [About]({$origin}/about): brand story\n";
echo "- [FAQ]({$origin}/faq): taxes, contact, product care\n";
echo "- [Shipping]({$origin}/shipping): delivery information\n";
echo "- [Contact]({$origin}/contact): " . BRAND_EMAIL . "\n\n";

try {
    $products = $pdo->query('SELECT name, slug FROM products WHERE is_active = 1 ORDER BY id ASC LIMIT 100')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $products = [];
}
if ($products) {
    echo "## Products\n";
    foreach ($products as $p) {
        echo '- [' . $p['name'] . "]({$origin}/product?slug=" . rawurlencode((string)$p['slug']) . ")\n";
    }
    echo "\n";
}

echo "## Social\n- Instagram: " . BRAND_INSTAGRAM . "\n";
