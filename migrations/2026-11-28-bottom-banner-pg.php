<?php
// Banner bawah (placement 'bottom') diganti 3 banner PassionGarage. Item bawah lama hanya dinonaktifkan, tidak dihapus.
require_once __DIR__ . '/../includes/home_media.php';

return static function (PDO $pdo): void {
    ensureHomeMediaTable($pdo);

    $banners = [
        'assets/images/home/pg-banner-6.webp',
        'assets/images/home/pg-banner-7.webp',
        'assets/images/home/pg-banner-8.webp',
    ];

    $in = implode(',', array_fill(0, count($banners), '?'));
    $pdo->prepare("UPDATE home_media SET is_active = 0 WHERE placement = 'bottom' AND media NOT IN ($in)")
        ->execute($banners);

    $exists = $pdo->prepare("SELECT id FROM home_media WHERE placement = 'bottom' AND media = ? LIMIT 1");
    $insert = $pdo->prepare(
        "INSERT INTO home_media (media, placement, width, height, sort_order, is_active)
         VALUES (?, 'bottom', 1920, 1080, ?, 1)"
    );
    $update = $pdo->prepare('UPDATE home_media SET sort_order = ?, is_active = 1 WHERE id = ?');
    foreach ($banners as $i => $media) {
        $exists->execute([$media]);
        $id = $exists->fetchColumn();
        $id === false ? $insert->execute([$media, $i]) : $update->execute([$i, $id]);
    }
};
