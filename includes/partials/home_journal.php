<?php /** Journal: deretan foto 16:9 rata kiri yang bisa digeser ke kanan; diatur dari admin (Homepage Media → Journal). Variabel: $journal. */ ?>
<section class="journal" aria-label="Journal">
  <div class="journal-strip" role="region" aria-label="Journal photos" tabindex="0">
    <?php foreach ($journal as $item):
        $hasLink = !empty($item['link']);
        $tag     = $hasLink ? 'a' : 'div';
    ?>
    <<?= $tag ?> class="journal-card"<?= $hasLink ? ' href="' . esc($item['link']) . '"' : '' ?>>
      <img src="<?= esc($item['media']) ?>" alt="<?= esc($item['title'] ?: BRAND_NAME) ?>" loading="lazy" decoding="async">
      <?php if (HOME_JOURNAL_SHOW_TEXT): ?>
      <?php if (!empty($item['title'])): ?><span class="journal-card__title"><?= esc($item['title']) ?></span><?php endif; ?>
      <?php if (!empty($item['subtitle'])): ?><span class="journal-card__sub"><?= esc($item['subtitle']) ?></span><?php endif; ?>
      <?php if ($hasLink): ?><span class="journal-card__more link-u">Read more</span><?php endif; ?>
      <?php endif; ?>
    </<?= $tag ?>>
    <?php endforeach; ?>
  </div>
</section>
