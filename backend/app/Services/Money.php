<?php
namespace App\Services;
final class Money {
 public static function toToman(int $rial):int {if($rial<0||$rial%10!==0)throw new \InvalidArgumentException('Amount must be non-negative whole toman in rial.');return intdiv($rial,10);}
 public static function toRial(int $toman):int {if($toman<0||$toman>intdiv(PHP_INT_MAX,10))throw new \InvalidArgumentException('Amount out of range.');return $toman*10;}
 public static function gateway(int $rial):int {if($rial<10000)throw new \InvalidArgumentException('Amount too small.');return $rial;}// Explicit Zarinpal currency = IRR
}
