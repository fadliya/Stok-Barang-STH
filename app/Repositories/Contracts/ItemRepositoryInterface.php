<?php

namespace App\Repositories\Contracts;

use App\Models\Item;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ItemRepositoryInterface
{
    public function getAll(): Collection;

    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function findById(int $id): ?Item;

    public function findByKode(string $kode): ?Item;

    public function create(array $data): Item;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function getLowStockItems(): Collection;

    public function getLatestItems(int $limit = 5): Collection;

    public function lockById(int $id): ?Item;

    public function updateStock(int $id, int $newStock): bool;

    public function incrementStock(int $id, int $qty): bool;

    public function decrementStock(int $id, int $qty): bool;
}
