<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:init', function () {
    $this->info('Initializing application...');
    try {
        $this->info('Running migrate:fresh');
        $this->call('migrate:fresh', [
            '--force' => true,
        ]);

        $this->info('Running db:seed');
        $this->call('db:seed', [
            '--force' => true,
        ]);

        $this->info('Initialization complete.');
    } catch (\Throwable $e) {
        $this->error('Initialization failed: ' . $e->getMessage());
        report($e);
        return 1;
    }
    return 0;
})->purpose('Reset database and seed initial data');
