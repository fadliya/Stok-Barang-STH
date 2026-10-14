<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GoodsRequest;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoodsRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Supplier $supplier;

    private Item $item;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['username' => 'request-user', 'role' => 'gudang']);
        $this->supplier = Supplier::create(['nama' => 'Supplier Utama', 'alamat' => 'Jalan Supplier 1', 'telepon' => '08123456789', 'email' => 'supplier@example.com']);
        $category = Category::create(['nama' => 'Alat Kantor']);
        $this->item = Item::create([
            'kode_barang' => 'PAPER-001', 'nama_barang' => 'Kertas A4',
            'category_id' => $category->id, 'supplier_id' => $this->supplier->id,
            'satuan' => 'Rim', 'stok' => 20, 'minimum_stok' => 5,
        ]);
        $this->actingAs($this->user);
    }

    private function payload(): array
    {
        return [
            'supplier_id' => $this->supplier->id, 'tanggal' => '2026-10-14',
            'selected' => [$this->item->id], 'quantities' => [$this->item->id => 7],
            'keterangan' => 'Mohon dikirim minggu ini.',
        ];
    }

    public function test_manual_selection_shows_all_supplier_items_including_sufficient_stock(): void
    {
        $this->get('/goods-requests/create')->assertOk()->assertDontSee('Kertas A4');
        $this->get('/goods-requests/create?supplier_id='.$this->supplier->id)
            ->assertOk()->assertSee('Kertas A4')->assertSee('Jumlah pesanan')
            ->assertDontSee('checked', false);
    }

    public function test_saves_only_checked_items_without_changing_stock_and_prints_a_stable_letter(): void
    {
        $other = $this->item->replicate();
        $other->kode_barang = 'PAPER-002';
        $other->nama_barang = 'Kertas Tidak Dipilih';
        $other->save();
        $data = $this->payload();
        $data['quantities'][$other->id] = 99;
        $this->post('/goods-requests', $data)->assertRedirect('/goods-requests/1');
        $document = GoodsRequest::firstOrFail();
        $this->assertDatabaseCount('goods_request_details', 1);
        $this->assertDatabaseHas('goods_request_details', ['item_id' => $this->item->id, 'jumlah' => 7]);
        $this->assertSame(20, $this->item->fresh()->stok);
        $this->assertDatabaseCount('incoming_transactions', 0);
        $this->item->update(['nama_barang' => 'Nama Baru']);
        $this->supplier->update(['nama' => 'Supplier Baru']);
        $this->get('/goods-requests')->assertOk()->assertSee($document->kode);
        $this->get('/goods-requests/'.$document->id)->assertOk()->assertSee('Kertas A4');
        $this->get('/goods-requests/'.$document->id.'/print')->assertOk()
            ->assertSee('Supplier Utama')->assertSee('Kertas A4')->assertSee('Jalan Supplier 1')
            ->assertSee('Mohon dikirim minggu ini.')->assertDontSee('Kertas Tidak Dipilih');
    }

    public function test_selection_and_positive_integer_quantities_are_required(): void
    {
        $data = $this->payload();
        $data['selected'] = [];
        $this->post('/goods-requests', $data)->assertSessionHasErrors('selected');
        foreach ([0, -1, '1.5', null] as $quantity) {
            $data = $this->payload();
            $data['quantities'][$this->item->id] = $quantity;
            $this->post('/goods-requests', $data)->assertSessionHasErrors('jumlah');
        }
        $data = $this->payload();
        $data['selected'][] = $this->item->id;
        $this->post('/goods-requests', $data)->assertSessionHasErrors('selected.0');
        $this->assertDatabaseCount('goods_requests', 0);
    }

    public function test_cannot_order_items_from_a_different_supplier(): void
    {
        $supplier = Supplier::create(['nama' => 'Supplier Lain', 'alamat' => 'Jalan Lain', 'telepon' => '08123456780', 'email' => 'other@example.com']);
        $data = $this->payload();
        $data['supplier_id'] = $supplier->id;
        $this->get('/goods-requests/create?supplier_id='.$supplier->id)->assertOk()->assertDontSee('Kertas A4');
        $this->post('/goods-requests', $data)->assertSessionHasErrors('supplier_id');
        $this->assertDatabaseCount('goods_requests', 0);
    }

    public function test_read_only_role_cannot_create_or_print_supplier_orders(): void
    {
        $this->post('/goods-requests', $this->payload())->assertRedirect();
        $this->user->update(['role' => 'pimpinan']);
        foreach (['/goods-requests', '/goods-requests/create', '/goods-requests/1', '/goods-requests/1/print'] as $url) {
            $this->get($url)->assertRedirect('/dashboard');
        }
        $this->post('/goods-requests', $this->payload())->assertRedirect('/dashboard');
        $this->assertDatabaseCount('goods_requests', 1);
    }
}
