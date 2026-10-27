<?php

require 'koneksi_db.php';
require 'keranjang_helper.php';

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(400);
    exit('ID produk tidak valid.');
}

$statement = $pdo->prepare(
    'SELECT p.*, c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.id = :id
     LIMIT 1'
);
$statement->execute(['id' => $id]);
$product = $statement->fetch();

if (!$product) {
    http_response_code(404);
    exit('Produk tidak ditemukan.');
}

$cartId = nusamartCartId($pdo);
$cartCount = nusamartCartCount($pdo, $cartId);
$flash = nusamartGetFlash();
$csrfToken = nusamartCsrfToken();

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7f8fc">
    <title><?= e($product['name']) ?> | NusaMart</title>
    <link rel="stylesheet" href="<?= e(nusamartStylesheetUrl()) ?>">
</head>

<body>
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="index.php" aria-label="NusaMart beranda">
                <span class="brand-mark">n</span>
                <span>Nusa<span class="brand-accent">Mart</span></span>
            </a>
            <div class="side-label">MENU</div>
            <nav class="side-nav" aria-label="Navigasi utama">
                <a class="side-link" href="index.php"><span class="nav-icon">⌂</span> Beranda</a>
                <a class="side-link active" href="index.php#products"><span class="nav-icon">▦</span> Jelajahi produk</a>
                <a class="side-link" href="index.php#categories"><span class="nav-icon">◈</span> Kategori</a>
            </nav>
            <div class="side-promo">
                <span class="side-promo-icon">✦</span>
                <strong>Belanja lebih seru</strong>
                <p>Temukan produk pilihan untuk menemani harimu.</p>
                <a href="index.php#products">Mulai jelajahi <span aria-hidden="true">→</span></a>
                <span class="promo-orbit orbit-one"></span>
                <span class="promo-orbit orbit-two"></span>
            </div>
            <div class="sidebar-footer">
                <span class="avatar">N</span>
                <span class="profile-copy"><strong>Pengunjung</strong><small>Selamat berbelanja</small></span>
                <span class="profile-more">···</span>
            </div>
        </aside>

        <div class="main-area">
            <header class="topbar">
                <a class="mobile-brand" href="index.php">
                    <span class="brand-mark">n</span>
                    <strong>Nusa<span class="brand-accent">Mart</span></strong>
                </a>
                <form class="search-box" action="index.php" method="get" role="search">
                    <span class="search-icon" aria-hidden="true">⌕</span>
                    <label class="sr-only" for="search">Cari produk</label>
                    <input id="search" name="search" type="search" placeholder="Cari produk, kategori, atau inspirasi...">
                    <kbd>Enter</kbd>
                </form>
                <div class="top-actions">
                    <span class="top-note">Belanja nyaman, setiap hari</span>
                    <a class="cart-link" href="keranjang.php">
                        <span aria-hidden="true">▱</span><span>Keranjang</span>
                        <span class="cart-count"><?= $cartCount ?></span>
                    </a>
                </div>
            </header>

            <main class="content">
                <div class="breadcrumb"><a href="index.php">Beranda</a><span>/</span><span>Detail produk</span></div>
                <?php if ($flash): ?>
                    <div class="notice notice-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
                <?php endif; ?>

                <section class="detail-card">
                    <div class="detail-visual tone-2">
                        <?php if (!empty($product['image_url'])): ?>
                            <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>">
                        <?php else: ?>
                            <span aria-hidden="true"><?= e($product['visual']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="detail-copy">
                        <p class="eyebrow"><?= e($product['category_name'] ?? 'Pilihan NusaMart') ?></p>
                        <?php if (!empty($product['badge'])): ?>
                            <span class="product-badge detail-badge"><?= e($product['badge']) ?></span>
                        <?php endif; ?>
                        <h1><?= e($product['name']) ?></h1>
                        <p class="detail-description"><?= nl2br(e($product['description'] ?: 'Belum ada deskripsi untuk produk ini.')) ?></p>
                        <div class="detail-price"><?= 'Rp ' . number_format((float) $product['price'], 0, ',', '.') ?></div>
                        <p class="detail-stock">
                            <?= (int) $product['stock'] > 0 ? (int) $product['stock'] . ' tersedia' : 'Stok habis' ?>
                        </p>
                        <form class="add-cart-form" action="tambah_keranjang.php" method="post">
                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <label for="quantity">Jumlah</label>
                            <div class="add-cart-controls">
                                <input
                                    type="number"
                                    id="quantity"
                                    name="quantity"
                                    min="1"
                                    max="<?= (int) $product['stock'] ?>"
                                    value="1"
                                    required
                                    <?= (int) $product['stock'] < 1 ? 'disabled' : '' ?>
                                >
                                <button class="primary-button" type="submit" name="action" value="add_to_cart" <?= (int) $product['stock'] < 1 ? 'disabled' : '' ?>>
                                    Tambah ke keranjang <span aria-hidden="true">→</span>
                                </button>
                                <button class="secondary-button" type="submit" name="action" value="buy_now" <?= (int) $product['stock'] < 1 ? 'disabled' : '' ?>>
                                    Beli langsung
                                </button>
                            </div>
                        </form>
                        <a class="back-link" href="index.php#products">← Kembali ke produk</a>
                    </div>
                </section>
                <footer class="page-footer">
                    <a class="footer-brand" href="index.php">Nusa<span>Mart</span></a>
                    <span>Temukan hal baik untuk hari ini.</span>
                    <span>© <?= date('Y') ?> NusaMart</span>
                </footer>
            </main>
        </div>
    </div>
</body>

</html>
