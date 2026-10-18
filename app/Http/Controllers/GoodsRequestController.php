<?php

namespace App\Http\Controllers;

use App\Models\GoodsRequest;
use App\Models\Item;
use App\Models\Supplier;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
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

        return redirect()->route('goods-requests.show', $document)->with('success', 'Pemesanan diajukan. Menunggu ACC pimpinan.');
    }

    public function show(GoodsRequest $goodsRequest)
    {
        $goodsRequest->load('details');

        return view('goods-requests.show', ['document' => $goodsRequest]);
    }

    public function print(GoodsRequest $goodsRequest)
    {
        abort_unless(in_array($goodsRequest->status, ['approved', 'received']), 403, 'Struk tersedia setelah ACC pimpinan.');
        $goodsRequest->load('details');

        return view('goods-requests.print', ['document' => $goodsRequest, 'verificationUrl' => URL::signedRoute('goods-requests.verify', $goodsRequest)]);
    }

    public function verify(GoodsRequest $goodsRequest)
    {
        abort_unless($goodsRequest->approved_at, 404);

        return view('goods-requests.verify', ['document' => $goodsRequest->load('details')]);
    }

    public function approve(Request $request, GoodsRequest $goodsRequest)
    {
        DB::transaction(function () use ($request, $goodsRequest) {
            $document = GoodsRequest::whereKey($goodsRequest->id)->lockForUpdate()->firstOrFail();
            abort_unless($document->status === 'pending', 409, 'Pemesanan sudah diproses.');
            abort_if($document->user_id === $request->user()->id, 403);
            $document->update([
                'status' => 'approved', 'approved_by' => $request->user()->id,
                'approver_name' => $request->user()->name, 'approved_at' => now(),
            ]);
        });

        return redirect()->route('goods-requests.show', $goodsRequest)->with('success', 'Pemesanan disetujui. Struk siap dicetak.');
    }

    public function scan()
    {
        return redirect()->route('incoming.index');
    }

    public function receive(Request $request, InventoryService $inventory, GoodsRequest $goodsRequest)
    {
        $data = $request->validate([
            'checked' => ['required', 'array', 'min:1', 'max:500'],
            'checked.*' => ['required', 'integer', 'distinct'],
        ], ['checked.required' => 'Periksa dan ceklis semua barang sebelum mengonfirmasi penerimaan.']);
        $transaction = DB::transaction(function () use ($data, $request, $inventory, $goodsRequest) {
            $document = GoodsRequest::whereKey($goodsRequest->id)->lockForUpdate()->firstOrFail();
            if (! $document || $document->status !== 'approved') {
                throw ValidationException::withMessages(['checked' => 'Pesanan belum disetujui atau sudah diterima.']);
            }
            $details = $document->details()->orderBy('item_id')->get();
            if ($details->pluck('id')->sort()->values()->all() !== collect($data['checked'])->map(fn ($id) => (int) $id)->sort()->values()->all()) {
                throw ValidationException::withMessages(['checked' => 'Semua barang dan jumlahnya harus sesuai pesanan. Jika belum lengkap atau tidak sesuai, biarkan pending.']);
            }
            foreach ($details as $detail) {
                $item = Item::whereKey($detail->item_id)->lockForUpdate()->firstOrFail();
                if ((int) $item->stok + (int) $detail->jumlah > 2147483647) {
                    throw ValidationException::withMessages(['checked' => 'Jumlah stok melebihi batas sistem.']);
                }
            }
            $transaction = $inventory->processIncomingTransaction([
                'supplier_id' => $document->supplier_id, 'tanggal' => now(),
                'keterangan' => 'Penerimaan pemesanan '.$document->kode,
            ], $details->map(fn ($detail) => ['item_id' => $detail->item_id, 'jumlah' => $detail->jumlah])->all(), $request->user()->id);
            $document->update([
                'status' => 'received', 'received_by' => $request->user()->id,
                'received_at' => now(), 'incoming_transaction_id' => $transaction->id,
            ]);

            return $transaction;
        });

        return redirect()->route('incoming.receipt', $transaction)->with('success', 'Seluruh barang pesanan diterima dan stok diperbarui.');
    }
}
