<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsRequest extends Model
{
    protected $fillable = ['kode', 'tanggal', 'supplier_id', 'penerima', 'alamat', 'user_id', 'pemohon', 'keterangan'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function details()
    {
        return $this->hasMany(GoodsRequestDetail::class);
    }
}
