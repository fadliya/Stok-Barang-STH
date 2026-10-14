<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Repositories\Contracts\OutgoingTransactionRepositoryInterface;
use App\Services\CategoryService;
use App\Services\InventoryService;
use App\Services\SupplierService;
use Exception;
use Illuminate\Http\Request;

class OutgoingItemController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected CategoryService $categoryService,
        protected SupplierService $supplierService,
        protected OutgoingTransactionRepositoryInterface $outgoingRepo
    ) {}

    public function index()
    {
        $transactions = $this->outgoingRepo->getPaginated(15);

        return view('outgoing.index', compact('transactions'));
    }

    public function create()
    {
        $items = Item::where('stok', '>', 0)->orderBy('nama_barang', 'asc')->get();
        $categories = $this->categoryService->getAllCategories();
        $suppliers = $this->supplierService->getAllSuppliers();

        return view('outgoing.create', compact('items', 'categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => ['required', 'date'],
            'tujuan' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'items' => ['required'],
        ], [
            'tanggal.required' => 'Tanggal pengeluaran wajib diisi.',
            'tujuan.required' => 'Tujuan pengiriman / divisi wajib diisi.',
            'items.required' => 'Daftar barang tidak boleh kosong.',
        ]);

        try {
            $rawItems = is_array($request->items) ? $request->items : json_decode($request->items, true);
            if (! is_array($rawItems) || count($rawItems) === 0) {
                return redirect()->back()->withInput()->with('error', 'Silakan pilih minimal 1 barang sebelum menyimpan.');
            }

            $itemsData = array_map(function ($row) {
                return [
                    'item_id' => $row['item_id'] ?? $row['id'] ?? null,
                    'jumlah' => (int) ($row['jumlah'] ?? 1),
                ];
            }, $rawItems);

            $headerData = [
                'tanggal' => $request->tanggal,
                'tujuan' => $request->tujuan,
                'keterangan' => $request->keterangan,
            ];

            $transaction = $this->inventoryService->processOutgoingTransaction($headerData, $itemsData, auth()->id());

            return redirect()->route('outgoing.index')->with('success', 'Transaksi barang keluar berhasil disimpan. Stok barang telah dikurangi.')->with('receipt_url', route('outgoing.receipt', $transaction->id));
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $transaction = $this->outgoingRepo->findById($id);
        if (! $transaction) {
            return redirect()->route('outgoing.index')->with('error', 'Data transaksi tidak ditemukan.');
        }

        return view('outgoing.show', compact('transaction'));
    }

    public function destroy($id)
    {
        try {
            $this->inventoryService->deleteOutgoingTransaction($id);

            return redirect()->route('outgoing.index')->with('success', 'Transaksi barang keluar berhasil dihapus dan stok barang telah dikembalikan.');
        } catch (Exception $e) {
            return redirect()->route('outgoing.index')->with('error', $e->getMessage());
        }
    }
}
