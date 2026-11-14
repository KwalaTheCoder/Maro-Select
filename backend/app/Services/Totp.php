<?php
namespace App\Services;
final class Totp {
 public static function counter(string $secret,string $code,?int $last=null):?int {if(!preg_match('/^\d{6}$/',$code))return null;$bits='';foreach(str_split(strtoupper($secret))as $c){$n=strpos('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567',$c);if($n===false)return null;$bits.=str_pad(decbin($n),5,'0',STR_PAD_LEFT);}$key='';foreach(str_split($bits,8)as $b)if(strlen($b)===8)$key.=chr(bindec($b));$now=intdiv(time(),30);for($t=$now-1;$t<=$now+1;$t++){if($last!==null&&$t<=$last)continue;$h=hash_hmac('sha1',pack('N2',0,$t),$key,true);$o=ord($h[19])&15;$v=(unpack('N',substr($h,$o,4))[1]&0x7fffffff)%1000000;if(hash_equals(str_pad((string)$v,6,'0',STR_PAD_LEFT),$code))return $t;}return null;}
}
