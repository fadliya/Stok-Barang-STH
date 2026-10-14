<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsRequestDetail extends Model
{
    protected $fillable = ['item_id', 'kode_barang', 'nama_barang', 'satuan', 'stok_saat_permintaan', 'jumlah'];
}
