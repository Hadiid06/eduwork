<?php

session_start();
require 'koneksi_db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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

function redirectToCheckout($message)
{
    $_SESSION['cart_message'] = $message;
    header('Location: checkout.php');
    exit;
}

$errors = [];
$message = $_SESSION['cart_message'] ?? '';
unset($_SESSION['cart_message']);
$form = [
    'customer_name' => '',
    'address' => '',
    'phone' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
            $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT);

            if (!$productId || !$quantity || $quantity < 1) {
                redirectToCheckout('Produk atau jumlah yang dipilih tidak valid.');
            }

            $stmt = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
            $stmt->execute([$productId]);
            $stock = $stmt->fetchColumn();
            $currentQuantity = (int) ($_SESSION['cart'][$productId] ?? 0);

            if ($stock === false) {
                redirectToCheckout('Produk tidak ditemukan.');
            }

            if ($currentQuantity + $quantity > (int) $stock) {
                redirectToCheckout('Jumlah melebihi stok yang tersedia.');
            }

            $_SESSION['cart'][$productId] = $currentQuantity + $quantity;
            redirectToCheckout('Produk berhasil ditambahkan ke keranjang.');
        }

        if ($action === 'update') {
            $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
            $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT);

            if (!$productId || !$quantity || $quantity < 1) {
                redirectToCheckout('Produk atau jumlah yang dipilih tidak valid.');
            }

            $stmt = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
            $stmt->execute([$productId]);
            $stock = $stmt->fetchColumn();

            if ($stock === false) {
                redirectToCheckout('Produk tidak ditemukan. Hapus produk tersebut dari keranjang.');
            }

            if ($quantity > (int) $stock) {
                redirectToCheckout('Jumlah melebihi stok yang tersedia.');
            }

            $_SESSION['cart'][$productId] = $quantity;
            redirectToCheckout('Jumlah produk berhasil diperbarui.');
        }

        if ($action === 'remove') {
            $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);

            if (!$productId) {
                redirectToCheckout('Produk yang dipilih tidak valid.');
            }

            unset($_SESSION['cart'][$productId]);
            redirectToCheckout('Produk dihapus dari keranjang.');
        }

        if ($action === 'place_order') {
            $form['customer_name'] = trim(
                is_string($_POST['customer_name'] ?? null) ? $_POST['customer_name'] : ''
            );
            $form['address'] = trim(
                is_string($_POST['address'] ?? null) ? $_POST['address'] : ''
            );
            $form['phone'] = trim(
                is_string($_POST['phone'] ?? null) ? $_POST['phone'] : ''
            );

            if ($form['customer_name'] === '' || strlen($form['customer_name']) > 120) {
                $errors[] = 'Nama wajib diisi dan maksimal 120 karakter.';
            }

            if ($form['address'] === '' || strlen($form['address']) > 500) {
                $errors[] = 'Alamat wajib diisi dan maksimal 500 karakter.';
            }

            if ($form['phone'] === '' || strlen($form['phone']) > 30) {
                $errors[] = 'Nomor telepon wajib diisi dan maksimal 30 karakter.';
            }

            if (empty($_SESSION['cart'])) {
                $errors[] = 'Keranjang masih kosong.';
            }

            if (!$errors) {
                try {
                    $pdo->beginTransaction();
                    $cartItems = [];
                    $totalCents = 0;
                    $maxOrderCents = 999999999999;
                    $productStmt = $pdo->prepare(
                        'SELECT id, name, price, stock FROM products WHERE id = ? FOR UPDATE'
                    );

                    foreach ($_SESSION['cart'] as $productId => $quantity) {
                        $productId = filter_var($productId, FILTER_VALIDATE_INT);
                        $quantity = filter_var($quantity, FILTER_VALIDATE_INT);

                        if (!$productId || !$quantity || $quantity < 1) {
                            throw new RuntimeException('Isi keranjang tidak valid.');
                        }

                        $productStmt->execute([$productId]);
                        $product = $productStmt->fetch();

                        if (!$product) {
                            throw new RuntimeException('Salah satu produk di keranjang sudah tidak tersedia.');
                        }

                        if ($quantity > (int) $product['stock']) {
                            throw new RuntimeException(
                                'Stok ' . $product['name'] . ' tidak mencukupi. Perbarui jumlah di keranjang.'
                            );
                        }

                        $priceParts = explode('.', (string) $product['price']);
                        $unitCents = ((int) $priceParts[0] * 100)
                            + (int) str_pad($priceParts[1] ?? '00', 2, '0');

                        if ($unitCents > intdiv($maxOrderCents, $quantity)) {
                            throw new RuntimeException('Total pesanan melebihi batas nilai yang didukung.');
                        }

                        $lineCents = $unitCents * $quantity;

                        if ($lineCents > $maxOrderCents - $totalCents) {
                            throw new RuntimeException('Total pesanan melebihi batas nilai yang didukung.');
                        }

                        $totalCents += $lineCents;
                        $cartItems[] = [
                            'product_id' => (int) $product['id'],
                            'product_name' => $product['name'],
                            'quantity' => $quantity,
                            'total_cents' => $lineCents,
                        ];
                    }

                    $totalPrice = intdiv($totalCents, 100)
                        . '.' . str_pad((string) ($totalCents % 100), 2, '0', STR_PAD_LEFT);

                    $orderStmt = $pdo->prepare(
                        'INSERT INTO orders (user_name, address, phone, total_price)
                         VALUES (?, ?, ?, ?)'
                    );
                    $orderStmt->execute([
                        $form['customer_name'],
                        $form['address'],
                        $form['phone'],
                        $totalPrice,
                    ]);
                    $orderId = (int) $pdo->lastInsertId();

                    $itemStmt = $pdo->prepare(
                        'INSERT INTO order_items (order_id, product_id, product_name, quantity, total_price)
                         VALUES (?, ?, ?, ?, ?)'
                    );
                    $stockStmt = $pdo->prepare(
                        'UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?'
                    );

                    foreach ($cartItems as $item) {
                        $linePrice = intdiv($item['total_cents'], 100)
                            . '.' . str_pad((string) ($item['total_cents'] % 100), 2, '0', STR_PAD_LEFT);
                        $itemStmt->execute([
                            $orderId,
                            $item['product_id'],
                            $item['product_name'],
                            $item['quantity'],
                            $linePrice,
                        ]);
                        $stockStmt->execute([
                            $item['quantity'],
                            $item['product_id'],
                            $item['quantity'],
                        ]);

                        if ($stockStmt->rowCount() !== 1) {
                            throw new RuntimeException('Stok berubah saat pesanan diproses. Silakan coba lagi.');
                        }
                    }

                    $pdo->commit();
                    $_SESSION['cart'] = [];
                    $_SESSION['order_ids'] = $_SESSION['order_ids'] ?? [];
                    $_SESSION['order_ids'][] = $orderId;
                    header('Location: invoice.php?id=' . $orderId);
                    exit;
                } catch (RuntimeException $exception) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }

                    $errors[] = $exception->getMessage();
                } catch (PDOException $exception) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }

                    error_log($exception->getMessage());
                    $errors[] = 'Pesanan gagal disimpan karena masalah database. Silakan coba lagi.';
                }
            }
        } else {
            $errors[] = 'Aksi checkout tidak dikenal.';
        }
    }
}

$cartItems = [];
$cartTotal = 0;
$cartCanBeCheckedOut = true;
$productStmt = $pdo->prepare('SELECT id, name, price, stock FROM products WHERE id = ?');

foreach ($_SESSION['cart'] ?? [] as $productId => $quantity) {
    $productId = filter_var($productId, FILTER_VALIDATE_INT);
    $quantity = filter_var($quantity, FILTER_VALIDATE_INT);

    if (!$productId || !$quantity || $quantity < 1) {
        $cartCanBeCheckedOut = false;
        continue;
    }

    $productStmt->execute([$productId]);
    $product = $productStmt->fetch();
    $available = $product && $quantity <= (int) $product['stock'];

    if (!$available) {
        $cartCanBeCheckedOut = false;
    }

    $lineTotal = $product ? (float) $product['price'] * $quantity : 0;
    $cartTotal += $lineTotal;
    $cartItems[] = [
        'product_id' => $productId,
        'quantity' => $quantity,
        'product' => $product,
        'available' => $available,
        'line_total' => $lineTotal,
    ];
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
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
        <section class="hero">
            <div class="eyebrow">Pesanan</div>
            <h1>Checkout</h1>
            <p>Periksa produk dan isi informasi penerima sebelum membuat pesanan.</p>
        </section>

        <?php if ($message !== ''): ?>
            <div class="alert alert-success" role="status"><?= e($message) ?></div>
        <?php endif; ?>

        <?php foreach ($errors as $error): ?>
            <div class="alert" role="alert"><?= e($error) ?></div>
        <?php endforeach; ?>

        <div class="checkout-layout">
            <section class="panel">
                <div class="panel-head">
                    <h2>Review order items</h2>
                    <a class="btn btn-light" href="catalog.php">Tambah produk</a>
                </div>

                <?php if ($cartItems): ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th>Total</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $item): ?>
                                    <tr>
                                        <td>
                                            <strong><?= e($item['product']['name'] ?? 'Produk tidak tersedia') ?></strong>
                                            <?php if (!$item['available']): ?>
                                                <div class="note">Produk tidak tersedia atau stok tidak mencukupi.</div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="price">
                                            <?= $item['product'] ? rupiah($item['product']['price']) : '-' ?>
                                        </td>
                                        <td>
                                            <form action="checkout.php" method="post" class="actions">
                                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="product_id" value="<?= (int) $item['product_id'] ?>">
                                                <label class="sr-only" for="quantity-<?= (int) $item['product_id'] ?>">Jumlah</label>
                                                <input
                                                    class="quantity-input"
                                                    id="quantity-<?= (int) $item['product_id'] ?>"
                                                    name="quantity"
                                                    type="number"
                                                    min="1"
                                                    max="<?= (int) ($item['product']['stock'] ?? 1) ?>"
                                                    value="<?= (int) $item['quantity'] ?>"
                                                    required
                                                >
                                                <button class="btn btn-light" type="submit">Perbarui</button>
                                            </form>
                                        </td>
                                        <td class="price"><?= rupiah($item['line_total']) ?></td>
                                        <td>
                                            <form action="checkout.php" method="post">
                                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                                <input type="hidden" name="action" value="remove">
                                                <input type="hidden" name="product_id" value="<?= (int) $item['product_id'] ?>">
                                                <button class="btn btn-danger" type="submit">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="summary-total">
                        <span>Total</span>
                        <span><?= rupiah($cartTotal) ?></span>
                    </div>
                <?php else: ?>
                    <div class="empty">
                        Keranjang masih kosong. <a href="catalog.php">Pilih produk di etalase</a>.
                    </div>
                <?php endif; ?>
            </section>

            <section class="panel">
                <h2>Informasi pelanggan</h2>
                <form action="checkout.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action" value="place_order">

                    <div class="field">
                        <label for="customer_name">Nama</label>
                        <input
                            id="customer_name"
                            name="customer_name"
                            maxlength="120"
                            autocomplete="name"
                            value="<?= e($form['customer_name']) ?>"
                            required
                        >
                    </div>

                    <div class="field">
                        <label for="address">Alamat</label>
                        <textarea
                            id="address"
                            name="address"
                            maxlength="500"
                            autocomplete="street-address"
                            required
                        ><?= e($form['address']) ?></textarea>
                    </div>

                    <div class="field">
                        <label for="phone">Nomor telepon</label>
                        <input
                            id="phone"
                            name="phone"
                            type="tel"
                            maxlength="30"
                            autocomplete="tel"
                            value="<?= e($form['phone']) ?>"
                            required
                        >
                    </div>

                    <button
                        class="btn"
                        type="submit"
                        <?= !$cartItems || !$cartCanBeCheckedOut ? 'disabled' : '' ?>
                    >
                        Buat pesanan
                    </button>
                </form>
            </section>
        </div>
    </main>
</body>

</html>
