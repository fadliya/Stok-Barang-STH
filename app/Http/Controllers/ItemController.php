<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemRequest;
use App\Models\Item;
use App\Services\CategoryService;
use App\Services\InventoryService;
use App\Services\ItemService;
use App\Services\SupplierService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function __construct(
        protected ItemService $itemService,
        protected CategoryService $categoryService,
        protected SupplierService $supplierService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'stock' => ['nullable', 'in:low,normal,empty'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
        ]);
        $items = $this->itemService->getItemsPaginated(15, $filters);
        $categories = $this->categoryService->getAllCategories();

        return view('items.index', compact('items', 'categories'));
    }

    public function create()
    {
        $categories = $this->categoryService->getAllCategories();
        $suppliers = $this->supplierService->getAllSuppliers();

        return view('items.create', compact('categories', 'suppliers'));
    }

    public function store(ItemRequest $request, InventoryService $inventory)
    {
        $transaction = DB::transaction(function () use ($request, $inventory) {
            $data = $request->validated();
            $initialStock = (int) $data['stok'];
            $data['stok'] = 0;
            $item = $this->itemService->createItem($data);

            return $initialStock > 0 ? $inventory->processIncomingTransaction([
                'supplier_id' => $item->supplier_id,
                'tanggal' => now(),
                'keterangan' => 'Penerimaan stok awal: '.$item->nama_barang,
            ], [['item_id' => $item->id, 'jumlah' => $initialStock]], $request->user()->id) : null;
        });

        if ($transaction) {
            return redirect()->route('incoming.receipt', $transaction->id)
                ->with('success', 'Barang ditambahkan. Stok awal tercatat sebagai penerimaan.')
                ->with('receipt_url', route('incoming.receipt', $transaction->id));
        }

        return redirect()->route('items.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function show($id)
    {
        $item = $this->itemService->getItemById($id);
        if (! $item) {
            return redirect()->route('items.index')->with('error', 'Barang tidak ditemukan.');
        }

        return view('items.show', compact('item'));
    }

    public function edit($id)
    {
        $item = $this->itemService->getItemById($id);
        if (! $item) {
            return redirect()->route('items.index')->with('error', 'Barang tidak ditemukan.');
        }
        $categories = $this->categoryService->getAllCategories();
        $suppliers = $this->supplierService->getAllSuppliers();

        return view('items.edit', compact('item', 'categories', 'suppliers'));
    }

    public function update(ItemRequest $request, $id)
    {
        $this->itemService->updateItem($id, $request->validated());

        return redirect()->route('items.index')->with('success', 'Data barang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        try {
            $this->itemService->deleteItem($id);
        } catch (QueryException $e) {
            report($e);

            return redirect()->route('items.index')->with('error', 'Barang tidak dapat dihapus karena masih memiliki riwayat transaksi. Gunakan fitur nonaktifkan jika tersedia.');
        }

        return redirect()->route('items.index')->with('success', 'Barang berhasil dihapus.');
    }

    public function printBarcodes(Request $request)
    {
        $selectedIds = $request->input('item_ids');
        if ($selectedIds && is_array($selectedIds)) {
            $items = Item::whereIn('id', $selectedIds)->get();
        } else {
            $items = $this->itemService->getAllItems();
        }

        return view('items.print-barcodes', compact('items'));
    }
}
