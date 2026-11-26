<?php
/**
 * Pengaturan site lock (password gate storefront). Diatur dari admin (admin/dashboard-site-lock.php),
 * disimpan sebagai satu baris JSON di admin_settings (key "site_lock"). Gate-nya: includes/site_gate.php.
 *
 * Password disimpan sebagai hash; huruf besar/kecil dan spasi diabaikan saat dicocokkan
 * ("Passion Garage" = "passiongarage").
 */

const SITE_LOCK_KEY              = 'site_lock';
const SITE_LOCK_DEFAULT_PASSWORD = 'passion garage';
const SITE_LOCK_MIN_LENGTH       = 4;
const SITE_LOCK_DEFAULT_MESSAGE  = 'Website under maintenance';

function siteLockNormalizePassword(string $password): string
{
    return mb_strtolower((string)preg_replace('/\s+/u', '', $password));
}

/**
 * Selalu array lengkap: ['enabled' => bool, 'hash' => string, 'message' => string].
 * hash kosong = password default. Belum pernah disimpan → terkunci dengan password default.
 */
function siteLockSettings(PDO $pdo): array
{
    $settings = ['enabled' => true, 'hash' => '', 'message' => SITE_LOCK_DEFAULT_MESSAGE];
    try {
        $stmt = $pdo->prepare('SELECT setting_value FROM admin_settings WHERE setting_key = :k LIMIT 1');
        $stmt->execute(['k' => SITE_LOCK_KEY]);
        $saved = json_decode((string)$stmt->fetchColumn(), true);
    } catch (Throwable $e) {
        $saved = null; // tabel belum ada → pakai default
    }
    if (is_array($saved)) {
        $settings['enabled'] = !empty($saved['enabled']);
        $settings['hash']    = (string)($saved['hash'] ?? '');
        $settings['message'] = (string)($saved['message'] ?? SITE_LOCK_DEFAULT_MESSAGE);
    }
    return $settings;
}

function siteLockVerify(array $settings, string $input): bool
{
    $input = siteLockNormalizePassword($input);
    return $settings['hash'] === ''
        ? hash_equals(siteLockNormalizePassword(SITE_LOCK_DEFAULT_PASSWORD), $input)
        : password_verify($input, $settings['hash']);
}

/** Penanda sesi: ganti password → semua pengunjung yang sudah masuk harus memasukkan password baru. */
function siteLockStamp(array $settings): string
{
    return $settings['hash'] === '' ? 'default' : substr(hash('sha256', $settings['hash']), 0, 16);
}

/** $newPassword null = password lama tetap dipakai. $message kosong = tanpa teks di bawah logo. */
function siteLockSave(PDO $pdo, bool $enabled, string $message, ?string $newPassword): void
{
    $current = siteLockSettings($pdo);
    $hash    = $newPassword !== null
        ? password_hash(siteLockNormalizePassword($newPassword), PASSWORD_DEFAULT)
        : $current['hash'];

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
    )->execute(['k' => SITE_LOCK_KEY, 'v' => json_encode(['enabled' => $enabled, 'hash' => $hash, 'message' => mb_substr(trim($message), 0, 120)], JSON_UNESCAPED_UNICODE)]);
}
