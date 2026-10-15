<?php

namespace App\Services;

use App\Repositories\Contracts\ItemRepositoryInterface;

class ItemService
{
    public function __construct(protected ItemRepositoryInterface $itemRepo) {}

    public function getAllItems()
    {
        return $this->itemRepo->getAll();
    }

    public function getItemsPaginated(int $perPage = 15, array $filters = [])
    {
        return $this->itemRepo->getPaginated($perPage, $filters);
    }

    public function getItemById(int $id)
    {
        return $this->itemRepo->findById($id);
    }

    public function createItem(array $data)
    {
        return $this->itemRepo->create($data);
    }

    public function updateItem(int $id, array $data)
    {
        return $this->itemRepo->update($id, $data);
    }

    public function deleteItem(int $id)
    {
        return $this->itemRepo->delete($id);
    }
}
