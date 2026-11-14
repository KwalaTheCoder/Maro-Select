<?php
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\Services\Store;
use App\Services\Backup;
Artisan::command('maro:admin',function(){ $email=$this->ask('Owner admin email');if(!filter_var($email,FILTER_VALIDATE_EMAIL))return $this->error('Invalid email');if(DB::table('users')->where('email',$email)->exists())return $this->error('User already exists');$password=$this->secret('Password (at least 14 characters)');if(strlen($password)<14)return $this->error('Password too short');$confirm=$this->secret('Confirm password');if(!hash_equals($password,$confirm))return $this->error('Passwords differ');DB::table('users')->insert(['name'=>'Store owner','email'=>$email,'password'=>Hash::make($password),'is_admin'=>true,'created_at'=>now(),'updated_at'=>now()]);$this->info('Admin created. No password was stored in logs.');});
Artisan::command('maro:totp {email}',function(){ $u=DB::table('users')->where('email',$this->argument('email'))->where('is_admin',true)->first();if(!$u)return $this->error('Admin not found');$secret=$this->secret('Enter your authenticator Base32 secret (32+ chars)');if(!preg_match('/^[A-Z2-7]{32,64}$/',$secret))return $this->error('Invalid Base32 secret');$code=$this->secret('Current 6-digit authenticator code');$counter=\App\Services\Totp::counter($secret,$code);if($counter===null)return $this->error('Invalid code');DB::table('users')->where('id',$u->id)->update(['totp_secret'=>Crypt::encryptString($secret),'totp_last_counter'=>$counter]);$this->info('Offline TOTP enabled. Keep an offline recovery copy with the owner.');});
Artisan::command('maro:expire',function(Store $store){$this->info('Expired: '.$store->expire());});
Artisan::command('maro:reconcile',function(Store $store){DB::table('payment_attempts')->whereIn('status',['unknown','pending','requesting'])->whereNotNull('authority')->where('updated_at','>',now()->subDays(2))->orderBy('id')->limit(50)->get()->each(fn($a)=>$store->reconcile($a->id));$this->info('Reconciliation finished.');});
Artisan::command('maro:backup',function(Backup $backup){try{$path=$backup->create();$this->info('Backup created: '.$path);}catch(\Throwable $e){logger()->error('MARO_BACKUP_FAILED',['class'=>get_class($e)]);$this->error('Backup failed; check disk, DB and cron notification.');return 1;}});
Artisan::command('maro:restore {file}',function(Backup $backup){$r=$backup->restore($this->argument('file'));$this->info(json_encode($r));});
Schedule::command('maro:expire')->everyMinute()->withoutOverlapping();
Schedule::command('maro:reconcile')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('maro:backup')->dailyAt('02:30')->withoutOverlapping();
