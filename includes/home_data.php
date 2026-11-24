<?php
/**
 * Data khusus halaman depan: tile kategori.
 * Dipisah dari index.php supaya halaman hanya mengatur urutan tampilan.
 */

/** Tile "Shop All" + satu tile per kategori yang punya produk; gambar = foto produk pertama di kategori itu. */
function buildCategoryTiles(array $products): array
{
    if (!$products) {
        return [];
    }
    $tiles = [['label' => 'Shop All', 'href' => 'shop.php', 'image' => (string)$products[0]['thumbnail']]];
    $seen  = [];
    foreach ($products as $product) {
        $slug = (string)($product['category_slug'] ?? '');
        if ($slug === '' || isset($seen[$slug])) {
            continue;
        }
        $seen[$slug] = true;
        $tiles[] = [
            'label' => sentenceCase((string)$product['category_name']),
            'href'  => 'index.php?category=' . urlencode($slug) . '#products',
            'image' => (string)$product['thumbnail'],
        ];
    }
    return $tiles;
}
