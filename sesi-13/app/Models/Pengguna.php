<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengguna extends Model
{
    protected $table = 'pengguna';

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password'];

    public function pesanans(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'user_id');
    }
}
