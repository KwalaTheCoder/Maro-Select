<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Database\Seeders\DemoSeeder;
use App\Services\Store;
class SecurityAndCheckoutTest extends TestCase {
 use RefreshDatabase;
 protected function setUp():void {parent::setUp();$this->seed(DemoSeeder::class);}
 public function test_admin_api_is_forbidden_for_guests():void {
  $this->getJson('/api/admin/products')->assertForbidden();
  $this->getJson('/api/admin/orders')->assertForbidden();
  $this->postJson('/api/admin/products',[])->assertForbidden();
 }
 public function test_invalid_city_is_rejected_before_order_creation():void {
  $variant=DB::table('product_variants')->value('id');
  $this->postJson('/api/checkout',[
   'items'=>[['variant_id'=>$variant,'quantity'=>1]],'name'=>'مشتری آزمایشی',
   'mobile'=>'09120000000','province'=>'تهران','city'=>'اصفهان',
   'address'=>'آدرس آزمایشی برای بررسی سفارش','postal'=>'1234567890',
   'idempotency_key'=>'550e8400-e29b-41d4-a716-446655440000',
   'quote_signature'=>str_repeat('a',64)
  ])->assertUnprocessable()->assertJsonValidationErrors('city');
  $this->assertDatabaseCount('orders',0);
 }
 public function test_sale_disabled_variant_cannot_be_quoted():void {
  $variant=DB::table('product_variants')->value('id');
  DB::table('product_variants')->where('id',$variant)->update(['sale_enabled'=>false]);
  $this->expectException(\Illuminate\Validation\ValidationException::class);
  app(Store::class)->quote(['items'=>[['variant_id'=>$variant,'quantity'=>1]],'province'=>'اصفهان']);
 }
}
