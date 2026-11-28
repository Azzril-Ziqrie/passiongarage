<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/brand.php';
require_once __DIR__ . '/../includes/free_shipping.php';

$message = '';
$error   = '';

function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!hash_equals(ddAdminCsrf(), (string)($_POST['csrf'] ?? ''))) {
            throw new RuntimeException('Session expired. Please reload and try again.');
        }
        $scope = (string)($_POST['scope'] ?? 'all');
        if (!isset(FREE_SHIPPING_SCOPES[$scope])) {
            throw new RuntimeException('Choose where free shipping applies.');
        }
        $minRaw = str_replace(',', '.', trim((string)($_POST['min_subtotal'] ?? '')));
        if ($minRaw !== '' && (!is_numeric($minRaw) || (float)$minRaw < 0)) {
            throw new RuntimeException('Minimum order must be a number of 0 or more.');
        }
        freeShippingSave($pdo, !empty($_POST['enabled']), $scope, (float)($minRaw === '' ? 0 : $minRaw));
        $message = 'Free shipping saved.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$fs      = freeShippingSettings($pdo);
$summary = freeShippingSummary($fs);
$csrf    = ddAdminCsrf();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Free Shipping — <?= h(BRAND_NAME) ?> Admin</title>
  <style>
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; }
    body { font-family: Arial, Helvetica, sans-serif; background: #ffffff; color: #111; }
    a { color: inherit; text-decoration: none; }

    .topbar {
      height: 74px;
      display: grid;
      grid-template-columns: 1fr auto 1fr;
      align-items: center;
      padding: 0 22px;
      background: #ffffff;
      border-bottom: 1px solid #d8d5ce;
      position: sticky;
      top: 0;
      z-index: 20;
    }
    .topbar__left, .topbar__right {
      display: flex;
      align-items: center;
      gap: 18px;
    }
    .topbar__right { justify-content: flex-end; }
    .logo { width: 140px; justify-self: center; display: flex; align-items: center; justify-content: center; }
    .logo img { width: 100%; height: 36px; object-fit: contain; }
    .nav-link { font-size: 12px; text-transform: uppercase; letter-spacing: .6px; }

    .wrap { max-width: 1440px; margin: 0 auto; border-left: 1px solid #d8d5ce; border-right: 1px solid #d8d5ce; min-height: calc(100vh - 74px); }

    .hero { padding: 22px 20px; border-bottom: 1px solid #d8d5ce; display: grid; grid-template-columns: 1.2fr .8fr; gap: 0; }
    .hero-left, .hero-right { min-width: 0; }
    .hero-right { border-left: 1px solid #d8d5ce; padding-left: 20px; }
    .eyebrow { font-size: 12px; text-transform: uppercase; letter-spacing: .7px; margin-bottom: 14px; }
    .headline { margin: 0; font-size: 36px; line-height: 1.08; text-transform: uppercase; font-weight: 400; max-width: 620px; }
    .subcopy { margin: 18px 0 0; max-width: 560px; font-size: 14px; line-height: 1.7; color: #555; }
    .hero-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 24px; }
    .btn, .btn-secondary {
      height: 48px;
      border: 1px solid #111;
      background: #111;
      color: #fff;
      padding: 0 18px;
      text-transform: uppercase;
      font-size: 12px;
      letter-spacing: .6px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }
    .btn-secondary { background: #faf9f5; color: #111; border-color: #d8d5ce; }
    .btn-secondary:hover { background: #f4f2ec; }
    .btn-sm { height: 36px; padding: 0 14px; font-size: 10px; }
    .btn-danger { background: #fff; color: #c62828; border-color: #c62828; }
    .btn-danger:hover { background: #c62828; color: #fff; }

    .section { min-width: 0; border-bottom: 1px solid #d8d5ce; }
    .section-head {
      padding: 18px 20px;
      border-bottom: 1px solid #d8d5ce;
      text-transform: uppercase;
      letter-spacing: .7px;
      font-size: 13px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      background: #faf9f5;
    }
    .section-body { padding: 20px; background: #ffffff; }

    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .form-group { margin-bottom: 14px; }
    .form-group.full { grid-column: 1 / -1; }
    .form-group label { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 6px; color: #555; }
    .form-input, .form-textarea, .form-file {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #d8d5ce;
      background: #fff;
      font-size: 13px;
      font-family: inherit;
    }
    .form-textarea { resize: vertical; min-height: 60px; }
    .form-row-check { display: flex; align-items: center; gap: 8px; }
    .form-row-check input { width: 16px; height: 16px; }
    .form-help { font-size: 11px; color: #888; margin-top: 4px; text-transform: none; }

    .alert { padding: 12px 14px; font-size: 12px; text-transform: none; letter-spacing: .02em; margin: 16px 20px 0; border: 1px solid #d8d5ce; }
    .alert.success { background: #f0f7f0; color: #2e7d32; border-color: #2e7d32; }
    .alert.error   { background: #fdf0f0; color: #c62828; border-color: #c62828; }

    .table-wrap { overflow: auto; border: 1px solid #d8d5ce; background: #faf9f5; }
    table { width: 100%; border-collapse: collapse; min-width: 720px; }
    th, td {
      border-bottom: 1px solid #e8e6df;
      padding: 14px 12px;
      text-align: left;
      font-size: 13px;
      vertical-align: middle;
    }
    th { text-transform: uppercase; letter-spacing: .6px; font-size: 12px; background: #f4f2ec; }
    .thumb-mini { width: 100px; height: 56px; border: 1px solid #e0ddd5; background: #fff; object-fit: cover; }

    .status { display: inline-block; padding: 4px 8px; border: 1px solid #d8d5ce; font-size: 10px; text-transform: uppercase; letter-spacing: .5px; background: #faf9f5; }
    .status.live { border-color: #111; background: #f4f2ec; }
    .row-actions { display: flex; gap: 6px; flex-wrap: wrap; }
    .row-actions form { display: inline; }

    .preview-img { max-width: 240px; max-height: 160px; object-fit: contain; background:#f4f4f4; border: 1px solid #d8d5ce; margin-top: 8px; }
    .thumb-mini { width: 56px; height: 72px; object-fit: cover; }

    @media (max-width: 980px) {
      .hero { grid-template-columns: 1fr; }
      .hero-right { border-left: 0; padding-left: 0; margin-top: 20px; }
      .form-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
      .topbar { padding: 0 16px; }
      .headline { font-size: 28px; }
    }
  </style>
  <link rel="stylesheet" href="admin-theme.css?v=1">
</head>
<body>
  <header class="topbar">
    <div class="topbar__left">
      <a href="dashboard.php" class="nav-link">Dashboard</a>
      <a href="../index.php" class="nav-link">Storefront</a>
    </div>
    <a href="dashboard.php" class="logo" aria-label="<?= h(BRAND_NAME) ?> Admin">
      <span class="brand"><span class="brand__name"><?= h(BRAND_NAME) ?></span></span>
    </a>
    <div class="topbar__right"><span class="nav-link">Free shipping</span></div>
  </header>

  <main class="wrap">
    <section class="hero">
      <div class="hero-left">
        <div class="eyebrow"><?= h(BRAND_NAME) ?> Admin</div>
        <h1 class="headline">Free Shipping</h1>
        <p class="subcopy">
          Turn on free shipping for every order, or only for orders above a minimum amount.
          Checkout drops the shipping cost to $0 when an order qualifies, and the Shipping &amp; Returns page shows the offer.
        </p>
      </div>
      <div class="hero-right">
        <div class="eyebrow">Status</div>
        <p class="subcopy" style="margin-top:0;">
          <strong><?= $fs['enabled'] ? 'On' : 'Off' ?></strong><br>
          <?= $summary !== '' ? h($summary) : 'Customers pay the courier rate shown at checkout.' ?>
        </p>
      </div>
    </section>

    <?php if ($message): ?><div class="alert success"><?= h($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>

    <section class="section">
      <div class="section-head"><span>Settings</span></div>
      <div class="section-body">
        <form method="POST" action="dashboard-free-shipping.php" autocomplete="off">
          <input type="hidden" name="csrf" value="<?= h($csrf) ?>">

          <div class="form-grid">
            <div class="form-group full form-row-check">
              <input type="checkbox" name="enabled" id="enabled" value="1" <?= $fs['enabled'] ? 'checked' : '' ?>>
              <label for="enabled" style="margin:0;">Offer free shipping</label>
            </div>

            <div class="form-group">
              <label for="scope">Applies to</label>
              <select name="scope" id="scope" class="form-input">
                <?php foreach (FREE_SHIPPING_SCOPES as $value => $label): ?>
                  <option value="<?= h($value) ?>" <?= $fs['scope'] === $value ? 'selected' : '' ?>><?= h($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="min_subtotal">Minimum order (USD)</label>
              <input type="number" name="min_subtotal" id="min_subtotal" class="form-input" min="0" step="0.01"
                     value="<?= $fs['min_subtotal'] > 0 ? h(rtrim(rtrim(number_format($fs['min_subtotal'], 2, '.', ''), '0'), '.')) : '' ?>" placeholder="0">
              <p class="form-help">Item subtotal before shipping. Leave empty or 0 for free shipping on every order.</p>
            </div>
          </div>

          <div class="hero-actions"><button type="submit" class="btn">Save</button></div>
        </form>
      </div>
    </section>
  </main>
</body>
</html>
