<?php
/** Identitas brand — satu-satunya tempat nama/logo PassionGarage didefinisikan. */

const BRAND_NAME    = 'PassionGarage';
const BRAND_URL    = 'https://passiongarage.co';   // domain utama, dipakai canonical, sitemap, JSON-LD
const BRAND_EST     = '2026';   // tahun berdiri, tampil di halaman About
const BRAND_TAGLINE = 'Your passion, our garage.';
const BRAND_LOGO    = 'assets/images/passiongarage-logo.png';   // hitam, transparan
const BRAND_ICON    = 'assets/images/passiongarage-icon.png';
const BRAND_MARK    = 'assets/images/passiongarage-dots.png';   // hanya tiga titik, hitam, transparan (navbar)

// Isi dengan URL profil Instagram brand; kosong = ikon Instagram disembunyikan di footer.
const BRAND_INSTAGRAM = 'https://www.instagram.com/passiongarage/';
const BRAND_WHATSAPP  = 'https://wa.me/6282197404484';

// Alamat email kontak (halaman Contact Us). Kosong = kartu email disembunyikan.
const BRAND_EMAIL = 'info@passiongarage.co';

// Kotak newsletter di footer (kirim ke newsletter.php). false = disembunyikan.
const BRAND_NEWSLETTER = true;

/**
 * Teks "about" brand: dipakai bagian intro di beranda dan halaman About supaya selalu sama.
 * Baris pertama adalah pernyataan brand; sisanya paragraf pendamping.
 *
 * @return string[]
 */
function brandAboutCopy(): array
{
    return [
        'PassionGarage — tulis pernyataan brand di sini.',
        'Paragraf tentang PassionGarage, bengkel dan layanannya.',
        'Paragraf pendamping.'
    ];
}
