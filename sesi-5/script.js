
const produk = [
    {
        nama: "Headphone",
        harga: 250000,
        deskripsi: "Headphone untuk mendengarkan musik.",
        gambar: "images/andorid.png",
        kategori: "Elektronik"
    },
    {
        nama: "xiomi 13",
        harga: 450000,
        deskripsi: "HP pintar untuk aktivitas harian.",
        gambar: "images/handphone-xiomi.jpg",
        kategori: "Elektronik"
    },
    {
        nama: "Kaos Basic",
        harga: 85000,
        deskripsi: "Kaos nyaman untuk sehari-hari.",
        gambar: "images/kaos.jpg",
        kategori: "Pakaian"
    },
    {
        nama: "Jaket Casual",
        harga: 180000,
        deskripsi: "Jaket dengan desain modern.",
        gambar: "images/jaket.jpg",
        kategori: "Pakaian"
    }
];

const daftarProduk = document.getElementById("daftarProduk");
const filterKategori = document.getElementById("filterKategori");

function tampilkanProduk(dataProduk) {
    daftarProduk.innerHTML = "";

    dataProduk.forEach(function(item) {
        daftarProduk.innerHTML += `
            <div class="col-md-6 col-lg-3">
                <div class="card kartu-produk shadow-sm">
                    <img
                        src="${item.gambar}"
                        class="card-img-top gambar-produk"
                        alt="${item.nama}"
                    >

                    <div class="card-body">
                        <h5 class="card-title">${item.nama}</h5>

                        <p class="text-primary fw-bold">
                            Rp ${item.harga.toLocaleString("id-ID")}
                        </p>

                        <p class="card-text">${item.deskripsi}</p>

                        <span class="badge bg-secondary">
                            ${item.kategori}
                        </span>
                    </div>
                </div>
            </div>
        `;
    });

    if (dataProduk.length === 0) {
        daftarProduk.innerHTML = `
            <div class="col-12 text-center">
                <p>Produk tidak ditemukan.</p>
            </div>
        `;
    }
}

filterKategori.addEventListener("change", function() {
    const kategoriDipilih = filterKategori.value;

    const hasilFilter = produk.filter(function(item) {
        if (kategoriDipilih === "Semua") {
            return true;
        }

        return item.kategori === kategoriDipilih;
    });

    tampilkanProduk(hasilFilter);
});

// Tampilkan semua produk saat halaman pertama dibuka
tampilkanProduk(produk);
