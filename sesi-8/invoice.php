<?php

session_start();
require 'koneksi_db.php';

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function rupiah($amount)
{
    $formatted = number_format((float) $amount, 2, ',', '.');

    if (substr($formatted, -3) === ',00') {
        $formatted = substr($formatted, 0, -3);
    }

    return 'Rp ' . $formatted;
}

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$allowedOrders = $_SESSION['order_ids'] ?? [];

if (!$orderId || !in_array($orderId, $allowedOrders, true)) {
    http_response_code(404);
    $order = false;
    $items = [];
} else {
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if ($order) {
        $stmt = $pdo->prepare(
            'SELECT product_name, quantity, total_price
             FROM order_items
             WHERE order_id = ?
             ORDER BY id'
        );
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll();
    } else {
        http_response_code(404);
        $items = [];
    }
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Pesanan</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header class="topbar">
        <div class="wrap">
            <a class="brand" href="index.php">stok<span>ku.</span></a>
            <nav class="navlinks">
                <a href="index.php">Kelola Produk</a>
                <a href="catalog.php">Etalase</a>
                <a href="checkout.php">Checkout</a>
            </nav>
        </div>
    </header>

    <main class="wrap">
        <?php if (!$order): ?>
            <section class="panel form-panel">
                <h1>Invoice tidak ditemukan</h1>
                <p class="note">Invoice ini tidak tersedia pada sesi kamu.</p>
                <a class="btn" href="catalog.php">Kembali ke etalase</a>
            </section>
        <?php else: ?>
            <section class="hero">
                <div class="eyebrow">Invoice</div>
                <h1>Pesanan #<?= (int) $order['id'] ?> berhasil dibuat</h1>
                <p>Simpan halaman ini sebagai bukti detail pesanan.</p>
            </section>

            <section class="panel">
                <div class="panel-head">
                    <h2>Detail pesanan</h2>
                    <span class="stock"><?= e($order['status']) ?></span>
                </div>

                <div class="invoice-meta">
                    <div>
                        <strong>Nama pelanggan</strong>
                        <p><?= e($order['user_name']) ?></p>
                    </div>
                    <div>
                        <strong>Nomor telepon</strong>
                        <p><?= e($order['phone']) ?></p>
                    </div>
                    <div>
                        <strong>Alamat pengiriman</strong>
                        <p><?= nl2br(e($order['address'])) ?></p>
                    </div>
                    <div>
                        <strong>Tanggal pesanan</strong>
                        <p><?= e($order['created_at']) ?></p>
                    </div>
                </div>

                <div class="table-wrap" style="margin-top: 20px;">
                    <table>
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Jumlah</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= e($item['product_name']) ?></td>
                                    <td><?= (int) $item['quantity'] ?></td>
                                    <td class="price"><?= rupiah($item['total_price']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="summary-total">
                    <span>Total pesanan</span>
                    <span><?= rupiah($order['total_price']) ?></span>
                </div>
            </section>

            <p><a class="btn" href="catalog.php">Kembali ke etalase</a></p>
        <?php endif; ?>
    </main>
</body>

</html>
