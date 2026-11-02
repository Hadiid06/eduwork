<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
    <div class="container py-2">
        <a class="navbar-brand d-flex align-items-center gap-2 me-lg-4" href="{{ url('/') }}" aria-label="EduShop, beranda">
            <span class="d-inline-flex align-items-center justify-content-center rounded-3 text-white" style="width: 38px; height: 38px; background: #207a52;">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M3 4h2l2.2 11.1a2 2 0 0 0 2 1.6h8.5a2 2 0 0 0 1.9-1.4L21 9H7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="10" cy="20" r="1.5" fill="currentColor"/>
                    <circle cx="18" cy="20" r="1.5" fill="currentColor"/>
                </svg>
            </span>
            <span class="brand-name fw-bold" style="font-size: 1.2rem; color: #17211b;">Edu<span style="color: #207a52;">Shop</span></span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#shopNavbar" aria-controls="shopNavbar" aria-expanded="false" aria-label="Buka navigasi">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="shopNavbar">
            <form class="d-flex my-3 my-lg-0 mx-lg-auto" role="search" action="{{ route('home') }}" method="GET" style="max-width: 430px; width: 100%;">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle cx="10.8" cy="10.8" r="6.8" stroke="#748078" stroke-width="1.8"/>
                            <path d="m16 16 4.2 4.2" stroke="#748078" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <input class="form-control bg-light border-start-0 rounded-end-pill py-2" type="search" name="q" value="{{ request('q') }}" placeholder="Cari produk favoritmu..." aria-label="Cari produk">
                </div>
            </form>

            <div class="d-flex align-items-center gap-3 ms-lg-4">
                <a class="shop-link fw-semibold d-none d-lg-inline" href="{{ route('home') }}#kategori">Kategori</a>
                <a class="shop-link d-flex align-items-center gap-2 fw-semibold" href="{{ route('cart.index') }}" aria-label="Keranjang belanja">
                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M3 4h2l2.2 11.1a2 2 0 0 0 2 1.6h8.5a2 2 0 0 0 1.9-1.4L21 9H7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="10" cy="20" r="1.5" fill="currentColor"/>
                        <circle cx="18" cy="20" r="1.5" fill="currentColor"/>
                    </svg>
                    <span>Keranjang</span>
                    @if (array_sum(session('cart', [])) > 0)
                        <span class="badge rounded-pill text-bg-success">{{ array_sum(session('cart', [])) }}</span>
                    @endif
                </a>
                <a class="shop-button d-inline-flex align-items-center justify-content-center" href="{{ route('cart.index') }}">Lihat keranjang</a>
            </div>
        </div>
    </div>
</nav>
