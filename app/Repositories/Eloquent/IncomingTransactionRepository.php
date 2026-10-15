<?php

namespace App\Repositories\Eloquent;

use App\Models\IncomingTransaction;
use App\Models\IncomingTransactionDetail;
use App\Repositories\Contracts\IncomingTransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class IncomingTransactionRepository implements IncomingTransactionRepositoryInterface
{
    public function getAll(): Collection
    {
        return IncomingTransaction::with(['supplier', 'user', 'details.item'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getPaginated(int $perPage = 15, string $search = ''): LengthAwarePaginator
    {
        return IncomingTransaction::with(['supplier', 'user', 'details.item'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('kode_transaksi', 'like', '%'.$search.'%')
                        ->orWhereHas('supplier', fn ($supplier) => $supplier->where('nama', 'like', '%'.$search.'%'))
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(int $id): ?IncomingTransaction
    {
        return IncomingTransaction::with(['supplier', 'user', 'details.item'])->find($id);
    }

    public function createHeader(array $data): IncomingTransaction
    {
        return IncomingTransaction::create($data);
    }

    public function createDetail(IncomingTransaction $transaction, array $detailData)
    {
        return $transaction->details()->create($detailData);
    }

    public function delete(int $id): bool
    {
        $record = IncomingTransaction::find($id);
        if (! $record) {
            return false;
        }

        return $record->delete();
    }

    public function getTodayCount(): int
    {
        return IncomingTransactionDetail::whereHas('transaction', function ($q) {
            $q->whereDate('tanggal', now()->format('Y-m-d'));
        })->sum('jumlah');
    }

    public function getMonthlyStats(): array
    {
        $months = [];
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthLabel = $date->translatedFormat('M Y');
            $sum = IncomingTransactionDetail::whereHas('transaction', function ($q) use ($date) {
                $q->whereYear('tanggal', $date->year)->whereMonth('tanggal', $date->month);
            })->sum('jumlah');
            $months[] = $monthLabel;
            $data[] = (int) $sum;
        }

        return ['labels' => $months, 'data' => $data];
    }

    public function getFiltered(?string $startDate, ?string $endDate, ?string $month, ?string $year): Collection
    {
        $query = IncomingTransaction::with(['supplier', 'user', 'details.item.category']);

        if ($startDate && $endDate) {
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        }

        if ($month) {
            $query->whereMonth('tanggal', $month);
        }

        if ($year) {
            $query->whereYear('tanggal', $year);
        }

        return $query->orderBy('tanggal', 'desc')->get();
    }
}
