<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'gudang'], true);
    }

    protected function prepareForValidation(): void
    {
        $items = $this->input('items');
        if (is_string($items)) {
            $items = json_decode($items, true);
        }
        if (is_array($items)) {
            $items = array_map(fn ($row) => is_array($row)
                ? ['item_id' => $row['item_id'] ?? $row['id'] ?? null, 'jumlah' => $row['jumlah'] ?? null]
                : $row, $items);
        }
        $this->merge(['items' => $items]);
        if (! $this->routeIs('pos.store')) {
            $this->merge(['transaction_type' => $this->routeIs('incoming.store') ? 'incoming' : 'outgoing']);
        }
    }

    public function rules(): array
    {
        return [
            'transaction_type' => ['required', 'in:incoming,outgoing'],
            'tanggal' => ['required', 'date'],
            'supplier_id' => ['exclude_unless:transaction_type,incoming', 'required', 'integer', 'exists:suppliers,id'],
            'tujuan' => ['exclude_unless:transaction_type,outgoing', 'required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1', 'max:500'],
            'items.*' => ['required', 'array'],
            'items.*.item_id' => ['required', 'integer', 'exists:items,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1', 'max:2147483647'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Pilih atau scan minimal satu barang.',
            'items.array' => 'Daftar barang tidak valid.',
            'items.*.item_id.exists' => 'Barang yang dipilih tidak ditemukan.',
            'items.*.jumlah.integer' => 'Jumlah barang harus berupa bilangan bulat.',
            'items.*.jumlah.min' => 'Jumlah barang minimal satu.',
            'items.*.jumlah.max' => 'Jumlah barang terlalu besar.',
            'supplier_id.required' => 'Supplier pemasok wajib dipilih.',
            'tujuan.required' => 'Tujuan / divisi penerima wajib diisi.',
            'tanggal.date' => 'Tanggal transaksi tidak valid.',
        ];
    }
}
