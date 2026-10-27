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

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$authorizedOrders = $_SESSION['nusamart_invoice_ids'] ?? [];
if (
    !$orderId
    || $orderId < 1
    || !is_array($authorizedOrders)
    || !in_array($orderId, array_map('intval', $authorizedOrders), true)
) {
    http_response_code(404);
    exit('Invoice tidak ditemukan atau tidak dapat diakses dari sesi ini.');
}

$orderQuery = $pdo->prepare(
    'SELECT id, customer_name, email, phone, shipping_address,
            subtotal, shipping_cost, total_price, status, payment_status, created_at
     FROM orders
     WHERE id = :id
     LIMIT 1'
);
$orderQuery->execute(['id' => $orderId]);
$order = $orderQuery->fetch();

if (!$order) {
    http_response_code(404);
    exit('Invoice tidak ditemukan.');
}

$itemsQuery = $pdo->prepare(
    'SELECT product_name, quantity, unit_price, total_price
     FROM order_items
     WHERE order_id = :order_id
     ORDER BY id'
);
$itemsQuery->execute(['order_id' => $orderId]);
$items = $itemsQuery->fetchAll();

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7f8fc">
    <title>Invoice #<?= (int) $order['id'] ?> | NusaMart</title>
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
                <a class="side-link" href="keranjang.php"><span class="nav-icon">▱</span> Keranjang</a>
            </nav>
            <div class="side-promo">
                <span class="side-promo-icon">✦</span>
                <strong>Terima kasih</strong>
                <p>Pesananmu sudah kami catat.</p>
                <a href="index.php#products">Belanja lagi <span aria-hidden="true">→</span></a>
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
                <div class="breadcrumb"><a href="index.php">Beranda</a><span>/</span><span>Invoice</span></div>
                <div class="invoice-actions">
                    <p class="eyebrow">PESANAN BERHASIL DICATAT</p>
                    <button class="secondary-button print-button" type="button" onclick="window.print()">Cetak invoice</button>
                </div>
                <section class="invoice-card">
                    <header class="invoice-header">
                        <div>
                            <a class="footer-brand invoice-brand" href="index.php">Nusa<span>Mart</span></a>
                            <p>Rincian pesanan belanjamu</p>
                        </div>
                        <div class="invoice-number">
                            <span>INVOICE</span>
                            <strong>#<?= (int) $order['id'] ?></strong>
                        </div>
                    </header>
                    <div class="invoice-meta">
                        <div><span>Tanggal pesanan</span><strong><?= e(date('d M Y, H:i', strtotime($order['created_at']))) ?></strong></div>
                        <div><span>Status pesanan</span><strong class="status-pill"><?= e(ucfirst($order['status'])) ?></strong></div>
                        <div><span>Status pembayaran</span><strong><?= e(ucfirst($order['payment_status'])) ?></strong></div>
                    </div>
                    <div class="invoice-customer">
                        <div>
                            <span>DITAGIHKAN KEPADA</span>
                            <strong><?= e($order['customer_name']) ?></strong>
                            <p><?= e($order['phone']) ?><?= $order['email'] ? ' · ' . e($order['email']) : '' ?></p>
                        </div>
                        <div>
                            <span>ALAMAT PENGIRIMAN</span>
                            <p><?= nl2br(e($order['shipping_address'])) ?></p>
                        </div>
                    </div>
                    <div class="invoice-table-wrap">
                        <table class="invoice-table">
                            <thead>
                                <tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Total</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?= e($item['product_name']) ?></td>
                                        <td><?= rupiah($item['unit_price']) ?></td>
                                        <td><?= (int) $item['quantity'] ?></td>
                                        <td><?= rupiah($item['total_price']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="invoice-totals">
                        <div><span>Subtotal</span><strong><?= rupiah($order['subtotal']) ?></strong></div>
                        <div><span>Pengiriman</span><strong><?= rupiah($order['shipping_cost']) ?></strong></div>
                        <div class="invoice-grand-total"><span>Total pesanan</span><strong><?= rupiah($order['total_price']) ?></strong></div>
                    </div>
                    <p class="invoice-note">Invoice ini merupakan bukti pesanan. Pembayaran online belum tersedia; status pesanan saat ini menunggu pembayaran.</p>
                </section>
                <div class="invoice-footer-actions">
                    <a class="primary-button" href="index.php#products">Lanjut belanja <span aria-hidden="true">→</span></a>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
