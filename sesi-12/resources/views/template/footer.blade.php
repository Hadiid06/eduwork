<footer class="mt-auto" style="background: #f6f8f6;">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <a class="navbar-brand d-inline-flex align-items-center gap-2 mb-3" href="{{ url('/') }}">
                    <span class="brand-name fw-bold" style="font-size: 1.25rem; color: #17211b;">Edu<span style="color: #207a52;">Shop</span></span>
                </a>
                <p class="mb-0" style="max-width: 340px; color: #748078; line-height: 1.8;">
                    Temukan produk pilihan untuk menemani aktivitas dan kebutuhan sehari-harimu.
                </p>
            </div>

            <div class="col-6 col-lg-2 col-md-3">
                <h2 class="h6 fw-bold mb-3">Jelajahi</h2>
                <ul class="list-unstyled d-grid gap-2 mb-0">
                    <li><a class="shop-link" href="{{ url('/') }}">Beranda</a></li>
                    <li><a class="shop-link" href="{{ route('home') }}#kategori">Kategori</a></li>
                    <li><a class="shop-link" href="{{ route('home') }}#produk">Produk</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2 col-md-3">
                <h2 class="h6 fw-bold mb-3">Bantuan</h2>
                <ul class="list-unstyled d-grid gap-2 mb-0">
                    <li><a class="shop-link" href="#pusat-bantuan">Pusat Bantuan</a></li>
                    <li><a class="shop-link" href="#pengiriman">Info Pengiriman</a></li>
                    <li><a class="shop-link" href="#kontak">Hubungi Kami</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-6">
                <h2 class="h6 fw-bold mb-3">Belanja dengan nyaman</h2>
                <p class="mb-0" style="color: #748078; line-height: 1.8;">
                    Produk pilihan, pembayaran mudah, dan dukungan yang siap membantu.
                </p>
            </div>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 border-top mt-4 pt-4" style="border-color: #e2e8e3 !important; color: #748078; font-size: .9rem;">
            <span>&copy; {{ date('Y') }} EduShop. Hak cipta dilindungi.</span>
            <span>Belanja mudah, pilihan tepat.</span>
        </div>
    </div>
</footer>
