@extends('template.layout')

@section('title', 'Keranjang')

@section('content')
    <div class="container">
        <div class="mb-4">
            <span class="product-category">PESANANMU</span>
            <h1 class="h2 fw-bold mt-1 mb-1">Keranjang belanja</h1>
            <p class="text-secondary mb-0">{{ $itemCount }} barang di keranjang</p>
        </div>

        @if ($items->isEmpty())
            <div class="cart-panel p-4 p-md-5 text-center">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 72px; height: 72px; color: #207a52; background: #eaf4ed;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M3 4h2l2.2 11.1a2 2 0 0 0 2 1.6h8.5a2 2 0 0 0 1.9-1.4L21 9H7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="10" cy="20" r="1.4" fill="currentColor"/><circle cx="18" cy="20" r="1.4" fill="currentColor"/>
                    </svg>
                </div>
                <h2 class="h4 fw-bold">Keranjangmu masih kosong</h2>
                <p class="text-secondary mb-4">Yuk, temukan produk yang kamu suka dan tambahkan ke keranjang.</p>
                <a class="shop-button d-inline-flex" href="{{ route('home') }}#produk">Mulai belanja</a>
            </div>
        @else
            <div class="row g-4 align-items-start">
                <div class="col-lg-8">
                    <div class="cart-panel p-3 p-md-4">
                        @foreach ($items as $item)
                            <div class="d-flex gap-3 {{ !$loop->last ? 'border-bottom pb-4 mb-4' : '' }}">
                                <img class="cart-item-image flex-shrink-0" src="{{ $item['product']['image'] }}" alt="{{ $item['product']['name'] }}">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex flex-column flex-sm-row justify-content-between gap-2">
                                        <div>
                                            <span class="product-category">{{ $item['product']['category'] }}</span>
                                            <h2 class="h6 fw-bold mt-1 mb-1">{{ $item['product']['name'] }}</h2>
                                            <span class="small text-secondary">Rp{{ number_format($item['product']['price'], 0, ',', '.') }} / item</span>
                                        </div>
                                        <strong class="text-nowrap">Rp{{ number_format($item['line_total'], 0, ',', '.') }}</strong>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-3">
                                        <form class="d-flex align-items-center gap-2" action="{{ route('cart.update', $item['product']['id']) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <label class="small text-secondary" for="quantity-{{ $item['product']['id'] }}">Jumlah</label>
                                            <input class="form-control form-control-sm cart-quantity" id="quantity-{{ $item['product']['id'] }}" type="number" name="quantity" min="1" max="{{ $item['product']['stock'] }}" value="{{ $item['quantity'] }}">
                                            <button class="btn btn-sm btn-outline-secondary" type="submit">Ubah</button>
                                        </form>
                                        <form action="{{ route('cart.remove', $item['product']['id']) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-link text-danger text-decoration-none" type="submit">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <a class="shop-link d-inline-flex align-items-center gap-2 mt-3 fw-semibold" href="{{ route('home') }}#produk">
                        <span aria-hidden="true">&larr;</span> Lanjut belanja
                    </a>
                </div>

                <aside class="col-lg-4">
                    <div class="summary-panel p-4">
                        <h2 class="h5 fw-bold mb-4">Ringkasan belanja</h2>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-secondary">Subtotal</span>
                            <span>Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-secondary">Pengiriman</span>
                            <span>{{ $shipping === 0 ? 'Gratis' : 'Rp'.number_format($shipping, 0, ',', '.') }}</span>
                        </div>
                        @if ($subtotal > 0 && $subtotal < 300000)
                            <p class="small text-secondary">Tambah belanja Rp{{ number_format(300000 - $subtotal, 0, ',', '.') }} lagi untuk gratis ongkir.</p>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <strong>Total</strong>
                            <strong class="fs-5">Rp{{ number_format($total, 0, ',', '.') }}</strong>
                        </div>
                        <button class="shop-button w-100" type="button" disabled aria-describedby="checkout-note">Lanjut ke pembayaran</button>
                        <p id="checkout-note" class="small text-secondary text-center mt-3 mb-0">Checkout belum tersedia. Hubungkan penyedia pembayaran sebelum menerima pesanan.</p>
                    </div>
                </aside>
            </div>
        @endif
    </div>
@endsection