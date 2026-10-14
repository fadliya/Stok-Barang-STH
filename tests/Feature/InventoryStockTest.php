<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryStockTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $gudang;

    protected User $pimpinan;

    protected Category $category;

    protected Supplier $supplier;

    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'username' => 'admin_test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $this->gudang = User::create([
            'name' => 'Gudang User',
            'username' => 'gudang_test',
            'email' => 'gudang@test.com',
            'password' => bcrypt('password123'),
            'role' => 'gudang',
        ]);

        $this->pimpinan = User::create([
            'name' => 'Pimpinan User',
            'username' => 'pimpinan_test',
            'email' => 'pimpinan@test.com',
            'password' => bcrypt('password123'),
            'role' => 'pimpinan',
        ]);

        $this->category = Category::create(['nama' => 'Elektronik']);

        $this->supplier = Supplier::create([
            'nama' => 'PT Supplier Utama',
            'alamat' => 'Jl. Test No. 1',
            'telepon' => '08123456789',
            'email' => 'supplier@test.com',
        ]);

        $this->item = Item::create([
            'kode_barang' => 'TEST-001',
            'barcode' => '89912345',
            'nama_barang' => 'Barang Testing',
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'satuan' => 'Pcs',
            'stok' => 10,
            'minimum_stok' => 5,
        ]);
    }

    public function test_stock_opname_records_difference_and_updates_stock()
    {
        $this->actingAs($this->gudang)->post('/stock-opname', [
            'item_id' => $this->item->id,
            'stok_sesudah' => 0,
            'tanggal' => '2026-10-14',
            'alasan' => 'Barang rusak',
        ])->assertRedirect('/stock-opname');

        $this->assertSame(0, $this->item->fresh()->stok);
        $this->assertDatabaseHas('stock_adjustments', [
            'item_id' => $this->item->id,
            'user_id' => $this->gudang->id,
            'stok_sebelumnya' => 10,
            'stok_sesudah' => 0,
            'selisih' => 10,
            'tipe' => 'kurang',
        ]);
        $this->get('/stock-opname')->assertOk()->assertSee('Barang rusak');
    }

    public function test_stock_opname_rejects_negative_stock_and_read_only_role()
    {
        $data = ['item_id' => $this->item->id, 'stok_sesudah' => -1, 'tanggal' => '2026-10-14', 'alasan' => 'Opname'];
        $this->actingAs($this->gudang)->post('/stock-opname', $data)->assertSessionHasErrors('stok_sesudah');
        $this->actingAs($this->pimpinan)->post('/stock-opname', array_replace($data, ['stok_sesudah' => 5]))->assertRedirect('/dashboard');
        $this->assertSame(10, $this->item->fresh()->stok);
        $this->assertDatabaseCount('stock_adjustments', 0);
    }

    public function test_stock_opname_can_increase_stock_and_record_an_unchanged_count()
    {
        foreach ([15, 15] as $stock) {
            $this->actingAs($this->admin)->post('/stock-opname', [
                'item_id' => $this->item->id,
                'stok_sesudah' => $stock,
                'tanggal' => '2026-10-14',
                'alasan' => 'Penghitungan fisik',
            ])->assertRedirect('/stock-opname');
        }
        $this->assertSame(15, $this->item->fresh()->stok);
        $this->assertDatabaseCount('stock_adjustments', 2);
        $this->assertDatabaseHas('stock_adjustments', ['stok_sebelumnya' => 10, 'stok_sesudah' => 15, 'selisih' => 5, 'tipe' => 'tambah']);
        $this->assertDatabaseHas('stock_adjustments', ['stok_sebelumnya' => 15, 'stok_sesudah' => 15, 'selisih' => 0]);
    }

    public function test_receipts_are_available_for_manual_and_pos_transactions()
    {
        foreach (['incoming', 'outgoing'] as $type) {
            foreach ([$type, 'pos/store'] as $endpoint) {
                $response = $this->actingAs($this->gudang)->post('/'.$endpoint, [
                    'transaction_type' => $type,
                    'supplier_id' => $this->supplier->id,
                    'tujuan' => 'Divisi IT',
                    'tanggal' => '2026-10-14',
                    'items' => json_encode([['item_id' => $this->item->id, 'jumlah' => 2]]),
                ]);
                $response->assertRedirect('/'.$type)->assertSessionHas('receipt_url');
                $receiptUrl = session('receipt_url');
                $this->get($receiptUrl)->assertOk()->assertSee('Barang Testing')->assertSee('Cetak / Simpan PDF');
                $this->actingAs($this->pimpinan)->get($receiptUrl)->assertRedirect('/dashboard');
            }
        }
        $this->actingAs($this->gudang)->get('/incoming/999999/receipt')->assertNotFound();
    }

    public function test_user_can_login_with_username_and_password()
    {
        $response = $this->post('/login', [
            'username' => 'admin_test',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_gudang_role_cannot_access_user_management()
    {
        $response = $this->actingAs($this->gudang)->get('/users');
        $response->assertRedirect('/dashboard');
    }

    public function test_barcode_scan_api_returns_item_data()
    {
        $response = $this->actingAs($this->gudang)->getJson('/api/items/scan?barcode=89912345');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'item' => [
                    'barcode' => '89912345',
                    'nama_barang' => 'Barang Testing',
                ],
            ]);
    }

    public function test_barcode_scan_api_returns_404_when_not_found()
    {
        $response = $this->actingAs($this->gudang)->getJson('/api/items/scan?barcode=99999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'barcode' => '99999999',
            ]);
    }

    public function test_multi_item_incoming_transaction_increases_stock()
    {
        $initialStock = $this->item->stok; // 10

        $response = $this->actingAs($this->gudang)
            ->from('/incoming/create')
            ->post('/incoming', [
                'tanggal' => now()->format('Y-m-d'),
                'supplier_id' => $this->supplier->id,
                'keterangan' => 'Penerimaan stok baru POS barcode',
                'items' => json_encode([
                    ['item_id' => $this->item->id, 'jumlah' => 15],
                ]),
            ]);

        $response->assertRedirect('/incoming');
        $this->item->refresh();

        $this->assertEquals(25, $this->item->stok);
    }

    public function test_multi_item_outgoing_transaction_reduces_stock()
    {
        $initialStock = $this->item->stok; // 10

        $response = $this->actingAs($this->gudang)
            ->from('/outgoing/create')
            ->post('/outgoing', [
                'tanggal' => now()->format('Y-m-d'),
                'tujuan' => 'Divisi HRD',
                'keterangan' => 'Pengeluaran alat kantor POS barcode',
                'items' => json_encode([
                    ['item_id' => $this->item->id, 'jumlah' => 4],
                ]),
            ]);

        $response->assertRedirect('/outgoing');
        $this->item->refresh();

        $this->assertEquals(6, $this->item->stok);
    }

    public function test_outgoing_item_fails_when_stock_insufficient()
    {
        $initialStock = $this->item->stok; // 10

        $response = $this->actingAs($this->gudang)
            ->from('/outgoing/create')
            ->post('/outgoing', [
                'tanggal' => now()->format('Y-m-d'),
                'tujuan' => 'Divisi IT',
                'keterangan' => 'Pengeluaran berlebih',
                'items' => json_encode([
                    ['item_id' => $this->item->id, 'jumlah' => 50], // Melebihi stok 10
                ]),
            ]);

        $response->assertSessionHas('error');
        $this->item->refresh();

        $this->assertEquals(10, $this->item->stok); // Stok tidak berubah
    }
}
