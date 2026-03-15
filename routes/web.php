<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Livewire\Doctor\DoctorSettings;
use App\Livewire\Admin\IncomeStatistics;
use App\Livewire\Admin\AdminDashboard; // Added for the new admin dashboard route

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
    });

    // Doctor Dashboard (with subscription guard)
    Route::middleware(['can:doctor'])->group(function () {
        Route::view('doctor', 'dashboard.doctor')
            ->middleware('subscription.active')
            ->name('doctor.dashboard');
        
        Route::get('doctor/settings', DoctorSettings::class)->name('doctor.settings');
        Route::get('doctor/statistics', IncomeStatistics::class)->name('doctor.statistics');
    });

    // Secretary Dashboard
    Route::middleware(['can:secretary'])->group(function () {
        Route::view('secretary', 'dashboard.secretary')->name('secretary.dashboard');
    });

    Route::view('subscription-inactive', 'subscription.inactive')->name('subscription.inactive');

    // Shared: Management Modules
    Volt::route('patients', 'shared.patients-list')->name('patients.index');
    Volt::route('appointments', 'shared.appointments-list')->name('appointments.index');

    // Shared: Patient Profile
    Route::get('patients/{patient}', \App\Livewire\Shared\PatientProfile::class)
        ->middleware(['can:doctor,secretary'])
        ->name('patients.show');

    // Doctor Only: Visit Form
    Route::get('appointments/{appointment}/visit', \App\Livewire\Doctor\VisitForm::class)
        ->middleware(['can:doctor', 'subscription.active'])
        ->name('appointments.visit');

    Route::view('profile', 'profile')->name('profile');

    Route::get('lang/{locale}', function ($locale) {
        if (in_array($locale, ['ar', 'en'])) {
            session()->put('locale', $locale);
        }
        return redirect()->back();
    })->name('lang.switch');
});

require __DIR__.'/auth.php';
