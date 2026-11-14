<?php
return ['live'=>env('MARO_LIVE',false),'gateway'=>env('PAYMENT_DRIVER','disabled'),'merchant'=>env('ZARINPAL_MERCHANT_ID'),'sandbox'=>env('ZARINPAL_SANDBOX',true),'reservation_minutes'=>15,'backup_dir'=>(env('MARO_BACKUP_DIR')?:storage_path('app/private/backups')),'backup_keep'=>14];
