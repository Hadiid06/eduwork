<?php

require 'koneksi_db.php';
require 'keranjang_helper.php';

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function rupiah($amount): string
{
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}

$cartId = nusamartCartId($pdo);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    nusamartReserveCartItems($pdo, $cartId);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $itemId = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);

    if (!nusamartValidCsrfToken($_POST['csrf_token'] ?? null)) {
        nusamartSetFlash('error', 'Permintaan tidak dapat diverifikasi. Silakan coba lagi.');
    } elseif (!$itemId || $itemId < 1 || !in_array($action, ['update', 'remove'], true)) {
        nusamartSetFlash('error', 'Aksi keranjang tidak valid.');
    } else {
        try {
            $pdo->beginTransaction();
            $itemQuery = $pdo->prepare(
                'SELECT ci.id, ci.product_id, ci.quantity, ci.stock_reserved, p.stock
                 FROM cart_items ci
                 INNER JOIN products p ON p.id = ci.product_id
                 WHERE ci.id = :item_id AND ci.cart_id = :cart_id
                 LIMIT 1
                 FOR UPDATE'
            );
            $itemQuery->execute([
                'item_id' => $itemId,
                'cart_id' => $cartId,
            ]);
            $item = $itemQuery->fetch();

            if (!$item) {
                $pdo->rollBack();
                nusamartSetFlash('error', 'Produk tersebut sudah tidak ada di keranjang.');
            } elseif ($action === 'remove') {
                if ((int) $item['stock_reserved'] === 1) {
                    $releaseStock = $pdo->prepare(
                        'UPDATE products SET stock = stock + :quantity WHERE id = :id'
                    );
                    $releaseStock->execute([
                        'quantity' => $item['quantity'],
                        'id' => $item['product_id'],
                    ]);
                }

                $removeItem = $pdo->prepare(
                    'DELETE FROM cart_items WHERE id = :item_id AND cart_id = :cart_id'
                );
                $removeItem->execute([
                    'item_id' => $itemId,
                    'cart_id' => $cartId,
                ]);
                $pdo->commit();
                nusamartSetFlash('success', 'Produk dihapus dari keranjang.');
            } else {
                $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
                if ($quantity === false || $quantity === null || $quantity < 1) {
                    $pdo->rollBack();
                    nusamartSetFlash('error', 'Jumlah produk harus minimal 1.');
                } else {
                    $wasReserved = (int) $item['stock_reserved'] === 1;
                    $stockDelta = $wasReserved
                        ? $quantity - (int) $item['quantity']
                        : $quantity;

                    if ($stockDelta > (int) $item['stock']) {
                        $pdo->rollBack();
                        nusamartSetFlash(
                            'error',
                            'Stok tidak mencukupi. Maksimal '
                                . ((int) $item['quantity'] + (int) $item['stock'])
                                . ' unit tersedia untuk item ini.'
                        );
                    } else {
                        if ($stockDelta !== 0) {
                            $adjustStock = $stockDelta > 0
                                ? $pdo->prepare(
                                    'UPDATE products SET stock = stock - :quantity WHERE id = :id'
                                )
                                : $pdo->prepare(
                                    'UPDATE products SET stock = stock + :quantity WHERE id = :id'
                                );
                            $adjustStock->execute([
                                'quantity' => abs($stockDelta),
                                'id' => $item['product_id'],
                            ]);
                        }

                        $updateItem = $pdo->prepare(
                            'UPDATE cart_items
                             SET quantity = :quantity, stock_reserved = 1
                             WHERE id = :item_id AND cart_id = :cart_id'
                        );
                        $updateItem->execute([
                            'quantity' => $quantity,
                            'item_id' => $itemId,
                            'cart_id' => $cartId,
                        ]);
                        $pdo->commit();
                        nusamartSetFlash('success', 'Jumlah produk berhasil diperbarui.');
                    }
                }
            }
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    header('Location: keranjang.php');
    exit;
}

$itemsQuery = $pdo->prepare(
    'SELECT ci.id AS item_id, ci.quantity, ci.stock_reserved,
            p.id AS product_id, p.name,
            p.price, p.stock, p.visual, p.image_url, c.name AS category_name
     FROM cart_items ci
     INNER JOIN products p ON p.id = ci.product_id
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE ci.cart_id = :cart_id
     ORDER BY ci.created_at DESC'
);
$itemsQuery->execute(['cart_id' => $cartId]);
$items = $itemsQuery->fetchAll();
$cartCount = nusamartCartCount($pdo, $cartId);
$total = 0;
foreach ($items as $item) {
    $total += (float) $item['price'] * (int) $item['quantity'];
}

$flash = nusamartGetFlash();
$csrfToken = nusamartCsrfToken();

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7f8fc">
    <title>Keranjang | NusaMart</title>
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
                <a class="side-link" href="index.php#products"><span class="nav-icon">▦</span> Jelajahi produk</a>
                <a class="side-link" href="index.php#categories"><span class="nav-icon">◈</span> Kategori</a>
                <a class="side-link active" href="keranjang.php"><span class="nav-icon">▱</span> Keranjang</a>
            </nav>
            <div class="side-promo">
                <span class="side-promo-icon">✦</span>
                <strong>Belanja lebih seru</strong>
                <p>Temukan produk pilihan untuk menemani harimu.</p>
                <a href="index.php#products">Lanjut belanja <span aria-hidden="true">→</span></a>
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
                    <a class="cart-link" href="keranjang.php" aria-current="page">
                        <span aria-hidden="true">▱</span><span>Keranjang</span>
                        <span class="cart-count"><?= $cartCount ?></span>
                    </a>
                </div>
            </header>

            <main class="content">
                <div class="breadcrumb"><a href="index.php">Beranda</a><span>/</span><span>Keranjang</span></div>
                <section class="section-heading cart-page-heading">
                    <div>
                        <p class="eyebrow">SIAP MENJADI MILIKMU</p>
                        <h1>Keranjang belanja</h1>
                    </div>
                    <span class="result-count"><?= $cartCount ?> barang</span>
                </section>

                <?php if ($flash): ?>
                    <div class="notice notice-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
                <?php endif; ?>

                <?php if ($items): ?>
                    <div class="cart-layout">
                        <section class="cart-items" aria-label="Produk di keranjang">
                            <form id="checkout-selection" action="checkout.php" method="post">
                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                <input type="hidden" name="checkout_selection" value="1">
                            </form>
                            <?php foreach ($items as $index => $item): ?>
                                <article class="cart-item">
                                    <label class="cart-select">
                                        <input
                                            type="checkbox"
                                            name="selected_items[]"
                                            value="<?= (int) $item['item_id'] ?>"
                                            form="checkout-selection"
                                            <?= (int) $item['stock_reserved'] === 1 ? '' : 'disabled' ?>
                                        >
                                        <span class="sr-only">Pilih <?= e($item['name']) ?> untuk dibayar</span>
                                    </label>
                                    <a class="cart-item-visual tone-<?= $index % 6 ?>" href="detail_produk.php?id=<?= (int) $item['product_id'] ?>">
                                        <?php if (!empty($item['image_url'])): ?>
                                            <img src="<?= e($item['image_url']) ?>" alt="">
                                        <?php else: ?>
                                            <span aria-hidden="true"><?= e($item['visual']) ?></span>
                                        <?php endif; ?>
                                    </a>
                                    <div class="cart-item-info">
                                        <span class="product-category"><?= e($item['category_name'] ?? 'Pilihan') ?></span>
                                        <h2><a href="detail_produk.php?id=<?= (int) $item['product_id'] ?>"><?= e($item['name']) ?></a></h2>
                                        <p><?= rupiah($item['price']) ?> · <?= (int) $item['stock'] ?> tersedia di luar keranjang</p>
                                        <?php if ((int) $item['stock_reserved'] !== 1): ?>
                                            <p class="reservation-warning">Stok untuk jumlah ini belum tersedia. Kurangi jumlah atau hapus produk.</p>
                                        <?php else: ?>
                                            <p class="reservation-status">Stok produk ini sudah dicadangkan untuk keranjangmu.</p>
                                        <?php endif; ?>
                                        <div class="cart-item-actions">
                                            <form class="quantity-form" action="keranjang.php" method="post">
                                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                <input type="hidden" name="item_id" value="<?= (int) $item['item_id'] ?>">
                                                <label class="sr-only" for="quantity-<?= (int) $item['item_id'] ?>">Jumlah <?= e($item['name']) ?></label>
                                                <input
                                                    id="quantity-<?= (int) $item['item_id'] ?>"
                                                    type="number"
                                                    name="quantity"
                                                    min="1"
                                                    max="<?= (int) $item['stock'] + ((int) $item['stock_reserved'] === 1 ? (int) $item['quantity'] : 0) ?>"
                                                    value="<?= (int) $item['quantity'] ?>"
                                                    required
                                                >
                                                <button class="text-button" type="submit" name="action" value="update">Perbarui</button>
                                            </form>
                                            <form action="keranjang.php" method="post">
                                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                <input type="hidden" name="item_id" value="<?= (int) $item['item_id'] ?>">
                                                <button class="text-button remove-button" type="submit" name="action" value="remove">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                    <strong class="cart-line-total"><?= rupiah((float) $item['price'] * (int) $item['quantity']) ?></strong>
                                </article>
                            <?php endforeach; ?>
                        </section>

                        <aside class="cart-summary">
                            <p class="eyebrow">RINGKASAN PESANAN</p>
                            <h2>Pilih barang untuk dibayar</h2>
                            <div class="summary-row"><span><?= $cartCount ?> barang</span><span><?= rupiah($total) ?></span></div>
                            <button class="primary-button summary-button" type="submit" form="checkout-selection">Bayar barang terpilih <span aria-hidden="true">→</span></button>
                            <a class="secondary-button cart-continue-button" href="index.php#products">Lanjut belanja</a>
                            <p class="summary-note">Centang satu atau lebih produk yang ingin dibayar. Produk lain tetap tersimpan di keranjang.</p>
                        </aside>
                    </div>
                <?php else: ?>
                    <div class="empty-state cart-empty-state">
                        <span aria-hidden="true">▱</span>
                        <h2>Keranjangmu masih kosong</h2>
                        <p>Temukan produk pilihan dan tambahkan ke keranjang untuk melihatnya di sini.</p>
                        <a class="primary-button" href="index.php#products">Jelajahi produk <span aria-hidden="true">→</span></a>
                    </div>
                <?php endif; ?>

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
