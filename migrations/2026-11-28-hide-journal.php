<?php
// Deretan foto Journal (di bawah hero, di atas produk) disembunyikan. Hanya dinonaktifkan, bisa dinyalakan lagi dari admin.
return static function (PDO $pdo): void {
    $exists = $pdo->query(
        "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'home_media'"
    )->fetchColumn();
    if ((int) $exists > 0) {
        $pdo->exec("UPDATE home_media SET is_active = 0 WHERE placement = 'journal'");
    }
};
