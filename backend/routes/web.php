<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoreController as Store;
Route::view('/','store');
Route::get('/config.js',function(){return response('window.MARO='.json_encode(['server'=>true,'csrf'=>csrf_token()]).';',200,['Content-Type'=>'application/javascript','Cache-Control'=>'private, no-store']);});
Route::get('/media/{id}',[Store::class,'media'])->whereNumber('id');
Route::get('/payment/callback',[Store::class,'callback'])->middleware('throttle:30,1');
Route::prefix('api')->group(function(){
 Route::get('/catalog',[Store::class,'catalog']);
 Route::post('/quote',[Store::class,'quote'])->middleware('throttle:30,1');
 Route::post('/checkout',[Store::class,'checkout'])->middleware('throttle:10,1');
 Route::post('/retry',function(\Illuminate\Http\Request $r,\App\Services\Store $s){$r->validate(['token'=>'required|regex:/^[a-f0-9]{64}$/']);abort_if(!config('maro.live')&&config('maro.gateway')==='zarinpal'&&!config('maro.sandbox'),403);return $s->retry($r->input('token'));})->middleware('throttle:5,1');
 Route::post('/track',[Store::class,'track'])->middleware('throttle:10,1');
 Route::post('/admin/login',[Store::class,'login'])->middleware('throttle:5,1');
 Route::prefix('admin')->middleware(\App\Http\Middleware\AdminOnly::class)->group(function(){
 Route::post('/logout',[Store::class,'logout']);Route::get('/products',[Store::class,'products']);Route::post('/products',[Store::class,'saveProduct']);Route::post('/products/{id}',[Store::class,'saveProduct'])->whereNumber('id');Route::put('/settings',[Store::class,'settings']);Route::get('/stats',[Store::class,'stats']);Route::get('/orders',[Store::class,'orders']);Route::get('/orders/{id}',[Store::class,'order'])->whereNumber('id');Route::post('/orders/{id}/shipment',[Store::class,'shipment'])->whereNumber('id');
 });
});
Route::get('/products/{slug}',function(string $slug,\App\Services\Store $store){$p=collect($store->catalog())->firstWhere('slug',$slug);abort_unless($p,404);return view('product-seo',['p'=>$p]);});
Route::get('/sitemap.xml',function(){ $rows=\Illuminate\Support\Facades\DB::table('products')->where('status','published')->where('demo',false)->get();$xml='<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url><loc>'.e(url('/')).'</loc></url>';foreach($rows as $p)$xml.='<url><loc>'.e(url('/products/'.$p->slug)).'</loc></url>';return response($xml.'</urlset>',200,['Content-Type'=>'application/xml']);});
