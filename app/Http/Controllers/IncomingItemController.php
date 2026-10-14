<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Repositories\Contracts\IncomingTransactionRepositoryInterface;
use App\Services\CategoryService;
use App\Services\InventoryService;
use App\Services\SupplierService;
use Exception;
use Illuminate\Http\Request;

class IncomingItemController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected CategoryService $categoryService,
        protected SupplierService $supplierService,
        protected IncomingTransactionRepositoryInterface $incomingRepo
    ) {}

    public function index()
    {
        $transactions = $this->incomingRepo->getPaginated(15);

        return view('incoming.index', compact('transactions'));
    }

    public function create()
    {
        $items = Item::orderBy('nama_barang', 'asc')->get();
        $categories = $this->categoryService->getAllCategories();
        $suppliers = $this->supplierService->getAllSuppliers();

        return view('incoming.create', compact('items', 'categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'tanggal' => ['required'],
            'keterangan' => ['nullable', 'string'],
            'items' => ['required'],
        ], [
            'supplier_id.required' => 'Supplier pemasok wajib dipilih.',
            'supplier_id.exists' => 'Supplier tidak ditemukan.',
            'tanggal.required' => 'Tanggal penerimaan wajib diisi.',
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
                'supplier_id' => $request->supplier_id,
                'tanggal' => $request->tanggal,
                'keterangan' => $request->keterangan,
            ];

            $transaction = $this->inventoryService->processIncomingTransaction($headerData, $itemsData, auth()->id());

            return redirect()->route('incoming.index')->with('success', 'Transaksi barang masuk berhasil disimpan. Stok barang telah diperbarui.')->with('receipt_url', route('incoming.receipt', $transaction->id));
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $transaction = $this->incomingRepo->findById($id);
        if (! $transaction) {
            return redirect()->route('incoming.index')->with('error', 'Data transaksi tidak ditemukan.');
        }

        return view('incoming.show', compact('transaction'));
    }

    public function destroy($id)
    {
        try {
            $this->inventoryService->deleteIncomingTransaction($id);

            return redirect()->route('incoming.index')->with('success', 'Transaksi barang masuk berhasil dihapus dan stok barang telah disesuaikan.');
        } catch (Exception $e) {
            return redirect()->route('incoming.index')->with('error', $e->getMessage());
        }
    }
}
