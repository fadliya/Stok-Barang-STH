<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_requests', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->date('tanggal');
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->string('penerima');
            $table->text('alamat')->nullable();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('pemohon');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
        Schema::create('goods_request_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->string('kode_barang');
            $table->string('nama_barang');
            $table->string('satuan');
            $table->integer('stok_saat_permintaan');
            $table->integer('jumlah');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_request_details');
        Schema::dropIfExists('goods_requests');
    }
};
