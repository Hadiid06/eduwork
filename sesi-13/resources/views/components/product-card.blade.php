@props(['id', 'image', 'name', 'description', 'category', 'price', 'badge' => null, 'link' => '#'])

<article class="product-card h-100">
    <a class="product-image-wrap" href="{{ $link }}" aria-label="Lihat {{ $name }}">
        @if ($badge)
            <span class="product-badge">{{ $badge }}</span>
        @endif
        <img src="{{ $image }}" alt="{{ $name }}" loading="lazy">
    </a>
    <div class="product-card-body">
        <span class="product-category">{{ $category }}</span>
        <h2 class="product-name">{{ $name }}</h2>
        <p class="product-description">{{ $description }}</p>
        <div class="d-flex align-items-center justify-content-between gap-3 mt-auto pt-2">
            <span class="product-price">Rp{{ number_format($price, 0, ',', '.') }}</span>
            <form action="{{ route('cart.add', $id) }}" method="POST">
                @csrf
                <button class="add-cart-button" type="submit" aria-label="Tambah {{ $name }} ke keranjang">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</article>