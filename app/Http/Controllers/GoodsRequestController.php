<?php

namespace App\Http\Controllers;

use App\Models\GoodsRequest;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GoodsRequestController extends Controller
{
    public function index()
    {
        $requests = GoodsRequest::withCount('details')->latest('id')->paginate(15);

        return view('goods-requests.index', compact('requests'));
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
        ]);
        $supplierId = $data['supplier_id'] ?? null;
        $suppliers = Supplier::orderBy('nama')->get();
        $items = Item::query()
            ->where('supplier_id', $supplierId)
            ->orderBy('nama_barang')->get();

        return view('goods-requests.create', compact('supplierId', 'suppliers', 'items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
            'selected' => ['required', 'array', 'min:1', 'max:500'],
            'selected.*' => ['required', 'integer', 'distinct', 'exists:items,id'],
            'quantities' => ['required', 'array'],
        ], ['selected.required' => 'Ceklis minimal satu barang yang diperlukan.']);

        $document = DB::transaction(function () use ($data, $request) {
            $items = Item::whereIn('id', $data['selected'])->orderBy('id')->lockForUpdate()->get();
            if ($items->count() !== count($data['selected'])) {
                throw ValidationException::withMessages(['selected' => 'Data barang berubah. Silakan pilih ulang barang.']);
            }
            $details = [];
            foreach ($items as $item) {
                if ($item->supplier_id != $data['supplier_id']) {
                    throw ValidationException::withMessages(['supplier_id' => 'Semua barang pada surat harus berasal dari supplier yang dipilih.']);
                }
                $quantity = $data['quantities'][$item->id] ?? null;
                validator(['jumlah' => $quantity], ['jumlah' => ['required', 'integer', 'min:1', 'max:2147483647']], [
                    'jumlah.required' => "Isi jumlah permintaan untuk {$item->nama_barang}.",
                    'jumlah.integer' => "Jumlah {$item->nama_barang} harus berupa bilangan bulat.",
                    'jumlah.min' => "Jumlah {$item->nama_barang} minimal 1.",
                ])->validate();
                $details[] = [
                    'item_id' => $item->id, 'kode_barang' => $item->kode_barang,
                    'nama_barang' => $item->nama_barang, 'satuan' => $item->satuan,
                    'stok_saat_permintaan' => $item->stok, 'jumlah' => (int) $quantity,
                ];
            }
            $supplier = Supplier::findOrFail($data['supplier_id']);
            $document = GoodsRequest::create([
                'kode' => 'PO-'.now()->format('Ymd').'-'.Str::upper(Str::random(10)),
                'tanggal' => $data['tanggal'],
                'supplier_id' => $supplier->id, 'penerima' => $supplier->nama,
                'alamat' => $supplier->alamat, 'user_id' => $request->user()->id,
                'pemohon' => $request->user()->name, 'keterangan' => $data['keterangan'] ?? null,
            ]);
            $document->details()->createMany($details);

            return $document;
        });

        return redirect()->route('goods-requests.show', $document)->with('success', 'Surat permintaan disimpan dan siap dicetak.');
    }

    public function show(GoodsRequest $goodsRequest)
    {
        $goodsRequest->load('details');

        return view('goods-requests.show', ['document' => $goodsRequest]);
    }

    public function print(GoodsRequest $goodsRequest)
    {
        $goodsRequest->load('details');

        return view('goods-requests.print', ['document' => $goodsRequest]);
    }
}
