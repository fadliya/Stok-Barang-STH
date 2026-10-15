<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventorySafetyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $gudang;

    protected Category $category;

    protected Supplier $supplier;

    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Safety',
            'username' => 'admin_safety',
            'email' => 'admin_safety@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $this->gudang = User::create([
            'name' => 'Gudang Safety',
            'username' => 'gudang_safety',
            'email' => 'gudang_safety@test.com',
            'password' => bcrypt('password123'),
            'role' => 'gudang',
        ]);

        $this->category = Category::create(['nama' => 'Kategori Safety']);
        $this->supplier = Supplier::create([
            'nama' => 'Supplier Safety',
            'alamat' => 'Jl. Safety No. 1',
            'telepon' => '08111111111',
            'email' => 'safety@supplier.com',
        ]);

        $this->item = Item::create([
            'kode_barang' => 'SAFE-001',
            'barcode' => '89900001',
            'nama_barang' => 'Barang Safety',
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'satuan' => 'Pcs',
            'stok' => 10,
            'minimum_stok' => 5,
        ]);
    }

    public function test_category_in_use_cannot_be_deleted()
    {
        $response = $this->actingAs($this->admin)->delete("/categories/{$this->category->id}");

        $response->assertRedirect('/categories');
        $response->assertSessionHas('error');

        $this->assertModelExists($this->category);
        $this->assertModelExists($this->item);
    }

    public function test_supplier_in_use_cannot_be_deleted()
    {
        $response = $this->actingAs($this->admin)->delete("/suppliers/{$this->supplier->id}");

        $response->assertRedirect('/suppliers');
        $response->assertSessionHas('error');

        $this->assertModelExists($this->supplier);
    }

    public function test_duplicate_item_rows_are_aggregated_before_stock_validation()
    {
        // Total permintaan 12 (6+6) melebihi stok 10, meski tiap baris <= stok
        $response = $this->actingAs($this->gudang)
            ->from('/outgoing/create')
            ->post('/outgoing', [
                'tanggal' => now()->format('Y-m-d'),
                'tujuan' => 'Divisi QA',
                'keterangan' => 'Tes agregasi baris duplikat',
                'items' => json_encode([
                    ['item_id' => $this->item->id, 'jumlah' => 6],
                    ['item_id' => $this->item->id, 'jumlah' => 6],
                ]),
            ]);

        $response->assertSessionHas('error');
        $this->item->refresh();
        $this->assertEquals(10, $this->item->stok);
    }

    public function test_duplicate_item_rows_aggregate_into_single_transaction()
    {
        $response = $this->actingAs($this->gudang)
            ->from('/outgoing/create')
            ->post('/outgoing', [
                'tanggal' => now()->format('Y-m-d'),
                'tujuan' => 'Divisi QA',
                'keterangan' => 'Tes agregasi sukses',
                'items' => json_encode([
                    ['item_id' => $this->item->id, 'jumlah' => 3],
                    ['item_id' => $this->item->id, 'jumlah' => 4],
                ]),
            ]);

        $response->assertRedirect(session('receipt_url'));
        $this->item->refresh();
        $this->assertEquals(3, $this->item->stok);
    }

    public function test_login_is_rate_limited_after_too_many_attempts()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/login', [
                'username' => 'hacker_test',
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->postJson('/login', [
            'username' => 'hacker_test',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
    }
}
