
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Produk</title>

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

        <!-- Form tambah produk -->
        <section class="panel form-panel">

            <div class="eyebrow">
                Produk baru
            </div>

            <h1>Tambah produk</h1>

            <p class="note">
                Isi informasi produk di bawah ini.
            </p>

            <form action="../crud_process/create.php" method="post">

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
                        placeholder="Contoh: Mouse Wireless"
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
                        placeholder="75000"
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
                        placeholder="10"
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
                        placeholder="Deskripsi singkat produk"
                    ></textarea>
                </div>

                <!-- Tombol aksi -->
                <div class="form-actions">

                    <button class="btn" type="submit">
                        Simpan produk
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
