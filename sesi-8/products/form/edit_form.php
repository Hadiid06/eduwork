
<?php

require '../../koneksi_db.php';

// Ambil ID produk dari URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Jika ID tidak valid, kembali ke halaman utama
if (!$id) {
    header('Location: ../../index.php');
    exit;
}

// Ambil data produk berdasarkan ID
$stmt = $pdo->prepare(
    'SELECT * FROM products WHERE id = ?'
);

$stmt->execute([$id]);
$product = $stmt->fetch();

// Jika produk tidak ditemukan, kembali ke halaman utama
if (!$product) {
    header('Location: ../../index.php');
    exit;
}

// Fungsi untuk mengamankan output HTML
function e($v)
{
    return htmlspecialchars(
        (string) $v,
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

    <title>Edit Produk</title>

    <link rel="stylesheet" href="../../styles.css">
</head>

<body>

    <!-- Header dan navigasi -->
    <header class="topbar">
        <div class="wrap">

            <a class="brand" href="../../index.php">
                stok<span>ku.</span>
            </a>

            <nav class="navlinks">
                <a href="../../index.php">
                    Kembali ke daftar
                </a>
            </nav>

        </div>
    </header>

    <main class="wrap">

        <!-- Form edit produk -->
        <section class="panel form-panel">

            <div class="eyebrow">
                Perbarui informasi
            </div>

            <h1>Edit produk</h1>

            <p class="note">
                Ubah detail produk lalu simpan.
            </p>

            <form action="../crud_process/update.php" method="post">

                <!-- ID produk yang akan diperbarui -->
                <input
                    type="hidden"
                    name="id"
                    value="<?= (int) $product['id'] ?>"
                >

                <!-- Nama produk -->
                <div class="field">
                    <label for="name">
                        Nama produk
                    </label>

                    <input
                        id="name"
                        name="name"
                        required
                        maxlength="120"
                        value="<?= e($product['name']) ?>"
                    >
                </div>

                <!-- Harga produk -->
                <div class="field">
                    <label for="price">
                        Harga (Rp)
                    </label>

                    <input
                        id="price"
                        name="price"
                        type="number"
                        min="0"
                        step="0.01"
                        required
                        value="<?= e($product['price']) ?>"
                    >
                </div>

                <!-- Stok produk -->
                <div class="field">
                    <label for="stock">
                        Stok
                    </label>

                    <input
                        id="stock"
                        name="stock"
                        type="number"
                        min="0"
                        step="1"
                        required
                        value="<?= (int) $product['stock'] ?>"
                    >
                </div>

                <!-- Deskripsi produk -->
                <div class="field">
                    <label for="description">
                        Deskripsi
                    </label>

                    <textarea
                        id="description"
                        name="description"
                    ><?= e($product['description']) ?></textarea>
                </div>

                <!-- Tombol aksi -->
                <div class="form-actions">

                    <button class="btn" type="submit">
                        Simpan perubahan
                    </button>

                    <a class="btn btn-light" href="../../index.php">
                        Batal
                    </a>

                </div>

            </form>

        </section>

    </main>

</body>

</html>
