<?php

return [
    'disk' => env('BACKUP_DISK', 'local'),
    'path' => env('BACKUP_PATH', 'backups'),
    'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 14),
    'automatic_token' => env('AUTO_BACKUP_TOKEN'),

    'admin' => [
        'staff_no' => env('ADMIN_STAFF_NO', '3001'),
        'password' => env('ADMIN_PASSWORD'),
        'name' => env('ADMIN_NAME', 'Sistem'),
        'surname' => env('ADMIN_SURNAME', 'Yoneticisi'),
    ],
];
