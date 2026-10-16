<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsRequest extends Model
{
    protected $fillable = ['kode', 'tanggal', 'supplier_id', 'penerima', 'alamat', 'user_id', 'pemohon', 'keterangan', 'status', 'approved_by', 'approver_name', 'approved_at', 'received_by', 'received_at', 'incoming_transaction_id'];

    protected function casts(): array
    {
        return ['tanggal' => 'date', 'approved_at' => 'datetime', 'received_at' => 'datetime'];
    }

    public function details()
    {
        return $this->hasMany(GoodsRequestDetail::class);
    }
}
