<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use App\Services\InventoryService;
use App\Services\SupplierService;
use Exception;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected CategoryService $categoryService,
        protected SupplierService $supplierService
    ) {}

    public function index()
    {
        $categories = $this->categoryService->getAllCategories();
        $suppliers = $this->supplierService->getAllSuppliers();

        return view('pos.scan', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'transaction_type' => ['required', 'in:incoming,outgoing'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string'],
            'items' => ['required', 'string'],
        ], [
            'transaction_type.required' => 'Jenis transaksi wajib dipilih (Barang Masuk / Keluar).',
            'tanggal.required' => 'Tanggal transaksi wajib diisi.',
            'items.required' => 'Daftar barang hasil scan tidak boleh kosong.',
        ]);

        $rawItemsData = json_decode($request->items, true);
        if (! is_array($rawItemsData) || count($rawItemsData) === 0) {
            return redirect()->back()->withInput()->with('error', 'Silakan melakukan scan minimal 1 barang terlebih dahulu.');
        }

        $itemsData = array_map(function ($row) {
            return [
                'item_id' => $row['item_id'] ?? $row['id'] ?? null,
                'jumlah' => (int) ($row['jumlah'] ?? 1),
            ];
        }, $rawItemsData);

        try {
            if ($request->transaction_type === 'incoming') {
                $request->validate([
                    'supplier_id' => ['required', 'exists:suppliers,id'],
                ], [
                    'supplier_id.required' => 'Supplier pemasok wajib dipilih untuk transaksi Barang Masuk.',
                    'supplier_id.exists' => 'Supplier tidak ditemukan.',
                ]);

                $headerData = [
                    'supplier_id' => $request->supplier_id,
                    'tanggal' => $request->tanggal,
                    'keterangan' => $request->keterangan,
                ];

                $transaction = $this->inventoryService->processIncomingTransaction($headerData, $itemsData, auth()->id());

                return redirect()->route('incoming.index')->with('success', 'Transaksi Barang Masuk berhasil disimpan. Stok barang telah diperbarui.')->with('receipt_url', route('incoming.receipt', $transaction->id));

            } else {
                $request->validate([
                    'tujuan' => ['required', 'string', 'max:255'],
                ], [
                    'tujuan.required' => 'Tujuan / Divisi penerima wajib diisi untuk transaksi Barang Keluar.',
                ]);

                $headerData = [
                    'tanggal' => $request->tanggal,
                    'tujuan' => $request->tujuan,
                    'keterangan' => $request->keterangan,
                ];

                $transaction = $this->inventoryService->processOutgoingTransaction($headerData, $itemsData, auth()->id());

                return redirect()->route('outgoing.index')->with('success', 'Transaksi Barang Keluar berhasil disimpan. Stok barang telah dikurangi.')->with('receipt_url', route('outgoing.receipt', $transaction->id));
            }
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
