<?php

namespace App\Services;

use App\Models\GoodsRequest;
use App\Models\StockAdjustment;
use App\Repositories\Contracts\IncomingTransactionRepositoryInterface;
use App\Repositories\Contracts\ItemRepositoryInterface;
use App\Repositories\Contracts\OutgoingTransactionRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(
        protected ItemRepositoryInterface $itemRepo,
        protected IncomingTransactionRepositoryInterface $incomingRepo,
        protected OutgoingTransactionRepositoryInterface $outgoingRepo
    ) {}

    /**
     * Gabungkan baris item yang sama agar validasi & mutasi stok akurat.
     */
    protected function aggregateItems(array $itemsData): array
    {
        $aggregated = [];

        foreach ($itemsData as $itemRow) {
            $itemId = $itemRow['item_id'] ?? $itemRow['id'] ?? null;

            if ($itemId === null || ! ctype_digit((string) $itemId)) {
                throw new Exception('ID barang tidak valid!');
            }

            $qty = (int) ($itemRow['jumlah'] ?? 0);
            if ($qty <= 0) {
                throw new Exception("Jumlah untuk barang ID #{$itemId} harus lebih dari 0!");
            }

            $itemId = (int) $itemId;
            $aggregated[$itemId] = ($aggregated[$itemId] ?? 0) + $qty;
        }

        if (empty($aggregated)) {
            throw new Exception('Daftar barang transaksi tidak boleh kosong!');
        }

        return $aggregated;
    }

    public function processIncomingTransaction(array $headerData, array $itemsData, int $userId)
    {
        return DB::transaction(function () use ($headerData, $itemsData, $userId) {
            $aggregated = $this->aggregateItems($itemsData);

            $headerData['kode_transaksi'] = 'TRX-IN-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4));
            $headerData['user_id'] = $userId;

            $transaction = $this->incomingRepo->createHeader($headerData);

            foreach ($aggregated as $itemId => $qty) {
                // Kunci baris item untuk mencegah race condition antar transaksi bersamaan
                $item = $this->itemRepo->lockById($itemId);
                if (! $item) {
                    throw new Exception("Barang ID #{$itemId} tidak ditemukan!");
                }

                $this->incomingRepo->createDetail($transaction, [
                    'item_id' => $item->id,
                    'jumlah' => $qty,
                ]);

                // Tambah stok secara atomik (stok = stok + qty)
                $this->itemRepo->incrementStock($item->id, $qty);
            }

            return $transaction;
        });
    }

    public function deleteIncomingTransaction(int $id)
    {
        return DB::transaction(function () use ($id) {
            if (GoodsRequest::where('incoming_transaction_id', $id)->exists()) {
                throw new Exception('Penerimaan pemesanan supplier tidak dapat dihapus. Gunakan Stock Opname untuk koreksi stok.');
            }
            $transaction = $this->incomingRepo->findById($id);
            if (! $transaction) {
                throw new Exception('Data transaksi barang masuk tidak ditemukan!');
            }

            foreach ($transaction->details as $detail) {
                // Kunci baris sebelum membalikkan mutasi stok
                $item = $this->itemRepo->lockById($detail->item_id);
                if (! $item) {
                    continue;
                }

                // Stok saat ini lebih kecil dari jumlah yang akan ditarik:
                // berarti sebagian stok hasil transaksi ini sudah keluar/dipakai.
                if ($item->stok < $detail->jumlah) {
                    throw new Exception(
                        "Transaksi tidak dapat dihapus! Stok '{$item->nama_barang}' tersisa {$item->stok} {$item->satuan}, ".
                        "kurang dari jumlah transaksi masuk yang akan dibatalkan ({$detail->jumlah} {$item->satuan}). ".
                        'Gunakan Stock Opname untuk menyesuaikan stok.'
                    );
                }

                $this->itemRepo->decrementStock($item->id, $detail->jumlah);
            }

            return $this->incomingRepo->delete($id);
        });
    }

    public function processOutgoingTransaction(array $headerData, array $itemsData, int $userId)
    {
        return DB::transaction(function () use ($headerData, $itemsData, $userId) {
            $aggregated = $this->aggregateItems($itemsData);

            // Step 1: Validasi kecukupan stok dengan baris terkunci (mencegah oversell)
            foreach ($aggregated as $itemId => $qty) {
                $item = $this->itemRepo->lockById($itemId);
                if (! $item) {
                    throw new Exception("Barang ID #{$itemId} tidak ditemukan!");
                }

                if ($item->stok < $qty) {
                    throw new Exception("Stok barang '{$item->nama_barang}' tidak mencukupi! Stok saat ini: {$item->stok} {$item->satuan}, Jumlah diminta: {$qty} {$item->satuan}. Transaksi dibatalkan.");
                }
            }

            $headerData['kode_transaksi'] = 'TRX-OUT-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4));
            $headerData['user_id'] = $userId;

            $transaction = $this->outgoingRepo->createHeader($headerData);

            // Step 2: Buat detail dan kurangi stok secara atomik (stok = stok - qty)
            foreach ($aggregated as $itemId => $qty) {
                $this->outgoingRepo->createDetail($transaction, [
                    'item_id' => $itemId,
                    'jumlah' => $qty,
                ]);

                $this->itemRepo->decrementStock($itemId, $qty);
            }

            return $transaction;
        });
    }

    public function deleteOutgoingTransaction(int $id)
    {
        return DB::transaction(function () use ($id) {
            $transaction = $this->outgoingRepo->findById($id);
            if (! $transaction) {
                throw new Exception('Data transaksi barang keluar tidak ditemukan!');
            }

            foreach ($transaction->details as $detail) {
                $item = $this->itemRepo->lockById($detail->item_id);
                if (! $item) {
                    continue;
                }

                $this->itemRepo->incrementStock($item->id, $detail->jumlah);
            }

            return $this->outgoingRepo->delete($id);
        });
    }

    public function processStockAdjustment(array $data, int $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            // Kunci baris agar stok terbaca konsisten
            $item = $this->itemRepo->lockById((int) $data['item_id']);
            if (! $item) {
                throw new Exception('Barang tidak ditemukan!');
            }

            $stokSebelumnya = $item->stok;
            $stokSesudah = (int) $data['stok_sesudah'];
            $selisih = $stokSesudah - $stokSebelumnya;
            $tipe = $selisih >= 0 ? 'tambah' : 'kurang';

            $adjustment = StockAdjustment::create([
                'kode_penyesuaian' => 'ADJ-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4)),
                'item_id' => $item->id,
                'user_id' => $userId,
                'stok_sebelumnya' => $stokSebelumnya,
                'stok_sesudah' => $stokSesudah,
                'selisih' => abs($selisih),
                'tipe' => $tipe,
                'alasan' => $data['alasan'],
                'tanggal' => $data['tanggal'] ?? now(),
                'keterangan' => $data['keterangan'] ?? null,
            ]);

            $this->itemRepo->updateStock($item->id, $stokSesudah);

            return $adjustment;
        });
    }
}
