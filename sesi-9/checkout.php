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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout_selection'])) {
    $selectedInput = $_POST['selected_items'] ?? [];
    $selectedIds = [];
    if (is_array($selectedInput)) {
        foreach ($selectedInput as $value) {
            $itemId = filter_var($value, FILTER_VALIDATE_INT);
            if ($itemId === false || $itemId < 1) {
                $selectedIds = [];
                break;
            }
            $selectedIds[] = $itemId;
        }
    }

    $selectedIds = array_values(array_unique($selectedIds));
    sort($selectedIds);

    if (!nusamartValidCsrfToken($_POST['csrf_token'] ?? null)) {
        nusamartSetFlash('error', 'Permintaan tidak dapat diverifikasi. Silakan coba lagi.');
        header('Location: keranjang.php');
        exit;
    }

    if (!$selectedIds) {
        nusamartSetFlash('error', 'Pilih minimal satu barang untuk melanjutkan pembayaran.');
        header('Location: keranjang.php');
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));

    try {
        $pdo->beginTransaction();
        $selectedQuery = $pdo->prepare(
            'SELECT ci.id, ci.quantity, ci.stock_reserved, p.id AS product_id,
                    p.name, p.price, p.stock
             FROM cart_items ci
             INNER JOIN products p ON p.id = ci.product_id
             WHERE ci.cart_id = ? AND ci.id IN (' . $placeholders . ')
             ORDER BY ci.product_id
             FOR UPDATE'
        );
        $selectedQuery->execute(array_merge([$cartId], $selectedIds));
        $selectedItems = $selectedQuery->fetchAll();

        if (count($selectedItems) !== count($selectedIds)) {
            $pdo->rollBack();
            nusamartSetFlash('error', 'Sebagian barang tidak lagi tersedia di keranjang.');
            header('Location: keranjang.php');
            exit;
        }

        $reserveProduct = $pdo->prepare(
            'UPDATE products SET stock = stock - :quantity WHERE id = :id'
        );
        $reserveItem = $pdo->prepare(
            'UPDATE cart_items SET stock_reserved = 1 WHERE id = :id AND cart_id = :cart_id'
        );
        foreach ($selectedItems as $item) {
            if ((int) $item['stock_reserved'] === 1) {
                continue;
            }

            if ((int) $item['stock'] < (int) $item['quantity']) {
                $pdo->rollBack();
                nusamartSetFlash(
                    'error',
                    'Stok ' . $item['name'] . ' tidak mencukupi untuk jumlah di keranjang.'
                );
                header('Location: keranjang.php');
                exit;
            }

            $reserveProduct->execute([
                'quantity' => $item['quantity'],
                'id' => $item['product_id'],
            ]);
            $reserveItem->execute([
                'id' => $item['id'],
                'cart_id' => $cartId,
            ]);
        }

        $pdo->commit();
        $_SESSION['nusamart_pending_checkout'] = [
            'type' => 'cart',
            'item_ids' => $selectedIds,
        ];
        header('Location: checkout.php');
        exit;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

$pending = $_SESSION['nusamart_pending_checkout'] ?? null;
$checkoutItems = [];
$checkoutType = '';

if (is_array($pending) && ($pending['type'] ?? null) === 'direct') {
    $productId = filter_var($pending['product_id'] ?? null, FILTER_VALIDATE_INT);
    $quantity = filter_var($pending['quantity'] ?? null, FILTER_VALIDATE_INT);
    if ($productId && $productId > 0 && $quantity && $quantity > 0) {
        $productQuery = $pdo->prepare(
            'SELECT id, name, price, stock, visual, image_url
             FROM products WHERE id = :id LIMIT 1'
        );
        $productQuery->execute(['id' => $productId]);
        $product = $productQuery->fetch();
        if ($product) {
            $checkoutItems[] = [
                'product_id' => (int) $product['id'],
                'name' => $product['name'],
                'price' => (float) $product['price'],
                'stock' => (int) $product['stock'],
                'visual' => $product['visual'],
                'image_url' => $product['image_url'],
                'quantity' => (int) $quantity,
            ];
            $checkoutType = 'direct';
        }
    }
} elseif (
    is_array($pending)
    && ($pending['type'] ?? null) === 'cart'
    && is_array($pending['item_ids'] ?? null)
    && $pending['item_ids']
) {
    $itemIds = array_values(array_unique(array_filter(
        array_map('intval', $pending['item_ids']),
        static fn (int $itemId): bool => $itemId > 0
    )));

    if ($itemIds) {
        $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
        $itemsQuery = $pdo->prepare(
            'SELECT ci.id AS item_id, ci.quantity, ci.stock_reserved,
                    p.id AS product_id, p.name, p.price, p.visual, p.image_url
             FROM cart_items ci
             INNER JOIN products p ON p.id = ci.product_id
             WHERE ci.cart_id = ? AND ci.id IN (' . $placeholders . ')
             ORDER BY ci.product_id'
        );
        $itemsQuery->execute(array_merge([$cartId], $itemIds));
        $cartItems = $itemsQuery->fetchAll();

        if (
            count($cartItems) === count($itemIds)
            && !array_filter($cartItems, static fn (array $item): bool => (int) $item['stock_reserved'] !== 1)
        ) {
            foreach ($cartItems as $item) {
                $checkoutItems[] = [
                    'item_id' => (int) $item['item_id'],
                    'product_id' => (int) $item['product_id'],
                    'name' => $item['name'],
                    'price' => (float) $item['price'],
                    'visual' => $item['visual'],
                    'image_url' => $item['image_url'],
                    'quantity' => (int) $item['quantity'],
                ];
            }
            $checkoutType = 'cart';
        }
    }
}

if (!$checkoutItems || $checkoutType === '') {
    unset($_SESSION['nusamart_pending_checkout']);
    nusamartSetFlash('error', 'Pilih barang dari keranjang atau gunakan tombol Beli langsung.');
    header('Location: keranjang.php');
    exit;
}

$flash = null;
$customerName = '';
$email = '';
$phone = '';
$shippingAddress = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = trim(is_string($_POST['customer_name'] ?? null) ? $_POST['customer_name'] : '');
    $email = trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : '');
    $phone = trim(is_string($_POST['phone'] ?? null) ? $_POST['phone'] : '');
    $shippingAddress = trim(is_string($_POST['shipping_address'] ?? null) ? $_POST['shipping_address'] : '');

    if (!nusamartValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $flash = 'Permintaan tidak dapat diverifikasi. Muat ulang halaman dan coba lagi.';
    } elseif ($customerName === '' || strlen($customerName) > 120) {
        $flash = 'Nama penerima wajib diisi dan maksimal 120 karakter.';
    } elseif ($email !== '' && (strlen($email) > 190 || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
        $flash = 'Format email tidak valid.';
    } elseif ($phone === '' || strlen($phone) > 30) {
        $flash = 'Nomor telepon wajib diisi dan maksimal 30 karakter.';
    } elseif ($shippingAddress === '') {
        $flash = 'Alamat pengiriman wajib diisi.';
    } else {
        try {
            $pdo->beginTransaction();
            $lockedItems = [];

            if ($checkoutType === 'direct') {
                $item = $checkoutItems[0];
                $lockedProductQuery = $pdo->prepare(
                    'SELECT id, name, price, stock
                     FROM products WHERE id = :id LIMIT 1 FOR UPDATE'
                );
                $lockedProductQuery->execute(['id' => $item['product_id']]);
                $lockedProduct = $lockedProductQuery->fetch();

                if (!$lockedProduct || (int) $lockedProduct['stock'] < $item['quantity']) {
                    $pdo->rollBack();
                    $flash = 'Stok produk berubah. Silakan kembali ke detail produk.';
                } else {
                    $lockedItems[] = [
                        'product_id' => (int) $lockedProduct['id'],
                        'name' => $lockedProduct['name'],
                        'price' => (float) $lockedProduct['price'],
                        'quantity' => $item['quantity'],
                        'reserved' => false,
                    ];
                }
            } else {
                $itemIds = array_column($checkoutItems, 'item_id');
                $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
                $lockedItemsQuery = $pdo->prepare(
                    'SELECT ci.id AS item_id, ci.quantity, ci.stock_reserved,
                            p.id AS product_id, p.name, p.price
                     FROM cart_items ci
                     INNER JOIN products p ON p.id = ci.product_id
                     WHERE ci.cart_id = ? AND ci.id IN (' . $placeholders . ')
                     ORDER BY ci.product_id
                     FOR UPDATE'
                );
                $lockedItemsQuery->execute(array_merge([$cartId], $itemIds));
                $rows = $lockedItemsQuery->fetchAll();

                if (
                    count($rows) !== count($itemIds)
                    || array_filter($rows, static fn (array $row): bool => (int) $row['stock_reserved'] !== 1)
                ) {
                    $pdo->rollBack();
                    $flash = 'Barang terpilih berubah atau stoknya tidak lagi dicadangkan. Silakan pilih ulang di keranjang.';
                } else {
                    foreach ($rows as $row) {
                        $lockedItems[] = [
                            'item_id' => (int) $row['item_id'],
                            'product_id' => (int) $row['product_id'],
                            'name' => $row['name'],
                            'price' => (float) $row['price'],
                            'quantity' => (int) $row['quantity'],
                            'reserved' => true,
                        ];
                    }
                }
            }

            if ($flash === null) {
                $subtotal = 0;
                foreach ($lockedItems as $item) {
                    $subtotal += $item['price'] * $item['quantity'];
                }

                $orderQuery = $pdo->prepare(
                    'INSERT INTO orders (
                        customer_name, email, phone, shipping_address,
                        subtotal, shipping_cost, total_price
                     ) VALUES (
                        :customer_name, :email, :phone, :shipping_address,
                        :subtotal, 0, :total_price
                     )'
                );
                $orderQuery->execute([
                    'customer_name' => $customerName,
                    'email' => $email !== '' ? $email : null,
                    'phone' => $phone,
                    'shipping_address' => $shippingAddress,
                    'subtotal' => $subtotal,
                    'total_price' => $subtotal,
                ]);
                $orderId = (int) $pdo->lastInsertId();

                $orderItemQuery = $pdo->prepare(
                    'INSERT INTO order_items (
                        order_id, product_id, product_name, quantity, unit_price, total_price
                     ) VALUES (
                        :order_id, :product_id, :product_name, :quantity, :unit_price, :total_price
                     )'
                );
                $decreaseStock = $pdo->prepare(
                    'UPDATE products SET stock = stock - :quantity WHERE id = :id'
                );
                foreach ($lockedItems as $item) {
                    $lineTotal = $item['price'] * $item['quantity'];
                    $orderItemQuery->execute([
                        'order_id' => $orderId,
                        'product_id' => $item['product_id'],
                        'product_name' => $item['name'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'total_price' => $lineTotal,
                    ]);

                    if (!$item['reserved']) {
                        $decreaseStock->execute([
                            'quantity' => $item['quantity'],
                            'id' => $item['product_id'],
                        ]);
                    }
                }

                if ($checkoutType === 'cart') {
                    $deleteItems = $pdo->prepare(
                        'DELETE FROM cart_items
                         WHERE cart_id = ? AND id IN (' . $placeholders . ')
                           AND stock_reserved = 1'
                    );
                    $deleteItems->execute(array_merge([$cartId], $itemIds));
                    if ($deleteItems->rowCount() !== count($itemIds)) {
                        throw new RuntimeException('Barang terpilih tidak dapat dihapus setelah checkout.');
                    }
                }

                $pdo->commit();
                $_SESSION['nusamart_invoice_ids'] ??= [];
                $_SESSION['nusamart_invoice_ids'][] = $orderId;
                $_SESSION['nusamart_invoice_ids'] = array_slice(
                    array_values(array_unique($_SESSION['nusamart_invoice_ids'])),
                    -20
                );
                unset($_SESSION['nusamart_pending_checkout']);
                header('Location: invoice.php?id=' . $orderId);
                exit;
            }
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }
}

$csrfToken = nusamartCsrfToken();
$subtotal = 0;
foreach ($checkoutItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7f8fc">
    <title>Konfirmasi pembelian | NusaMart</title>
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
                <a class="side-link" href="keranjang.php"><span class="nav-icon">▱</span> Keranjang</a>
            </nav>
            <div class="side-promo">
                <span class="side-promo-icon">✦</span>
                <strong>Belanja lebih seru</strong>
                <p>Lengkapi alamat agar pesananmu segera diproses.</p>
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
                    <a class="cart-link" href="keranjang.php"><span aria-hidden="true">▱</span><span>Keranjang</span></a>
                </div>
            </header>

            <main class="content">
                <div class="breadcrumb"><a href="index.php">Beranda</a><span>/</span><span>Konfirmasi pembelian</span></div>
                <section class="section-heading cart-page-heading">
                    <div>
                        <p class="eyebrow">TINGGAL SATU LANGKAH</p>
                        <h1>Konfirmasi pembelian</h1>
                    </div>
                </section>

                <?php if ($flash !== null): ?>
                    <div class="notice notice-error" role="alert"><?= e($flash) ?></div>
                <?php endif; ?>

                <div class="checkout-layout">
                    <form class="checkout-form" action="checkout.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                        <h2>Informasi penerima</h2>
                        <label for="customer_name">Nama lengkap</label>
                        <input id="customer_name" name="customer_name" type="text" maxlength="120" autocomplete="name" value="<?= e($customerName) ?>" required>

                        <label for="phone">Nomor telepon</label>
                        <input id="phone" name="phone" type="tel" maxlength="30" autocomplete="tel" value="<?= e($phone) ?>" required>

                        <label for="email">Email <span>(opsional)</span></label>
                        <input id="email" name="email" type="email" maxlength="190" autocomplete="email" value="<?= e($email) ?>">

                        <label for="shipping_address">Alamat lengkap</label>
                        <textarea id="shipping_address" name="shipping_address" rows="4" autocomplete="street-address" required><?= e($shippingAddress) ?></textarea>

                        <button class="primary-button checkout-submit" type="submit">Buat pesanan dan invoice <span aria-hidden="true">→</span></button>
                    </form>

                    <aside class="cart-summary">
                        <p class="eyebrow"><?= $checkoutType === 'cart' ? 'BARANG TERPILIH DARI KERANJANG' : 'PESANAN LANGSUNG' ?></p>
                        <h2>Ringkasan produk</h2>
                        <div class="checkout-products">
                            <?php foreach ($checkoutItems as $item): ?>
                                <div class="checkout-product">
                                    <span class="checkout-product-visual">
                                        <?php if (!empty($item['image_url'])): ?>
                                            <img src="<?= e($item['image_url']) ?>" alt="">
                                        <?php else: ?>
                                            <?= e($item['visual']) ?>
                                        <?php endif; ?>
                                    </span>
                                    <span><strong><?= e($item['name']) ?></strong><small><?= (int) $item['quantity'] ?> × <?= rupiah($item['price']) ?></small></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="summary-total"><span>Total pesanan</span><strong><?= rupiah($subtotal) ?></strong></div>
                        <p class="summary-note">Pesanan akan tercatat dengan status menunggu pembayaran. Pembayaran online belum tersedia.</p>
                    </aside>
                </div>

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
