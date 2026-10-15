<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryTransactionRequest;
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

    public function index(Request $request)
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:255']]);
        $transactions = $this->outgoingRepo->getPaginated(15, trim($filters['search'] ?? ''));

        return view('outgoing.index', compact('transactions'));
    }

    public function create()
    {
        $items = Item::where('stok', '>', 0)->orderBy('nama_barang', 'asc')->get();
        $categories = $this->categoryService->getAllCategories();
        $suppliers = $this->supplierService->getAllSuppliers();

        return view('outgoing.create', compact('items', 'categories', 'suppliers'));
    }

    public function store(InventoryTransactionRequest $request)
    {
        $itemsData = $request->validated('items');
        try {
            $headerData = [
                'tanggal' => $request->tanggal,
                'tujuan' => $request->tujuan,
                'keterangan' => $request->keterangan,
            ];

            $transaction = $this->inventoryService->processOutgoingTransaction($headerData, $itemsData, auth()->id());

            return redirect()->route('outgoing.receipt', $transaction->id)->with('success', 'Transaksi barang keluar berhasil disimpan. Stok barang telah dikurangi.')->with('receipt_url', route('outgoing.receipt', $transaction->id));
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

        return redirect()->route('outgoing.receipt', $transaction->id);
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
