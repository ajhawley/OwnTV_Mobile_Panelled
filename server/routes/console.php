<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('panel:about', function () {
    $this->info('OwnTV Custom management panel');
    $this->line('Configuration endpoint: '.url('/panel/api/v1/bootstrap'));
})->purpose('Display basic OwnTV Custom panel information');
