<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StaffDashboardController;
use App\Http\Controllers\StudentDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'home'])->name('home');

Route::get('/student/login', [AuthController::class, 'studentLogin'])->name('student.login');
Route::post('/student/login', [AuthController::class, 'authenticateStudent'])->name('student.login.post');
Route::get('/student/register', [AuthController::class, 'studentRegister'])->name('student.register');
Route::post('/student/register', [AuthController::class, 'registerStudent'])->name('student.register.post');

Route::get('/staff/login', [AuthController::class, 'staffLogin'])->name('staff.login');
Route::post('/staff/login', [AuthController::class, 'authenticateStaff'])->name('staff.login.post');
Route::get('/staff/register', [AuthController::class, 'staffRegister'])->name('staff.register');
Route::post('/staff/register', [AuthController::class, 'registerStaff'])->name('staff.register.post');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/internal/backups/run', [BackupController::class, 'runAutomatic'])->name('internal.backups.run');

Route::middleware('student')->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/dives/create', [StudentDashboardController::class, 'create'])->name('dives.create');
    Route::post('/dives', [StudentDashboardController::class, 'store'])->name('dives.store');
    Route::get('/dives/{dive}/edit', [StudentDashboardController::class, 'edit'])->name('dives.edit');
    Route::put('/dives/{dive}', [StudentDashboardController::class, 'update'])->name('dives.update');
});

Route::middleware('staff')->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/students/{student}/report', [ReportController::class, 'studentReport'])->name('students.report');
    Route::post('/reports/student', [ReportController::class, 'selected'])->name('reports.student');

    Route::middleware('admin')->group(function () {
        Route::post('/students/{student}/approve', [StaffDashboardController::class, 'approveStudent'])->name('students.approve');
        Route::delete('/students/{student}', [StaffDashboardController::class, 'destroyStudent'])->name('students.destroy');
        Route::post('/staff/{staffMember}/approve', [StaffDashboardController::class, 'approveStaff'])->name('staff.approve');
        Route::get('/backup', [BackupController::class, 'index'])->name('backup');
        Route::post('/backup', [BackupController::class, 'create'])->name('backup.create');
        Route::get('/backup/json', [BackupController::class, 'json'])->name('backup.json');
        Route::get('/backup/all.csv', [BackupController::class, 'allCsv'])->name('backup.all_csv');
        Route::get('/backup/students.csv', [BackupController::class, 'studentsCsv'])->name('backup.students_csv');
        Route::get('/backup/dives.csv', [BackupController::class, 'divesCsv'])->name('backup.dives_csv');
        Route::get('/backup/files/{filename}', [BackupController::class, 'downloadStored'])->name('backup.files');
    });
});
