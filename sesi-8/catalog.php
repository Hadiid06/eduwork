
<?php

require 'koneksi_db.php';

// Ambil kata kunci pencarian
$search = trim($_GET['search'] ?? '');

// Ambil data produk
if ($search !== '') {
    $stmt = $pdo->prepare(
        'SELECT * FROM products WHERE name LIKE ? ORDER BY name'
    );

    $stmt->execute(['%' . $search . '%']);
    $products = $stmt->fetchAll();
} else {
    $products = $pdo
        ->query('SELECT * FROM products ORDER BY name')
        ->fetchAll();
}

// Fungsi untuk mengamankan output HTML
function e($v)
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Etalase Produk</title>

    <link rel="stylesheet" href="styles.css">

    <style>
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }

        .product-card {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 18px;
            background: #fff;
        }

        .product-card h3 {
            margin: 8px 0;
        }

        .product-card p {
            color: var(--muted);
            line-height: 1.5;
            min-height: 40px;
        }

        .product-card .price {
            color: var(--blue);
            font-size: 18px;
        }
    </style>
</head>

<body>

    <header class="topbar">
        <div class="wrap">
            <a class="brand" href="index.php">
                stok<span>ku.</span>
            </a>

            <nav class="navlinks">
                <a href="index.php">Kelola Produk</a>
                <a href="catalog.php">Etalase</a>
            </nav>
        </div>
    </header>

    <main class="wrap">

        <!-- Bagian judul halaman -->
        <section class="hero">
            <div class="eyebrow">Katalog</div>

            <h1>Etalase produk</h1>

            <p>Lihat semua produk dan informasi stoknya.</p>
        </section>

        <!-- Form pencarian produk -->
        <section class="panel">
            <form class="search" method="get">
                <input
                    name="search"
                    type="search"
                    placeholder="Cari produk..."
                    value="<?= e($search) ?>"
                >

                <button class="btn" type="submit">
                    Cari
                </button>
            </form>
        </section>

        <!-- Daftar produk -->
        <section class="cards">

            <?php if ($products): ?>

                <?php foreach ($products as $p): ?>

                    <article class="product-card">

                        <span class="eyebrow">
                            Produk #<?= (int) $p['id'] ?>
                        </span>

                        <h3>
                            <?= e($p['name']) ?>
                        </h3>

                        <div class="price">
                            Rp <?= number_format((float) $p['price'], 0, ',', '.') ?>
                        </div>

                        <p>
                            <?= e($p['description'] ?: 'Tidak ada deskripsi.') ?>
                        </p>

                        <span class="stock">
                            Stok <?= (int) $p['stock'] ?> unit
                        </span>

                    </article>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="panel">
                    Produk belum tersedia.
                </div>

            <?php endif; ?>

        </section>

    </main>

</body>

</html>
