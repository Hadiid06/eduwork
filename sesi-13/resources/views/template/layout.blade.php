<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'EduShop') | EduShop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --shop-ink: #17211b;
            --shop-muted: #748078;
            --shop-green: #207a52;
            --shop-green-dark: #15583b;
            --shop-surface: #f6f8f6;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: var(--shop-ink);
            background: #fff;
            font-family: 'DM Sans', sans-serif;
        }

        h1, h2, h3, h4, h5, h6, .brand-name {
            font-family: 'Manrope', sans-serif;
        }

        .shop-main {
            flex: 1;
            padding-top: 2rem;
            padding-bottom: 4rem;
        }

        .announcement-bar {
            color: #fff;
            background: var(--shop-green-dark);
            font-size: .82rem;
        }

        .product-card, .cart-panel, .summary-panel {
            overflow: hidden;
            border: 1px solid #e9eee9;
            border-radius: 1.1rem;
            background: #fff;
            box-shadow: 0 8px 26px rgb(23 33 27 / 4%);
        }

        .product-card {
            display: flex;
            flex-direction: column;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 34px rgb(23 33 27 / 9%);
        }

        .product-image-wrap {
            position: relative;
            display: flex;
            height: 230px;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #f5f7f4;
        }

        .product-image-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .3s ease;
        }

        .product-card:hover .product-image-wrap img {
            transform: scale(1.04);
        }

        .product-badge {
            position: absolute;
            z-index: 1;
            top: 14px;
            left: 14px;
            padding: .35rem .65rem;
            border-radius: 999px;
            color: var(--shop-green-dark);
            background: #e8f4ec;
            font-size: .72rem;
            font-weight: 700;
        }

        .product-card-body {
            display: flex;
            flex: 1;
            flex-direction: column;
            padding: 1.1rem;
        }

        .product-category {
            color: var(--shop-green);
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .product-name {
            margin: .45rem 0;
            font-size: 1rem;
            font-weight: 700;
        }

        .product-description {
            margin-bottom: .75rem;
            color: var(--shop-muted);
            font-size: .88rem;
            line-height: 1.6;
        }

        .product-price {
            font-weight: 800;
            font-size: 1.05rem;
        }

        .add-cart-button {
            display: inline-flex;
            width: 42px;
            height: 42px;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 50%;
            color: #fff;
            background: var(--shop-green);
            transition: background-color .2s ease;
        }

        .add-cart-button:hover {
            background: var(--shop-green-dark);
        }

        .hero-panel {
            position: relative;
            overflow: hidden;
            border-radius: 1.5rem;
            color: #fff;
            background: linear-gradient(110deg, #173f30 0%, #207a52 65%, #70a980 100%);
        }

        .hero-panel::after {
            position: absolute;
            top: -110px;
            right: -60px;
            width: 340px;
            height: 340px;
            border: 1px solid rgb(255 255 255 / 18%);
            border-radius: 50%;
            box-shadow: 0 0 0 45px rgb(255 255 255 / 5%), 0 0 0 90px rgb(255 255 255 / 4%);
            content: '';
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 620px;
            padding: clamp(2rem, 6vw, 4.5rem);
        }

        .category-chip {
            display: inline-flex;
            padding: .55rem .9rem;
            border: 1px solid #e4eae4;
            border-radius: 999px;
            color: var(--shop-ink);
            background: #fff;
            font-size: .88rem;
            font-weight: 600;
            text-decoration: none;
        }

        .category-chip.active, .category-chip:hover {
            border-color: var(--shop-green);
            color: var(--shop-green-dark);
            background: #eff7f0;
        }

        .cart-item-image {
            width: 96px;
            height: 96px;
            border-radius: .8rem;
            object-fit: cover;
            background: var(--shop-surface);
        }

        .cart-quantity {
            width: 76px;
        }

        @media (max-width: 575.98px) {
            .product-image-wrap { height: 190px; }
            .cart-item-image { width: 74px; height: 74px; }
        }

        .shop-button {
            border: 0;
            border-radius: 999px;
            padding: .7rem 1.15rem;
            color: #fff;
            background: var(--shop-green);
            font-weight: 700;
            text-decoration: none;
            transition: background-color .2s ease, transform .2s ease;
        }

        .shop-button:hover {
            color: #fff;
            background: var(--shop-green-dark);
            transform: translateY(-1px);
        }

        .shop-link {
            color: var(--shop-ink);
            text-decoration: none;
        }

        .shop-link:hover {
            color: var(--shop-green);
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="announcement-bar py-2 text-center">
        Gratis ongkir untuk belanja minimal Rp300.000
    </div>
    @include('template.navbar')

    <main class="shop-main">
        @if (session('success'))
            <div class="container">
                <div class="alert alert-success alert-dismissible fade show" role="status">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            </div>
        @endif
        @if ($errors->any())
            <div class="container">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            </div>
        @endif
        @yield('content')
    </main>

    @include('template.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
