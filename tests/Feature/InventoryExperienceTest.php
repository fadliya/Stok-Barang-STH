<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryExperienceTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        $category = Category::create(['nama' => 'Jaringan']);
        $supplier = Supplier::create(['nama' => "Supplier O'Brien", 'alamat' => 'Jakarta', 'telepon' => '123', 'email' => 'supplier@example.test']);

        return ['kode_barang' => 'BARU-001', 'nama_barang' => "Kabel O'Brien", 'category_id' => $category->id,
            'supplier_id' => $supplier->id, 'satuan' => 'Pcs', 'stok' => 5, 'minimum_stok' => 2];
    }

    public function test_read_only_role_cannot_register_items_or_create_master_data(): void
    {
        $user = User::factory()->create(['role' => 'pimpinan']);
        $data = $this->payload();
        $data['category_id'] = 'NEW';
        $data['new_category_name'] = 'Tidak boleh dibuat';
        $this->actingAs($user)->postJson('/api/items/quick-store', $data)->assertForbidden();
        $this->assertDatabaseCount('items', 0);
        $this->assertDatabaseMissing('categories', ['nama' => 'Tidak boleh dibuat']);
        $this->assertDatabaseCount('incoming_transactions', 0);
    }

    public function test_operational_roles_can_register_initial_stock_with_a_receipt(): void
    {
        $data = $this->payload();
        foreach (['admin', 'gudang'] as $index => $role) {
            $data['kode_barang'] = 'BARU-'.$index;
            $response = $this->actingAs(User::factory()->create(['role' => $role]))->postJson('/api/items/quick-store', $data)
                ->assertOk()->assertJsonPath('success', true)->assertJsonPath('item.stok', 5);
            $this->get($response->json('receipt_url'))->assertOk()->assertSee("Kabel O'Brien")->assertSee('Thermal 58 mm')->assertSee('Total per satuan');
        }
        $this->assertDatabaseCount('incoming_transactions', 2);
    }

    public function test_invalid_master_reference_does_not_leave_partial_data(): void
    {
        $data = $this->payload();
        $data['category_id'] = 'NEW';
        $data['new_category_name'] = 'Tidak boleh tersisa';
        $data['supplier_id'] = 99999;
        $this->actingAs(User::factory()->create(['role' => 'gudang']))->postJson('/api/items/quick-store', $data)->assertUnprocessable();
        $this->assertDatabaseMissing('categories', ['nama' => 'Tidak boleh tersisa']);
        $this->assertDatabaseCount('items', 0);
    }

    public function test_inventory_search_and_filters_cover_all_pages_and_keep_url_state(): void
    {
        $data = $this->payload();
        for ($i = 0; $i < 20; $i++) {
            Item::create(array_replace($data, ['kode_barang' => 'A-'.$i, 'nama_barang' => 'A Barang '.$i, 'stok' => 20]));
        }
        $target = Item::create(array_replace($data, ['nama_barang' => "Z Kabel O'Brien", 'stok' => 0, 'barcode' => '89999888']));
        $this->actingAs(User::factory()->create(['role' => 'pimpinan']))
            ->get('/items?search='.urlencode("O'Brien").'&stock=empty&category='.$target->category_id)
            ->assertOk()->assertViewHas('items', fn ($items) => $items->total() === 1 && $items->first()->id === $target->id)
            ->assertDontSee('x-show="(search', false);
        $this->get('/items?search=89999888')->assertViewHas('items', fn ($items) => $items->total() === 1);
        $this->get('/items?stock=normal')->assertViewHas('items', fn ($items) => $items->total() === 20 && str_contains($items->nextPageUrl(), 'stock=normal'));
        $this->get('/items?search=tidak-ada')->assertOk()->assertSee('Tidak ada barang yang sesuai.');
    }

    public function test_history_search_matches_all_records_and_receipts_escape_user_text(): void
    {
        $data = $this->payload();
        $item = Item::create($data);
        $user = User::factory()->create(['role' => 'gudang']);
        $this->actingAs($user);
        for ($i = 0; $i < 17; $i++) {
            $response = $this->post('/incoming', ['supplier_id' => $item->supplier_id, 'tanggal' => '2026-10-15',
                'items' => [['item_id' => $item->id, 'jumlah' => 1]], 'keterangan' => '<script>alert(1)</script>']);
            $response->assertRedirect(session('receipt_url'));
        }
        $receiptUrl = session('receipt_url');
        $this->get('/incoming?search='.urlencode("O'Brien"))->assertOk()->assertViewHas('transactions', fn ($transactions) => $transactions->total() === 17);
        $this->get($receiptUrl)->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_invalid_quantities_and_dates_do_not_mutate_inventory(): void
    {
        $item = Item::create($this->payload());
        $this->actingAs(User::factory()->create(['role' => 'gudang']));
        foreach (['incoming', 'outgoing', 'pos/store'] as $endpoint) {
            foreach ([0, -1, 1.5, 'invalid', 2147483648] as $quantity) {
                $this->postJson('/'.$endpoint, ['transaction_type' => 'outgoing', 'supplier_id' => $item->supplier_id,
                    'tujuan' => 'IT', 'tanggal' => '2026-10-15', 'items' => json_encode([['id' => $item->id, 'jumlah' => $quantity]])])
                    ->assertUnprocessable()->assertJsonValidationErrors('items.0.jumlah');
            }
        }
        $this->postJson('/incoming', ['supplier_id' => $item->supplier_id, 'tanggal' => 'invalid',
            'items' => [['item_id' => $item->id, 'jumlah' => 1]]])->assertUnprocessable()->assertJsonValidationErrors('tanggal');
        $this->assertSame(5, $item->fresh()->stok);
        $this->assertDatabaseCount('incoming_transactions', 0);
        $this->assertDatabaseCount('outgoing_transactions', 0);
    }

    public function test_master_creation_records_initial_stock_and_edit_cannot_bypass_stock_history(): void
    {
        $data = $this->payload();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post('/items', $data)->assertRedirect(session('receipt_url'));
        $item = Item::firstOrFail();
        $this->assertSame(5, $item->stok);
        $this->assertDatabaseHas('incoming_transaction_details', ['item_id' => $item->id, 'jumlah' => 5]);
        $this->get(session('receipt_url'))->assertOk()->assertSee('Penerimaan stok awal');
        $this->put('/items/'.$item->id, array_replace($data, ['stok' => 999]))->assertSessionHasErrors('stok');
        $this->assertSame(5, $item->fresh()->stok);
        unset($data['stok']);
        $data['nama_barang'] = 'Kabel diperbarui';
        $this->put('/items/'.$item->id, $data)->assertRedirect('/items')->assertSessionHasNoErrors();
        $this->assertSame('Kabel diperbarui', $item->fresh()->nama_barang);
        $this->assertSame(5, $item->fresh()->stok);
    }
}
