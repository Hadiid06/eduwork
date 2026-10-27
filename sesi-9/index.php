<?php

// Hubungkan dashboard ke database sesi-9.
require 'koneksi_db.php';
require 'keranjang_helper.php';

$cartId = nusamartCartId($pdo);
$cartCount = nusamartCartCount($pdo, $cartId);
$flash = nusamartGetFlash();

// Amankan teks dari database atau URL sebelum ditampilkan sebagai HTML.
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Tampilkan harga dalam format mata uang Rupiah.
function rupiah($amount)
{
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}

// Ambil kata pencarian dan kategori dari URL, misalnya ?search=tas&category=2.
$search = trim(is_string($_GET['search'] ?? null) ? $_GET['search'] : '');
$categoryId = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);

// Ambil daftar kategori beserta jumlah produk yang masih tersedia di tiap kategori.
$categories = $pdo->query(
    'SELECT c.id, c.name, c.icon, COUNT(p.id) AS product_count
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id AND p.stock > 0
     GROUP BY c.id, c.name, c.icon
     ORDER BY c.id'
)->fetchAll();

// Ambil ringkasan jumlah produk dan kategori untuk kartu statistik dashboard.
$stats = $pdo->query(
    'SELECT COUNT(*) AS product_count,
            COUNT(DISTINCT category_id) AS category_count
     FROM products
     WHERE stock > 0'
)->fetch();

// Susun filter produk. Produk yang stoknya habis tidak ditampilkan.
$conditions = ['p.stock > 0'];
$parameters = [];

// Jika ada kata pencarian, cocokkan dengan nama, deskripsi, atau nama kategori.
if ($search !== '') {
    $conditions[] = '(p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)';
    $searchTerm = '%' . $search . '%';
    array_push($parameters, $searchTerm, $searchTerm, $searchTerm);
}

// Tambahkan filter kategori jika pengguna memilih kategori tertentu.
if ($categoryId) {
    $conditions[] = 'p.category_id = ?';
    $parameters[] = $categoryId;
}

// Ambil maksimal delapan produk, utamakan produk unggulan.
$productQuery = $pdo->prepare(
    'SELECT p.id, p.name, p.description, p.price, p.stock, p.visual,
            p.badge, c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE ' . implode(' AND ', $conditions) . '
     ORDER BY p.is_featured DESC, p.created_at DESC
     LIMIT 8'
);
$productQuery->execute($parameters);
$products = $productQuery->fetchAll();

// Cari nama kategori terpilih agar bisa ditampilkan sebagai judul produk.
$selectedCategoryName = '';
if ($categoryId) {
    foreach ($categories as $category) {
        if ((int) $category['id'] === $categoryId) {
            $selectedCategoryName = $category['name'];
            break;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7f8fc">
    <title>Beranda | NusaMart</title>
    <link rel="stylesheet" href="<?= e(nusamartStylesheetUrl()) ?>">
</head>

<body>
    <div class="app-shell">
        <!-- Sidebar desktop berisi navigasi, promosi, dan profil pengunjung. -->
        <aside class="sidebar">
            <a class="brand" href="index.php" aria-label="NusaMart beranda">
                <span class="brand-mark">n</span>
                <span>Nusa<span class="brand-accent">Mart</span></span>
            </a>

            <div class="side-label">MENU</div>
            <nav class="side-nav" aria-label="Navigasi utama">
                <a class="side-link active" href="index.php">
                    <span class="nav-icon">⌂</span> Beranda
                </a>
                <a class="side-link" href="#products">
                    <span class="nav-icon">▦</span> Jelajahi produk
                </a>
                <a class="side-link" href="#categories">
                    <span class="nav-icon">◈</span> Kategori
                </a>
                <a class="side-link" href="#benefits">
                    <span class="nav-icon">♡</span> Keuntungan
                </a>
            </nav>

            <div class="side-promo">
                <span class="side-promo-icon">✦</span>
                <strong>Belanja lebih seru</strong>
                <p>Temukan produk pilihan untuk menemani harimu.</p>
                <a href="#products">Mulai jelajahi <span aria-hidden="true">→</span></a>
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
            <!-- Header berisi pencarian dan pintasan navigasi pengguna. -->
            <header class="topbar">
                <div class="mobile-brand">
                    <span class="brand-mark">n</span>
                    <strong>Nusa<span class="brand-accent">Mart</span></strong>
                </div>

                <form class="search-box" action="index.php" method="get" role="search">
                    <span class="search-icon" aria-hidden="true">⌕</span>
                    <label class="sr-only" for="search">Cari produk</label>
                    <input
                        id="search"
                        name="search"
                        type="search"
                        placeholder="Cari produk, kategori, atau inspirasi..."
                        value="<?= e($search) ?>"
                    >
                    <kbd>Enter</kbd>
                </form>

                <div class="top-actions">
                    <span class="top-note">Belanja nyaman, setiap hari</span>
                    <a class="icon-button" href="#benefits" aria-label="Informasi keuntungan">♡</a>
                    <a class="cart-link" href="keranjang.php">
                        <span aria-hidden="true">▱</span>
                        <span>Keranjang</span>
                        <span class="cart-count"><?= $cartCount ?></span>
                    </a>
                </div>
            </header>

            <main class="content">
                <?php if ($flash): ?>
                    <div class="notice notice-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
                <?php endif; ?>

                <!-- Sapaan dan pengantar dashboard. -->
                <section class="welcome-row">
                    <div>
                        <p class="eyebrow">SENANG BERTEMU DENGANMU</p>
                        <h1>Temukan yang kamu suka<span class="title-dot">.</span></h1>
                        <p class="welcome-copy">Pilihan menarik untuk membuat hari-harimu jadi lebih istimewa.</p>
                    </div>  
                </section>

                <!-- Banner utama untuk mengenalkan pilihan produk toko. -->
                <section class="hero-banner" aria-label="Promo pilihan">
                    <div class="hero-content">
                        <span class="hero-label"><span></span> PILIHAN MINGGU INI</span>
                        <h2>Hal kecil,<br>bahagia besar.</h2>
                        <p>Jelajahi koleksi pilihan yang bikin keseharianmu terasa lebih berwarna.</p>
                        <a class="hero-button" href="#products">Jelajahi produk <span aria-hidden="true">→</span></a>
                        <div class="hero-pagination"><span class="current"></span><span></span><span></span></div>
                    </div>

                    <div class="hero-art" aria-hidden="true">
                        <div class="hero-sun"></div>
                        <div class="hero-ring ring-a"></div>
                        <div class="hero-ring ring-b"></div>
                        <div class="hero-blob"></div>
                        <div class="hero-product hero-product-main"><span>🎧</span></div>
                        <div class="hero-product hero-product-small"><span>👜</span></div>
                        <div class="hero-spark spark-a">✦</div>
                        <div class="hero-spark spark-b">✳</div>
                        <div class="hero-caption">your everyday<br>favorites</div>
                    </div>
                    <span class="hero-decoration decoration-a"></span>
                    <span class="hero-decoration decoration-b"></span>
                </section>

                <!-- Ringkasan statistik yang dihitung dari data produk database. -->
                <section class="stats-row" aria-label="Ringkasan toko">
                    <div class="stat-item">
                        <span class="stat-icon stat-icon-purple">▦</span>
                        <span><strong><?= number_format((int) $stats['product_count']) ?>+</strong><small>Produk tersedia</small></span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <span class="stat-icon stat-icon-orange">◈</span>
                        <span><strong><?= number_format((int) $stats['category_count']) ?></strong><small>Kategori pilihan</small></span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <span class="stat-icon stat-icon-green">♡</span>
                        <span><strong>Untukmu</strong><small>Kurasi produk menarik</small></span>
                    </div>
                </section>

                <!-- Daftar kategori; memilih kategori memfilter produk di bawah. -->
                <section class="section-block" id="categories">
                    <div class="section-heading">
                        <div>
                            <p class="eyebrow">PILIH SESUKAMU</p>
                            <h2>Jelajahi kategori</h2>
                        </div>
                        <?php if ($categoryId || $search !== ''): ?>
                            <a class="text-link" href="index.php">Hapus filter <span aria-hidden="true">×</span></a>
                        <?php else: ?>
                            <a class="text-link" href="#categories">Lihat semua <span aria-hidden="true">→</span></a>
                        <?php endif; ?>
                    </div>

                    <div class="category-grid">
                        <?php foreach ($categories as $index => $category): ?>
                            <a
                                class="category-card tone-<?= $index % 6 ?><?= $categoryId === (int) $category['id'] ? ' selected' : '' ?>"
                                href="index.php?category=<?= (int) $category['id'] ?>#products"
                            >
                                <span class="category-visual"><?= e($category['icon']) ?></span>
                                <span class="category-name"><?= e($category['name']) ?></span>
                                <span class="category-count"><?= (int) $category['product_count'] ?> produk</span>
                                <span class="category-arrow" aria-hidden="true">↗</span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Hasil pencarian atau produk pilihan dalam bentuk kartu. -->
                <section class="section-block products-section" id="products">
                    <div class="section-heading">
                        <div>
                            <p class="eyebrow">DIPILIH KHUSUS UNTUKMU</p>
                            <h2>
                                <?php if ($search !== ''): ?>
                                    Hasil pencarian
                                <?php elseif ($selectedCategoryName !== ''): ?>
                                    <?= e($selectedCategoryName) ?>
                                <?php else: ?>
                                    Produk pilihan
                                <?php endif; ?>
                            </h2>
                        </div>
                        <span class="result-count"><?= count($products) ?> produk ditampilkan</span>
                    </div>

                    <?php if ($products): ?>
                        <div class="product-grid">
                            <?php foreach ($products as $index => $product): ?>
                                <!-- Satu kartu produk: visual, kategori, harga, dan stok. -->
                                <a class="product-card" href="detail_produk.php?id=<?= (int) $product['id'] ?>">
                                    <div class="product-image tone-<?= ($index + 2) % 6 ?>">
                                        <?php if ($product['badge']): ?>
                                            <span class="product-badge"><?= e($product['badge']) ?></span>
                                        <?php endif; ?>
                                        <span class="favorite-mark" aria-hidden="true">♡</span>
                                        <span class="product-visual" aria-hidden="true"><?= e($product['visual']) ?></span>
                                        <span class="image-glow"></span>
                                    </div>
                                    <div class="product-info">
                                        <span class="product-category"><?= e($product['category_name'] ?? 'Pilihan') ?></span>
                                        <h3><?= e($product['name']) ?></h3>
                                        <p><?= e($product['description'] ?: 'Temukan pilihan menarik untuk menemani keseharianmu.') ?></p>
                                        <div class="product-card-bottom">
                                            <strong class="product-price"><?= rupiah($product['price']) ?></strong>
                                            <span class="product-stock"><?= (int) $product['stock'] ?> tersedia</span>
                                        </div>
                                    </div>
                                 </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <!-- Pesan pengganti saat filter tidak menemukan produk. -->
                        <div class="empty-state">
                            <span>⌕</span>
                            <h3>Belum ada produk yang cocok</h3>
                            <p>Coba kata kunci atau kategori lain untuk menemukan produk.</p>
                            <a class="outline-button" href="index.php">Lihat semua produk</a>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Informasi singkat tentang manfaat berbelanja di toko. -->
                <section class="benefit-strip" id="benefits">
                    <div class="benefit-item"><span>✦</span><strong>Kurasi pilihan</strong><small>Produk menarik untukmu</small></div>
                    <div class="benefit-item"><span>⌑</span><strong>Belanja praktis</strong><small>Temukan semua di satu tempat</small></div>
                    <div class="benefit-item"><span>♡</span><strong>Selalu nyaman</strong><small>Pengalaman belanja simpel</small></div>
                </section>

                <!-- Footer dashboard. -->
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
