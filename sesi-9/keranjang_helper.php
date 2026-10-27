<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function nusamartCartId(PDO $pdo): int
{
    $reservationColumn = $pdo->query(
        "SHOW COLUMNS FROM cart_items LIKE 'stock_reserved'"
    )->fetch();
    if (!$reservationColumn) {
        $pdo->exec(
            'ALTER TABLE cart_items
             ADD COLUMN stock_reserved TINYINT(1) NOT NULL DEFAULT 0
             AFTER quantity'
        );
    }

    if (!isset($_SESSION['nusamart_cart_key'])) {
        $_SESSION['nusamart_cart_key'] = bin2hex(random_bytes(32));
    }

    $statement = $pdo->prepare(
        'INSERT IGNORE INTO carts (session_key) VALUES (:session_key)'
    );
    $statement->execute(['session_key' => $_SESSION['nusamart_cart_key']]);

    $statement = $pdo->prepare(
        'SELECT id FROM carts WHERE session_key = :session_key LIMIT 1'
    );
    $statement->execute(['session_key' => $_SESSION['nusamart_cart_key']]);
    $cartId = (int) $statement->fetchColumn();
    if ($cartId < 1) {
        throw new RuntimeException('Keranjang tidak dapat disiapkan.');
    }

    return $cartId;
}

function nusamartReserveCartItems(PDO $pdo, int $cartId): int
{
    $pdo->beginTransaction();

    try {
        $itemsQuery = $pdo->prepare(
            'SELECT ci.id, ci.product_id, ci.quantity, p.stock
             FROM cart_items ci
             INNER JOIN products p ON p.id = ci.product_id
             WHERE ci.cart_id = :cart_id AND ci.stock_reserved = 0
             ORDER BY ci.product_id
             FOR UPDATE'
        );
        $itemsQuery->execute(['cart_id' => $cartId]);
        $items = $itemsQuery->fetchAll();
        $reserveProduct = $pdo->prepare(
            'UPDATE products SET stock = stock - :quantity WHERE id = :id'
        );
        $markReserved = $pdo->prepare(
            'UPDATE cart_items SET stock_reserved = 1 WHERE id = :id'
        );
        $reservedCount = 0;

        foreach ($items as $item) {
            if ((int) $item['stock'] < (int) $item['quantity']) {
                continue;
            }

            $reserveProduct->execute([
                'quantity' => $item['quantity'],
                'id' => $item['product_id'],
            ]);
            $markReserved->execute(['id' => $item['id']]);
            $reservedCount++;
        }

        $pdo->commit();

        return $reservedCount;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

function nusamartCartCount(PDO $pdo, int $cartId): int
{
    $statement = $pdo->prepare(
        'SELECT COALESCE(SUM(quantity), 0)
         FROM cart_items
         WHERE cart_id = :cart_id'
    );
    $statement->execute(['cart_id' => $cartId]);

    return (int) $statement->fetchColumn();
}

function nusamartStylesheetUrl(): string
{
    $scriptDirectory = str_replace(
        '\\',
        '/',
        dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/'))
    );
    $basePath = rtrim($scriptDirectory, '/');
    if ($basePath === '.' || $basePath === DIRECTORY_SEPARATOR) {
        $basePath = '';
    }

    $stylesheetPath = __DIR__ . DIRECTORY_SEPARATOR . 'styles.css';
    $version = is_file($stylesheetPath) ? (string) filemtime($stylesheetPath) : '';

    return $basePath . '/styles.css?v=' . rawurlencode($version);
}

function nusamartSetFlash(string $type, string $message): void
{
    $_SESSION['nusamart_flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function nusamartGetFlash(): ?array
{
    $flash = $_SESSION['nusamart_flash'] ?? null;
    unset($_SESSION['nusamart_flash']);

    return is_array($flash) ? $flash : null;
}

function nusamartCsrfToken(): string
{
    if (!isset($_SESSION['nusamart_csrf_token'])) {
        $_SESSION['nusamart_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['nusamart_csrf_token'];
}

function nusamartValidCsrfToken(mixed $token): bool
{
    return is_string($token)
        && isset($_SESSION['nusamart_csrf_token'])
        && hash_equals($_SESSION['nusamart_csrf_token'], $token);
}
