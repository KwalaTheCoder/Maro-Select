<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
class Gateway {
 protected function base():string {return config('maro.sandbox')?'https://sandbox.zarinpal.com/pg/':'https://api.zarinpal.com/pg/';}
 public function request(object $order):array {
  if(config('maro.gateway')!=='zarinpal'||!config('maro.merchant'))return ['status'=>'disabled'];
  try{$j=Http::timeout(20)->connectTimeout(8)->post($this->base().'v4/payment/request.json',['merchant_id'=>config('maro.merchant'),'amount'=>Money::gateway($order->total),'currency'=>'IRR','description'=>'MARO order '.$order->number,'callback_url'=>url('/payment/callback')])->throw()->json();
   if(($j['data']['code']??0)!==100||!is_string($j['data']['authority']??null))return ['status'=>'failed'];
   return ['status'=>'pending','authority'=>$j['data']['authority'],'redirect'=>(config('maro.sandbox')?'https://sandbox.zarinpal.com/pg/StartPay/':'https://www.zarinpal.com/pg/StartPay/').rawurlencode($j['data']['authority'])];
  }catch(\Throwable $e){return ['status'=>'unknown'];}
 }
 public function verify(object $attempt):array {
  if(config('maro.gateway')!=='zarinpal'||!config('maro.merchant')||!$attempt->authority)return ['status'=>'unknown'];
  try{$j=Http::timeout(20)->connectTimeout(8)->post($this->base().'v4/payment/verify.json',['merchant_id'=>config('maro.merchant'),'amount'=>Money::gateway($attempt->amount),'authority'=>$attempt->authority])->throw()->json();
   $code=(int)($j['data']['code']??$j['errors']['code']??0);
   if(in_array($code,[100,101],true)&&isset($j['data']['ref_id']))return ['status'=>'verified','amount'=>$attempt->amount,'authority'=>$attempt->authority,'reference'=>(string)$j['data']['ref_id']];
   // Only definitive non-payment releases inventory; other API errors require review.
   return ['status'=>in_array($code,[-51,-52],true)?'failed':'unknown'];
  }catch(\Throwable $e){return ['status'=>'unknown'];}
 }
}
