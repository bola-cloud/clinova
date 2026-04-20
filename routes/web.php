<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Livewire\Doctor\DoctorSettings;
use App\Http\Controllers\FileController;
use App\Livewire\Admin\IncomeStatistics;
use App\Livewire\Admin\AdminDashboard;
use App\Livewire\Admin\SystemRevenue;
use App\Livewire\Admin\DoctorSubscriptionManager;
use App\Livewire\Doctor\StaffManagement;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    // Role-based dashboard redirector
    Route::get('dashboard', function () {
        $user = auth()->user();
        if ($user->isAdmin()) return redirect()->route('admin.dashboard');
        if ($user->isDoctor()) return redirect()->route('doctor.dashboard');
        if ($user->isSecretary()) return redirect()->route('secretary.dashboard');
        return redirect('/');
    })->name('dashboard');

    // Admin Dashboard
    Route::middleware(['can:admin'])->group(function () {
        Route::view('admin', 'dashboard.admin')->name('admin.dashboard');
        Route::get('admin/statistics', IncomeStatistics::class)->name('admin.statistics');
        Route::get('admin/revenue', SystemRevenue::class)->name('admin.revenue');
        Route::get('admin/doctors/{doctor}/subscriptions', DoctorSubscriptionManager::class)->name('admin.doctor.subscriptions');
        Route::view('admin/patients', 'dashboard.admin-patients')->name('admin.patients');
        Route::view('admin/specialties', 'dashboard.admin-specialties')->name('admin.specialties');
        Route::view('admin/settings', 'dashboard.admin-settings')->name('admin.settings');
    });

    // Doctor Dashboard (with subscription guard)
    Route::middleware(['can:doctor', 'subscription.active'])->group(function () {
        Route::view('doctor', 'dashboard.doctor')->name('doctor.dashboard');
        Route::get('doctor/settings', DoctorSettings::class)->name('doctor.settings');
        Route::get('doctor/staff', StaffManagement::class)->name('doctor.staff');
        Route::get('doctor/statistics', IncomeStatistics::class)->name('doctor.statistics');
    });

    // Secretary Dashboard
    Route::middleware(['can:secretary', 'subscription.active'])->group(function () {
        Route::view('secretary', 'dashboard.secretary')->name('secretary.dashboard');
        Route::get('secretary/settings', \App\Livewire\Secretary\SecretarySettings::class)->name('secretary.settings');
    });

    Route::view('subscription-inactive', 'subscription.inactive')->name('subscription.inactive');

    // Shared: Management Modules & Patient Profile (with subscription guard)
    Route::middleware(['subscription.active'])->group(function () {
        Volt::route('patients', 'shared.patients-list')->name('patients.index');
        Volt::route('appointments', 'shared.appointments-list')->name('appointments.index');

        Route::get('patients/{patient}', \App\Livewire\Shared\PatientProfile::class)
            ->middleware(['can:doctor,secretary'])
            ->name('patients.show');

        // Doctor Only: Visit Form
        Route::get('appointments/{appointment}/visit', \App\Livewire\Doctor\VisitForm::class)
            ->middleware(['can:doctor'])
            ->name('appointments.visit');

        // File Serving with decompression
        Route::get('files/serve/{path}', [FileController::class, 'serve'])->where('path', '.*')->name('files.serve');
        Route::get('files/p/{id}', [FileController::class, 'servePatientFile'])->name('files.patient');
        Route::get('files/v/{id}', [FileController::class, 'serveVisitFile'])->name('files.visit');
    });

    Route::view('profile', 'profile')->name('profile');
});

Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['ar', 'en'])) {
        session()->put('locale', $locale);
    }
    return redirect()->back();
})->name('lang.switch');

require __DIR__.'/auth.php';
