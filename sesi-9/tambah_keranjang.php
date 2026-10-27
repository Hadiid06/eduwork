<?php

require 'koneksi_db.php';
require 'keranjang_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        nusamartSetFlash('error', 'Pilih produk terlebih dahulu untuk menambahkannya ke keranjang.');
        header('Location: index.php');
        exit;
    }

    http_response_code(405);
    header('Allow: POST');
    exit('Metode permintaan tidak valid.');
}

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
$action = is_string($_POST['action'] ?? null) ? $_POST['action'] : 'add_to_cart';

if (!$productId || $productId < 1) {
    nusamartSetFlash('error', 'Produk yang dipilih tidak valid.');
    header('Location: index.php');
    exit;
}

if (!in_array($action, ['add_to_cart', 'buy_now'], true)) {
    nusamartSetFlash('error', 'Aksi produk tidak valid.');
    header('Location: detail_produk.php?id=' . $productId);
    exit;
}

if ($quantity === false || $quantity === null || $quantity < 1) {
    nusamartSetFlash('error', 'Masukkan jumlah produk yang valid.');
    header('Location: detail_produk.php?id=' . $productId);
    exit;
}

if (!nusamartValidCsrfToken($_POST['csrf_token'] ?? null)) {
    nusamartSetFlash('error', 'Permintaan tidak dapat diverifikasi. Silakan coba lagi.');
    header('Location: detail_produk.php?id=' . $productId);
    exit;
}

$cartId = nusamartCartId($pdo);

try {
    $pdo->beginTransaction();

    $productQuery = $pdo->prepare(
        'SELECT id, name, price, stock
         FROM products
         WHERE id = :id
         LIMIT 1
         FOR UPDATE'
    );
    $productQuery->execute(['id' => $productId]);
    $product = $productQuery->fetch();

    if (!$product) {
        $pdo->rollBack();
        http_response_code(404);
        nusamartSetFlash('error', 'Produk tidak ditemukan.');
        header('Location: index.php');
        exit;
    }

    if ($action === 'buy_now') {
        if ($quantity > (int) $product['stock']) {
            $pdo->rollBack();
            nusamartSetFlash(
                'error',
                'Stok tidak mencukupi. Maksimal ' . (int) $product['stock'] . ' unit tersedia.'
            );
            header('Location: detail_produk.php?id=' . $productId);
            exit;
        }

        $pdo->commit();
        $_SESSION['nusamart_pending_checkout'] = [
            'type' => 'direct',
            'product_id' => $productId,
            'quantity' => $quantity,
        ];
        header('Location: checkout.php');
        exit;
    }

    $itemQuery = $pdo->prepare(
        'SELECT id, quantity, stock_reserved
         FROM cart_items
         WHERE cart_id = :cart_id AND product_id = :product_id
         LIMIT 1
         FOR UPDATE'
    );
    $itemQuery->execute([
        'cart_id' => $cartId,
        'product_id' => $productId,
    ]);
    $existingItem = $itemQuery->fetch();
    $wasReserved = $existingItem && (int) $existingItem['stock_reserved'] === 1;
    $newQuantity = (int) ($existingItem['quantity'] ?? 0) + $quantity;
    $stockToReserve = $wasReserved ? $quantity : $newQuantity;

    if ((int) $product['stock'] < $stockToReserve) {
        $pdo->rollBack();
        nusamartSetFlash(
            'error',
            'Stok tidak mencukupi. Maksimal ' . (int) $product['stock'] . ' unit tersedia.'
        );
        header('Location: detail_produk.php?id=' . $productId);
        exit;
    }

    $reserveStock = $pdo->prepare(
        'UPDATE products SET stock = stock - :quantity WHERE id = :id'
    );
    $reserveStock->execute([
        'quantity' => $stockToReserve,
        'id' => $productId,
    ]);

    if ($existingItem) {
        $saveItem = $pdo->prepare(
            'UPDATE cart_items
             SET quantity = :quantity, stock_reserved = 1
             WHERE id = :id'
        );
        $saveItem->execute([
            'quantity' => $newQuantity,
            'id' => $existingItem['id'],
        ]);
    } else {
        $saveItem = $pdo->prepare(
            'INSERT INTO cart_items (cart_id, product_id, quantity, stock_reserved)
             VALUES (:cart_id, :product_id, :quantity, 1)'
        );
        $saveItem->execute([
            'cart_id' => $cartId,
            'product_id' => $productId,
            'quantity' => $quantity,
        ]);
    }

    $pdo->commit();
    nusamartSetFlash('success', $product['name'] . ' berhasil ditambahkan ke keranjang.');
    header('Location: keranjang.php');
    exit;
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    throw $exception;
}
