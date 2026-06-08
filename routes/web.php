<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\CompetitionController as AdminCompetitionController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FundRequestController;
use App\Http\Controllers\MentorController;
use App\Http\Controllers\MentorRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResultReportController;
use App\Http\Controllers\SopController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? auth()->user()->dashboardRoute() : 'login'));

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'redirect'])->name('dashboard');

    Route::get('/lomba', [CompetitionController::class, 'index'])->name('competitions.index');
    Route::get('/lomba/{competition}', [CompetitionController::class, 'show'])->name('competitions.show');
    Route::post('/lomba/{competition}/daftar', [CompetitionController::class, 'register'])
        ->middleware(['role:mahasiswa', 'throttle:10,1'])
        ->name('competitions.register');

    Route::get('/lomba-saya', [CompetitionController::class, 'my'])
        ->middleware('role:mahasiswa')
        ->name('registrations.index');
    Route::get('/lomba-saya/{registration}/lapor-hasil', [ResultReportController::class, 'create'])
        ->middleware('role:mahasiswa')
        ->name('registrations.results.create');
    Route::get('/lomba-saya/{registration}/hasil', [ResultReportController::class, 'show'])
        ->middleware('role:mahasiswa')
        ->name('registrations.results.show');
    Route::post('/lomba-saya/{registration}/lapor-hasil', [ResultReportController::class, 'store'])
        ->middleware(['role:mahasiswa', 'throttle:10,1'])
        ->name('registrations.results.store');
    Route::patch('/lomba-saya/{registration}/upload-bukti', [CompetitionController::class, 'reuploadProof'])
        ->middleware(['role:mahasiswa', 'throttle:10,1'])
        ->name('registrations.reupload-proof');

    Route::get('/mentor', [MentorController::class, 'index'])->name('mentors.index');
    Route::get('/mentor/{mentor}', [MentorController::class, 'show'])->name('mentors.show');
    Route::post('/pengajuan-mentor', [MentorRequestController::class, 'store'])
        ->middleware(['role:mahasiswa', 'throttle:10,1'])
        ->name('mentor-requests.store');

    Route::get('/pengajuan-dana/create', [FundRequestController::class, 'create'])
        ->middleware('role:mahasiswa')
        ->name('fund-requests.create');
    Route::post('/pengajuan-dana', [FundRequestController::class, 'store'])
        ->middleware(['role:mahasiswa', 'throttle:10,1'])
        ->name('fund-requests.store');

    Route::get('/sop', [SopController::class, 'index'])->name('sop.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->middleware('throttle:5,1')
        ->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
        Route::resource('competitions', AdminCompetitionController::class);
        Route::get('/registrations', [ReviewController::class, 'registrations'])->name('registrations.index');
        Route::patch('/registrations/{registration}', [ReviewController::class, 'updateRegistration'])
            ->middleware('throttle:30,1')
            ->name('registrations.update');
        Route::get('/mentor-requests', [ReviewController::class, 'mentorRequests'])->name('mentor-requests.index');
        Route::patch('/mentor-requests/{mentorRequest}', [ReviewController::class, 'updateMentorRequest'])
            ->middleware('throttle:30,1')
            ->name('mentor-requests.update');
        Route::get('/fund-requests', [ReviewController::class, 'fundRequests'])->name('fund-requests.index');
        Route::patch('/fund-requests/{fundRequest}', [ReviewController::class, 'updateFundRequest'])
            ->middleware('throttle:30,1')
            ->name('fund-requests.update');
    });

Route::middleware(['auth', 'role:pimpinan'])
    ->prefix('pimpinan')
    ->name('pimpinan.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'pimpinan'])->name('dashboard');
    });

Route::middleware(['auth', 'role:mentor'])
    ->prefix('mentor-area')
    ->name('mentor.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'mentor'])->name('dashboard');
    });

require __DIR__.'/auth.php';
