<?php /** Tile kategori. Variabel: $tiles (array [label, href, image]). */ ?>
<nav class="tiles" aria-label="Browse">
  <?php foreach ($tiles as $tile): ?>
  <a class="tile" href="<?= esc($tile['href']) ?>">
    <img src="<?= esc($tile['image']) ?>" alt="" loading="lazy" decoding="async">
    <span><?= esc($tile['label']) ?></span>
  </a>
  <?php endforeach; ?>
</nav>
