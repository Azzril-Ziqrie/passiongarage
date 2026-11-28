<?php
/**
 * Gratis ongkir, diatur dari admin (admin/dashboard-free-shipping.php).
 * Disimpan sebagai satu baris JSON di admin_settings (key "free_shipping"), seperti site lock.
 *
 * Aturan: kalau aktif, cakupan cocok (semua / domestik / internasional), dan subtotal barang (USD)
 * mencapai minimum, ongkir jadi 0. Checkout menghitung ulang di server (checkout.php), jadi nilai
 * dari browser tidak dipercaya.
 */

const FREE_SHIPPING_KEY    = 'free_shipping';
const FREE_SHIPPING_SCOPES = [
    'all'           => 'All orders (Indonesia & international)',
    'domestic'      => 'Indonesia only',
    'international' => 'International only',
];

/** Selalu array lengkap: ['enabled' => bool, 'scope' => string, 'min_subtotal' => float (USD, 0 = tanpa minimum)]. */
function freeShippingSettings(PDO $pdo): array
{
    $settings = ['enabled' => false, 'scope' => 'all', 'min_subtotal' => 0.0];
    try {
        $stmt = $pdo->prepare('SELECT setting_value FROM admin_settings WHERE setting_key = :k LIMIT 1');
        $stmt->execute(['k' => FREE_SHIPPING_KEY]);
        $saved = json_decode((string)$stmt->fetchColumn(), true);
    } catch (Throwable $e) {
        $saved = null; // tabel belum ada → nonaktif
    }
    if (is_array($saved)) {
        $settings['enabled']      = !empty($saved['enabled']);
        $settings['scope']        = isset(FREE_SHIPPING_SCOPES[$saved['scope'] ?? '']) ? (string)$saved['scope'] : 'all';
        $settings['min_subtotal'] = max(0.0, round((float)($saved['min_subtotal'] ?? 0), 2));
    }
    return $settings;
}

function freeShippingSave(PDO $pdo, bool $enabled, string $scope, float $minSubtotal): void
{
    if (!isset(FREE_SHIPPING_SCOPES[$scope])) {
        throw new InvalidArgumentException('Unknown free shipping scope.');
    }
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS admin_settings (
            setting_key VARCHAR(50) NOT NULL,
            setting_value TEXT NOT NULL,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );
    $pdo->prepare(
        'INSERT INTO admin_settings (setting_key, setting_value) VALUES (:k, :v)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    )->execute(['k' => FREE_SHIPPING_KEY, 'v' => json_encode([
        'enabled'      => $enabled,
        'scope'        => $scope,
        'min_subtotal' => max(0.0, round($minSubtotal, 2)),
    ])]);
}

/** $mode: 'domestic' atau lainnya (= internasional), sama dengan shipping_mode di checkout. */
function freeShippingApplies(array $settings, string $mode, float $subtotalUsd): bool
{
    if (!$settings['enabled']) {
        return false;
    }
    $isDomestic = $mode === 'domestic';
    if (($settings['scope'] === 'domestic' && !$isDomestic) || ($settings['scope'] === 'international' && $isDomestic)) {
        return false;
    }
    return $subtotalUsd + 0.000001 >= $settings['min_subtotal'];
}

/** Kalimat untuk pembeli, mis. "Free shipping within Indonesia on orders of $150 or more." Kosong kalau nonaktif. */
function freeShippingSummary(array $settings): string
{
    if (!$settings['enabled']) {
        return '';
    }
    $what = [
        'all'           => 'Free shipping',
        'domestic'      => 'Free shipping within Indonesia',
        'international' => 'Free international shipping',
    ][$settings['scope']];
    $min = $settings['min_subtotal'] > 0
        ? ' on orders of $' . number_format($settings['min_subtotal'], fmod($settings['min_subtotal'], 1.0) == 0.0 ? 0 : 2) . ' or more'
        : ' on every order';
    return $what . $min . '.';
}
