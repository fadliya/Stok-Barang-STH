<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GoodsRequest;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
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
        $this->get('/goods-requests/'.$document->id.'/print')->assertForbidden();
        $leader = User::factory()->create(['role' => 'pimpinan']);
        $this->actingAs($leader)->post('/goods-requests/'.$document->id.'/approve')->assertRedirect();
        $this->actingAs($this->user);
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

    public function test_leader_can_view_and_approve_but_cannot_create_or_receive(): void
    {
        $this->post('/goods-requests', $this->payload())->assertRedirect();
        $leader = User::factory()->create(['role' => 'pimpinan']);
        $this->actingAs($leader)->get('/goods-requests')->assertOk();
        $this->get('/goods-requests/1')->assertOk()->assertSee('ACC Pemesanan');
        $this->get('/goods-requests/create')->assertRedirect('/dashboard');
        $this->post('/goods-requests', $this->payload())->assertRedirect('/dashboard');
        $this->post('/goods-requests/1/receive', ['checked' => [1]])->assertRedirect('/dashboard');
        $this->post('/goods-requests/1/approve')->assertRedirect();
        $this->get('/goods-requests/1/print')->assertOk()->assertSee($leader->name)->assertSee('Tidak memerlukan tanda tangan manual');
        $this->post('/goods-requests/1/approve')->assertStatus(409);
        $this->assertSame(20, $this->item->fresh()->stok);
    }

    public function test_checklist_receives_once_only_after_approval_and_full_inspection(): void
    {
        $other = $this->item->replicate();
        $other->kode_barang = 'PAPER-002';
        $other->save();
        $payload = $this->payload();
        $payload['selected'][] = $other->id;
        $payload['quantities'][$other->id] = 3;
        $this->post('/goods-requests', $payload)->assertRedirect();
        $document = GoodsRequest::firstOrFail();
        $checked = ['checked' => $document->details->pluck('id')->all()];
        $this->post('/goods-requests/1/receive', $checked)->assertSessionHasErrors('checked');
        $leader = User::factory()->create(['role' => 'pimpinan']);
        $this->actingAs($leader)->post('/goods-requests/1/approve')->assertRedirect();
        $this->post('/goods-requests/1/receive', $checked)->assertRedirect('/dashboard');
        $this->actingAs($this->user)->get('/incoming')->assertOk()->assertSee($document->kode)->assertSee('Pending');
        $this->assertSame(20, $this->item->fresh()->stok);
        $this->assertDatabaseCount('incoming_transactions', 0);
        $this->post('/goods-requests/1/receive', [])->assertSessionHasErrors('checked');
        $this->post('/goods-requests/1/receive', ['checked' => [$checked['checked'][0]]])->assertSessionHasErrors('checked');
        $this->post('/goods-requests/1/receive', ['checked' => [99999]])->assertSessionHasErrors('checked');
        $this->assertSame(20, $this->item->fresh()->stok);
        $this->post('/goods-requests/1/receive', $checked)->assertRedirect('/incoming/1/receipt');
        $this->assertSame(27, $this->item->fresh()->stok);
        $this->assertSame(23, $other->fresh()->stok);
        $this->assertSame('received', $document->fresh()->status);
        $this->assertNotNull($document->fresh()->received_at);
        $this->post('/goods-requests/1/receive', $checked)->assertSessionHasErrors('checked');
        $this->assertDatabaseCount('incoming_transactions', 1);
        $this->assertSame(27, $this->item->fresh()->stok);
        $this->delete('/incoming/1')->assertSessionHas('error');
        $this->assertDatabaseCount('incoming_transactions', 1);
    }

    public function test_public_verification_requires_signed_link_and_actual_approval(): void
    {
        $this->post('/goods-requests', $this->payload())->assertRedirect();
        $document = GoodsRequest::firstOrFail();
        $url = URL::signedRoute('goods-requests.verify', $document);
        $this->get($url)->assertNotFound();
        $leader = User::factory()->create(['role' => 'pimpinan']);
        $this->actingAs($leader)->post('/goods-requests/1/approve')->assertRedirect();
        auth()->logout();
        $this->get($url)->assertOk()->assertSee($leader->name)->assertSee('Kertas A4')->assertSee('Tanggal ACC');
        $this->get('/persetujuan-pemesanan/1')->assertForbidden();
        $this->get($url.'&changed=1')->assertForbidden();
        $this->assertSame(20, $this->item->fresh()->stok);
    }
}
