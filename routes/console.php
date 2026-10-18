<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('deploy:admin', function () {
    if (User::exists()) {
        $this->info('Pengguna sudah ada; tidak membuat atau mengubah akun.');

        return 0;
    }

    $data = validator([
        'email' => config('deployment.admin_email'),
        'password' => config('deployment.admin_password'),
    ], ['email' => 'required|email', 'password' => 'required|string|min:12'])->validate();

    User::create([
        'name' => 'Administrator', 'username' => 'admin',
        'email' => $data['email'], 'password' => $data['password'], 'role' => 'admin',
    ]);
    $this->info('Akun admin pertama dibuat.');

    return 0;
})->purpose('Membuat admin awal tanpa data contoh atau mengganti akun yang sudah ada');
