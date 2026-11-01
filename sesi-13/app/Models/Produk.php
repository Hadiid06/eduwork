<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    protected $table = 'produk';

    protected $fillable = [
        'name',
        'description',
        'stock',
        'image',
        'kategori_produk_id',
    ];

    public function kategoriProduk(): BelongsTo
    {
        return $this->belongsTo(KategoriProduk::class, 'kategori_produk_id');
    }

    public function pesanans(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'product_id');
    }
}
