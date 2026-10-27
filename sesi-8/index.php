
<?php

require 'koneksi_db.php';

// Ambil kata kunci pencarian
$search = trim($_GET['search'] ?? '');

// Ambil data produk berdasarkan pencarian
if ($search !== '') {
    $stmt = $pdo->prepare(
        'SELECT * FROM products WHERE name LIKE ? ORDER BY id DESC'
    );

    $stmt->execute(['%' . $search . '%']);
    $products = $stmt->fetchAll();
} else {
    $products = $pdo
        ->query('SELECT * FROM products ORDER BY id DESC')
        ->fetchAll();
}

// Hitung jumlah produk
$totalProducts = (int) $pdo
    ->query('SELECT COUNT(*) FROM products')
    ->fetchColumn();

// Hitung total stok
$totalStock = (int) $pdo
    ->query('SELECT COALESCE(SUM(stock), 0) FROM products')
    ->fetchColumn();

// Fungsi untuk mengamankan output HTML
function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kelola Produk</title>

    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <!-- Header dan navigasi -->
    <header class="topbar">
        <div class="wrap">

            <a class="brand" href="index.php">
                stok<span>ku.</span>
            </a>

            <nav class="navlinks">
                <a href="index.php">Kelola Produk</a>
                <a href="catalog.php">Etalase</a>
                <a href="checkout.php">Checkout</a>
            </nav>

        </div>
    </header>

    <main class="wrap">

        <!-- Bagian pembuka -->
        <section class="hero">
            <div class="eyebrow">Ruang kerja produk</div>

            <h1>Kelola produk dengan mudah.</h1>

            <p>
                Tambah, perbarui, dan pantau stok produk
                dalam satu halaman sederhana.
            </p>
        </section>

        <!-- Ringkasan inventaris -->
        <section class="panel">

            <div class="panel-head">
                <div>
                    <h2>Ringkasan</h2>
                    <p class="note">
                        Data singkat dari inventaris kamu.
                    </p>
                </div>
            </div>

            <div style="display: flex; gap: 14px; flex-wrap: wrap;">

                <div style="background: #f5f7ff; border-radius: 10px; padding: 16px 22px; min-width: 170px;">
                    <div class="note">Jenis produk</div>

                    <strong style="font-size: 25px;">
                        <?= $totalProducts ?>
                    </strong>
                </div>

                <div style="background: #effaf5; border-radius: 10px; padding: 16px 22px; min-width: 170px;">
                    <div class="note">Total stok</div>

                    <strong style="font-size: 25px;">
                        <?= $totalStock ?>
                    </strong>

                    <span class="note">unit</span>
                </div>

            </div>

        </section>

        <!-- Daftar produk -->
        <section class="panel">

            <div class="panel-head">
                <h2>Daftar produk</h2>

                <a class="btn" href="products/form/create_form.php">
                    + Tambah produk
                </a>
            </div>

            <!-- Form pencarian -->
            <form class="search" method="get">

                <input
                    type="search"
                    name="search"
                    placeholder="Cari nama produk..."
                    value="<?= e($search) ?>"
                >

                <button class="btn btn-light" type="submit">
                    Cari
                </button>

                <?php if ($search !== ''): ?>
                    <a class="btn btn-light" href="index.php">
                        Reset
                    </a>
                <?php endif; ?>

            </form>

            <!-- Tabel produk -->
            <div class="table-wrap" style="margin-top: 16px;">

                <table>

                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if ($products): ?>

                            <?php foreach ($products as $p): ?>

                                <tr>

                                    <td>
                                        <strong>
                                            <?= e($p['name']) ?>
                                        </strong>

                                        <div class="note">
                                            ID #<?= (int) $p['id'] ?>
                                        </div>
                                    </td>

                                    <td class="price">
                                        Rp <?= number_format(
                                            (float) $p['price'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>

                                    <td>
                                        <span class="stock">
                                            <?= (int) $p['stock'] ?> unit
                                        </span>
                                    </td>

                                    <td>
                                        <?= e($p['description'] ?: '-') ?>
                                    </td>

                                    <td>
                                        <div class="actions">

                                            <a
                                                class="btn btn-light"
                                                href="products/form/edit_form.php?id=<?= (int) $p['id'] ?>"
                                            >
                                                Edit
                                            </a>

                                            <form
                                                action="products/crud_process/delete.php"
                                                method="post"
                                                onsubmit="return confirm('Hapus produk ini?')"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= (int) $p['id'] ?>"
                                                >

                                                <button
                                                    class="btn btn-danger"
                                                    type="submit"
                                                >
                                                    Hapus
                                                </button>
                                            </form>

                                        </div>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="5" class="empty">
                                    Belum ada produk. Klik “Tambah produk”
                                    untuk mulai.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

        <!-- Footer -->
        <p class="note" style="text-align: center; margin: 28px 0;">
            Stokku · latihan CRUD PHP dan MySQL
        </p>

    </main>

</body>

</html>
