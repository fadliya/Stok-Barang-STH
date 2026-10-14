<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockAdjustment;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class StockOpnameController extends Controller
{
    public function index()
    {
        $items = Item::orderBy('nama_barang')->get();
        $adjustments = StockAdjustment::with(['item', 'user'])->latest('id')->paginate(15);

        return view('stock-opname.index', compact('items', 'adjustments'));
    }

    public function store(Request $request, InventoryService $inventory)
    {
        $data = $request->validate([
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'stok_sesudah' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'tanggal' => ['required', 'date'],
            'alasan' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
        ]);

        $inventory->processStockAdjustment($data, $request->user()->id);

        return redirect()->route('stock-opname.index')->with('success', 'Stok opname disimpan. Stok barang telah disesuaikan dengan stok fisik.');
    }
}
