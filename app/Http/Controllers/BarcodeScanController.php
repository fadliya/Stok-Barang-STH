<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\IncomingTransaction;
use App\Models\IncomingTransactionDetail;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BarcodeScanController extends Controller
{
    public function scan(Request $request)
    {
        $barcode = trim($request->input('barcode'));

        if (! $barcode) {
            return response()->json(['success' => false, 'message' => 'Barcode tidak boleh kosong.'], 400);
        }

        $item = Item::with(['category', 'supplier'])
            ->where('barcode', $barcode)
            ->orWhere('kode_barang', $barcode)
            ->first();

        if ($item) {
            return response()->json([
                'success' => true,
                'item' => [
                    'id' => $item->id,
                    'kode_barang' => $item->kode_barang,
                    'barcode' => $item->barcode ?? $item->kode_barang,
                    'nama_barang' => $item->nama_barang,
                    'satuan' => $item->satuan,
                    'stok' => $item->stok,
                    'minimum_stok' => $item->minimum_stok,
                    'category_name' => $item->category->nama ?? '-',
                    'supplier_name' => $item->supplier->nama ?? '-',
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'barcode' => $barcode,
            'message' => "Barang dengan barcode '{$barcode}' belum terdaftar.",
        ], 404);
    }

    public function quickStore(Request $request)
    {
        $request->validate([
            'kode_barang' => ['required', 'string', 'max:100', 'unique:items,kode_barang'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:items,barcode'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== 'NEW' && (! ctype_digit((string) $value) || ! Category::whereKey($value)->exists())) {
                    $fail('Kategori tidak ditemukan.');
                }
            }],
            'new_category_name' => ['nullable', 'string', 'max:255'],
            'supplier_id' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== 'NEW' && (! ctype_digit((string) $value) || ! Supplier::whereKey($value)->exists())) {
                    $fail('Supplier tidak ditemukan.');
                }
            }],
            'new_supplier_name' => ['nullable', 'string', 'max:255'],
            'satuan' => ['required', 'string', 'max:50'],
            'stok' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'minimum_stok' => ['required', 'integer', 'min:0'],
            'keterangan' => ['nullable', 'string'],
        ], [
            'kode_barang.required' => 'Kode barang wajib diisi.',
            'kode_barang.unique' => 'Kode barang sudah digunakan.',
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'satuan.required' => 'Satuan barang wajib diisi.',
            'minimum_stok.required' => 'Minimum stok wajib diisi.',
        ]);

        $request->validate([
            'new_category_name' => [Rule::requiredIf(! $request->category_id || $request->category_id === 'NEW'), 'nullable', 'string', 'max:255'],
            'new_supplier_name' => [Rule::requiredIf(! $request->supplier_id || $request->supplier_id === 'NEW'), 'nullable', 'string', 'max:255'],
        ]);
        $receiptUrl = null;
        $initialStock = (int) $request->input('stok', 0);

        try {
            $item = DB::transaction(function () use ($request, $initialStock, &$receiptUrl) {
                // Auto-create Category if new_category_name is supplied
                $categoryId = $request->category_id;
                if ((! $categoryId || $categoryId === 'NEW') && $request->filled('new_category_name')) {
                    $category = Category::firstOrCreate([
                        'nama' => trim($request->new_category_name),
                    ]);
                    $categoryId = $category->id;
                }

                // Auto-create Supplier if new_supplier_name is supplied
                $supplierId = $request->supplier_id;
                if ((! $supplierId || $supplierId === 'NEW') && $request->filled('new_supplier_name')) {
                    $supplierName = trim($request->new_supplier_name);
                    $slugEmail = Str::slug($supplierName).'-'.substr(uniqid(), -4).'@supplier.com';

                    $supplier = Supplier::firstOrCreate(
                        ['nama' => $supplierName],
                        [
                            'telepon' => '-',
                            'email' => $slugEmail,
                            'alamat' => '-',
                        ]
                    );
                    $supplierId = $supplier->id;
                }

                $data = $request->only([
                    'kode_barang', 'barcode', 'nama_barang', 'satuan', 'minimum_stok', 'keterangan',
                ]);
                $data['category_id'] = $categoryId;
                $data['supplier_id'] = $supplierId;
                $data['stok'] = $initialStock;

                $item = Item::create($data);
                $item->load(['category', 'supplier']);

                // Auto-create Inbound Audit Log if initial stock > 0
                if ($initialStock > 0) {
                    $incomingTrx = IncomingTransaction::create([
                        'kode_transaksi' => 'TRX-IN-AUTO-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4)),
                        'supplier_id' => $item->supplier_id,
                        'user_id' => auth()->id(),
                        'tanggal' => date('Y-m-d'),
                        'keterangan' => "Stok Penerimaan Awal (Auto-Inbound POS pada Registrasi Cepat Barang '{$item->nama_barang}')",
                    ]);

                    IncomingTransactionDetail::create([
                        'incoming_transaction_id' => $incomingTrx->id,
                        'item_id' => $item->id,
                        'jumlah' => $initialStock,
                    ]);
                }

                if (isset($incomingTrx)) {
                    $receiptUrl = route('incoming.receipt', $incomingTrx->id);
                }

                return $item;
            });
        } catch (\Exception $e) {
            Log::error('Gagal registrasi cepat barang: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan barang. Silakan coba lagi.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Barang '{$item->nama_barang}' berhasil terdaftar dan masuk ke daftar transaksi.",
            'receipt_url' => $receiptUrl,
            'item' => [
                'id' => $item->id,
                'kode_barang' => $item->kode_barang,
                'barcode' => $item->barcode ?? $item->kode_barang,
                'nama_barang' => $item->nama_barang,
                'satuan' => $item->satuan,
                'stok' => $item->stok,
                'minimum_stok' => $item->minimum_stok,
                'category_name' => $item->category->nama ?? '-',
                'supplier_name' => $item->supplier->nama ?? '-',
            ],
        ]);
    }
}
