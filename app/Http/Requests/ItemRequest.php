<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        $itemId = $this->route('id') ?? $this->route('item');

        return [
            'kode_barang' => ['required', 'string', 'max:100', Rule::unique('items', 'kode_barang')->ignore($itemId)],
            'nama_barang' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'satuan' => ['required', 'string', 'max:50'],
            'stok' => $itemId ? ['prohibited'] : ['required', 'integer', 'min:0', 'max:2147483647'],
            'minimum_stok' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode_barang.required' => 'Kode barang wajib diisi.',
            'kode_barang.unique' => 'Kode barang sudah digunakan.',
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak valid.',
            'supplier_id.required' => 'Supplier wajib dipilih.',
            'supplier_id.exists' => 'Supplier tidak valid.',
            'satuan.required' => 'Satuan barang wajib diisi.',
            'stok.required' => 'Stok awal wajib diisi.',
            'stok.min' => 'Stok minimal 0.',
            'stok.prohibited' => 'Ubah persediaan melalui transaksi barang masuk, barang keluar, atau stok opname.',
            'minimum_stok.required' => 'Minimum stok wajib diisi.',
            'minimum_stok.min' => 'Minimum stok minimal 0.',
        ];
    }
}
