<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryTransactionRequest;
use App\Models\GoodsRequest;
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

    public function index(Request $request)
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:255']]);
        $transactions = $this->incomingRepo->getPaginated(15, trim($filters['search'] ?? ''));

        $pendingRequests = GoodsRequest::withCount('details')->where('status', 'approved')
            ->when(trim($filters['search'] ?? ''), fn ($query, $search) => $query->where(function ($query) use ($search) {
                $query->where('kode', 'like', '%'.$search.'%')->orWhere('penerima', 'like', '%'.$search.'%');
            }))->latest('approved_at')->paginate(15, ['*'], 'pending_page')->withQueryString();

        return view('incoming.index', compact('transactions', 'pendingRequests'));
    }

    public function create()
    {
        $items = Item::orderBy('nama_barang', 'asc')->get();
        $categories = $this->categoryService->getAllCategories();
        $suppliers = $this->supplierService->getAllSuppliers();

        return view('incoming.create', compact('items', 'categories', 'suppliers'));
    }

    public function store(InventoryTransactionRequest $request)
    {
        $itemsData = $request->validated('items');
        try {
            $headerData = [
                'supplier_id' => $request->supplier_id,
                'tanggal' => $request->tanggal,
                'keterangan' => $request->keterangan,
            ];

            $transaction = $this->inventoryService->processIncomingTransaction($headerData, $itemsData, auth()->id());

            return redirect()->route('incoming.receipt', $transaction->id)->with('success', 'Transaksi barang masuk berhasil disimpan. Stok barang telah diperbarui.')->with('receipt_url', route('incoming.receipt', $transaction->id));
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

        return redirect()->route('incoming.receipt', $transaction->id);
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
