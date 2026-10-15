<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryTransactionRequest;
use App\Services\CategoryService;
use App\Services\InventoryService;
use App\Services\SupplierService;
use Exception;

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

    public function store(InventoryTransactionRequest $request)
    {
        $itemsData = $request->validated('items');
        try {
            if ($request->transaction_type === 'incoming') {
                $headerData = [
                    'supplier_id' => $request->supplier_id,
                    'tanggal' => $request->tanggal,
                    'keterangan' => $request->keterangan,
                ];

                $transaction = $this->inventoryService->processIncomingTransaction($headerData, $itemsData, auth()->id());

                return redirect()->route('incoming.receipt', $transaction->id)->with('success', 'Transaksi Barang Masuk berhasil disimpan. Stok barang telah diperbarui.')->with('receipt_url', route('incoming.receipt', $transaction->id));

            } else {
                $headerData = [
                    'tanggal' => $request->tanggal,
                    'tujuan' => $request->tujuan,
                    'keterangan' => $request->keterangan,
                ];

                $transaction = $this->inventoryService->processOutgoingTransaction($headerData, $itemsData, auth()->id());

                return redirect()->route('outgoing.receipt', $transaction->id)->with('success', 'Transaksi Barang Keluar berhasil disimpan. Stok barang telah dikurangi.')->with('receipt_url', route('outgoing.receipt', $transaction->id));
            }
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
