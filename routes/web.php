<?php

use App\Http\Controllers\ReportPdfController;
use App\Livewire\Auth\Login;
use App\Livewire\Budget\Workspace as BudgetWorkspace;
use App\Livewire\Dashboard\Overview as DashboardOverview;
use App\Livewire\Distribution\LivePreview as DistributionLivePreview;
use App\Livewire\Distribution\Workspace as DistributionWorkspace;
use App\Livewire\Ggms\TransactionLedger as GgmsTransactionLedger;
use App\Livewire\Masterlist\Index as MasterlistIndex;
use App\Livewire\Masterlist\Profile as MasterlistProfile;
use App\Livewire\Reports\Builder as ReportsBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::get('/login', Login::class)->name('login');

// Logout
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout');

// Root Redirection to Login
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Authenticated Ayuda Application Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardOverview::class)->name('dashboard');
    Route::get('/masterlist', MasterlistIndex::class)->name('masterlist');
    Route::get('/masterlist/{civilRegistryId}', MasterlistProfile::class)->name('masterlist.profile');
    Route::get('/budget', BudgetWorkspace::class)->name('budget');
    Route::get('/distribution', DistributionWorkspace::class)->name('distribution');
    Route::get('/distribution/live-preview/{project}', DistributionLivePreview::class)->name('distribution.live-preview');
    Route::get('/ggms', GgmsTransactionLedger::class)->name('ggms');
    Route::get('/reports', ReportsBuilder::class)->name('reports');
    Route::get('/reports/download-pdf', [ReportPdfController::class, 'download'])->name('reports.pdf');
});
