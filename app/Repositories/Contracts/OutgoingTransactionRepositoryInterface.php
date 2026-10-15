<?php

namespace App\Repositories\Contracts;

use App\Models\OutgoingTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OutgoingTransactionRepositoryInterface
{
    public function getAll(): Collection;

    public function getPaginated(int $perPage = 15, string $search = ''): LengthAwarePaginator;

    public function findById(int $id): ?OutgoingTransaction;

    public function createHeader(array $data): OutgoingTransaction;

    public function createDetail(OutgoingTransaction $transaction, array $detailData);

    public function delete(int $id): bool;

    public function getTodayCount(): int;

    public function getMonthlyStats(): array;

    public function getFiltered(?string $startDate, ?string $endDate, ?string $month, ?string $year): Collection;
}
