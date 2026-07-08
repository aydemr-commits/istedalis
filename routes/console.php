<?php

use Illuminate\Support\Facades\Artisan;
use App\Models\Staff;
use App\Services\BackupService;

Artisan::command('about-dalis', function () {
    $this->info('istedalis hazir.');
});

Artisan::command('admin:ensure {--force : Uretim ortaminda admin hesabi olusturmayi onaylar}', function () {
    if (app()->environment('production') && ! $this->option('force')) {
        $this->error('Uretim ortaminda calistirmak icin --force kullanin.');

        return 1;
    }

    $password = config('backup.admin.password');

    if (app()->environment('production') && blank($password)) {
        $this->error('ADMIN_PASSWORD ortam degiskeni zorunludur.');

        return 1;
    }

    $staff = Staff::updateOrCreate(
        ['staff_no' => config('backup.admin.staff_no', '3001')],
        [
            'password' => $password ?: '',
            'name' => config('backup.admin.name', 'Sistem'),
            'surname' => config('backup.admin.surname', 'Yoneticisi'),
            'role_name' => 'admin',
        ],
    );

    $this->info("Admin hesabi hazir: {$staff->staff_no}");

    return 0;
});

Artisan::command('backup:students {--type=scheduled : Yedek dosyasi etiketi}', function (BackupService $backups) {
    $backup = $backups->createJsonFile((string) $this->option('type'));
    $deleted = $backups->deleteExpiredBackups();

    $this->info("Yedek olusturuldu: {$backup['filename']}");
    $this->info("SHA-256: {$backup['checksum']}");
    $this->info("Temizlenen eski yedek: {$deleted}");

    return 0;
});
