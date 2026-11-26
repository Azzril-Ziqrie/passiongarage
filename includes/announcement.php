<?php
/**
 * Announcement bar di paling atas halaman depan (di atas navbar). Diatur dari admin (admin/dashboard-announcement.php),
 * disimpan sebagai satu baris JSON di admin_settings (key "announcement").
 */

const ANNOUNCEMENT_KEY = 'announcement';

const ANNOUNCEMENT_ANIMATIONS = [
    'marquee' => 'Scrolling ticker',
    'slide'   => 'Slide up, one message at a time',
    'fade'    => 'Fade, one message at a time',
    'drift'   => 'Fade + drift right, then hold still',
    'static'  => 'No animation',
];

const ANNOUNCEMENT_DEFAULTS = [
    'enabled'   => false,
    'messages'  => [],
    'link'      => '',
    'animation' => 'marquee',
    'speed'     => 25,
];

/** Pengaturan tersimpan, digabung dengan default; selalu array lengkap. */
function announcementSettings(PDO $pdo): array
{
    $settings = ANNOUNCEMENT_DEFAULTS;
    if (!tableExists($pdo, 'admin_settings')) {
        return $settings;
    }
    try {
        $stmt = $pdo->prepare('SELECT setting_value FROM admin_settings WHERE setting_key = :k LIMIT 1');
        $stmt->execute(['k' => ANNOUNCEMENT_KEY]);
        $saved = json_decode((string)$stmt->fetchColumn(), true);
    } catch (Throwable $e) {
        error_log('[announcement] read failed: ' . $e->getMessage());
        return $settings;
    }
    return is_array($saved) ? announcementNormalize($saved) : $settings;
}

/** Rapikan input (form atau JSON tersimpan) ke bentuk yang aman dipakai. */
function announcementNormalize(array $in): array
{
    $messages = $in['messages'] ?? [];
    if (is_string($messages)) {
        $messages = preg_split('/\R/', $messages) ?: [];
    }
    $messages = array_values(array_filter(array_map(
        static fn($m) => mb_substr(trim((string)$m), 0, 160),
        (array)$messages
    ), static fn($m) => $m !== ''));

    $animation = (string)($in['animation'] ?? '');

    return [
        'enabled'   => !empty($in['enabled']),
        'messages'  => array_slice($messages, 0, 10),
        'link'      => trim((string)($in['link'] ?? '')),
        'animation' => isset(ANNOUNCEMENT_ANIMATIONS[$animation]) ? $animation : ANNOUNCEMENT_DEFAULTS['animation'],
        'speed'     => max(2, min(120, (int)($in['speed'] ?? ANNOUNCEMENT_DEFAULTS['speed']))),
    ];
}

function announcementSave(PDO $pdo, array $settings): void
{
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
    )->execute(['k' => ANNOUNCEMENT_KEY, 'v' => json_encode($settings, JSON_UNESCAPED_UNICODE)]);
}

/** Pengaturan yang siap tampil, atau null bila bar mati / tidak ada pesan. */
function announcementForDisplay(PDO $pdo): ?array
{
    $settings = announcementSettings($pdo);
    return $settings['enabled'] && $settings['messages'] ? $settings : null;
}

/** Animasi yang menampilkan satu pesan sekaligus dan berganti tiap $speed detik. */
const ANNOUNCEMENT_ROTATING = ['slide', 'fade', 'drift'];

/** HTML bar. $a berasal dari announcementForDisplay(). */
function renderAnnouncement(array $a): string
{
    $animation = $a['animation'];
    $speed     = (int)$a['speed'];
    $body      = $animation === 'marquee'
        ? announcementTrackHtml($a['messages'], $a['link'])
        : announcementStageHtml($a['messages'], $a['link'], $animation === 'drift');

    $rotates = in_array($animation, ANNOUNCEMENT_ROTATING, true) && count($a['messages']) > 1;

    return '<div class="announce announce--' . announcementEsc($animation) . '" role="region" aria-label="Announcement"'
         . ' style="--announce-speed:' . $speed . 's">'
         . $body
         . ($rotates ? announcementRotatorScript($speed * 1000) : '')
         . "</div>\n";
}

function announcementEsc($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function announcementMessageHtml(string $text, string $link): string
{
    return $link !== ''
        ? '<a class="announce__msg" href="' . announcementEsc($link) . '">' . announcementEsc($text) . '</a>'
        : '<span class="announce__msg">' . announcementEsc($text) . '</span>';
}

/** Ticker: dua grup identik bergeser selebar dirinya sendiri → putaran mulus tanpa JS. */
function announcementTrackHtml(array $messages, string $link): string
{
    $group = '';
    foreach ($messages as $m) {
        $group .= announcementMessageHtml($m, $link) . '<span class="announce__sep" aria-hidden="true">/</span>';
    }
    return '<div class="announce__track"><div class="announce__group">' . $group . '</div>'
         . '<div class="announce__group" aria-hidden="true">' . $group . '</div></div>';
}

/** Satu pesan per slide; pesan pertama langsung tampil (is-intro = animasi masuk saat halaman dibuka). */
function announcementStageHtml(array $messages, string $link, bool $withIntro): string
{
    $out = '<div class="announce__stage">';
    foreach ($messages as $i => $m) {
        $state = $i === 0 ? ' is-active' . ($withIntro ? ' is-intro' : '') : '';
        $out  .= '<div class="announce__slide' . $state . '">' . announcementMessageHtml($m, $link) . '</div>';
    }
    return $out . '</div>';
}

/**
 * Ganti pesan tiap $intervalMs. Slide berikutnya di-reset ke posisi awal tanpa transisi,
 * lalu masuk; slide lama keluar ke arah sebaliknya. Berhenti sementara saat di-hover/fokus.
 * Reduced motion tetap berganti — CSS mematikan geraknya sehingga tinggal cross-fade.
 */
function announcementRotatorScript(int $intervalMs): string
{
    return <<<HTML
<script>
(function () {
  var bar = document.currentScript.parentNode;
  var slides = bar.querySelectorAll('.announce__slide'), cur = 0, paused = false;
  function hold(v) { return function () { paused = v; }; }
  bar.addEventListener('pointerenter', hold(true));
  bar.addEventListener('pointerleave', hold(false));
  bar.addEventListener('focusin', hold(true));
  bar.addEventListener('focusout', hold(false));
  setInterval(function () {
    if (paused || document.hidden) return;
    var prev = slides[cur], next = slides[(cur + 1) % slides.length];
    next.classList.add('no-t'); next.classList.remove('is-out'); void next.offsetWidth; next.classList.remove('no-t');
    prev.classList.remove('is-active', 'is-intro'); prev.classList.add('is-out');
    next.classList.add('is-active');
    cur = (cur + 1) % slides.length;
  }, $intervalMs);
})();
</script>
HTML;
}
