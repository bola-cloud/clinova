<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Patient;
use App\Models\User;
use App\Services\PatientService;
use App\Services\AppointmentService;
use Carbon\Carbon;

new class extends Component
{
    use WithPagination;

    public $search = '';
    public $showAddPatient = false;
    public $showAdvancedFilters = false;
    
    // Advanced Filters
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $filterHasFiles = '';
    public $filterMinVisits = '';
    
    // New patient fields
    public $name, $phone, $age, $weight, $address, $gender;

    // Rapid booking fields
    public $bookingPatientId = null;
    public $bookingDoctorId = '';
    public $bookingDate = '';
    public $bookingType = 'checkup';

    public function mount()
    {
        $this->bookingDate = now()->format('Y-m-d');
    }

    public function createPatient()
    {
        $this->validate([
            'name' => 'required|min:3',
            'phone' => 'required|numeric',
        ]);

        $doctorId = auth()->user()->isDoctor() ? auth()->id() : auth()->user()->doctor_id;

        app(PatientService::class)->createPatient([
            'name' => $this->name,
            'phone' => $this->phone,
            'age' => $this->age,
            'weight' => $this->weight,
            'address' => $this->address,
            'doctor_id' => $doctorId,
        ]);

        $this->reset(['name', 'phone', 'age', 'weight', 'address', 'showAddPatient']);
        session()->flash('success', __('Patient added successfully.'));
    }

    public function openBooking($patientId)
    {
        $this->bookingPatientId = $patientId;
        
        // Auto-select doctor: if doctor, select self. If secretary/admin and only 1 doctor, select that one.
        if (auth()->user()->role === 'doctor') {
            $this->bookingDoctorId = auth()->id();
        } else {
            $doctors = User::where('role', 'doctor')->get();
            $this->bookingDoctorId = $doctors->count() === 1 ? $doctors->first()->id : '';
        }

        $this->bookingDate = now()->format('Y-m-d');
        $this->bookingType = 'checkup';
    }

    public function cancelBooking()
    {
        $this->bookingPatientId = null;
        $this->bookingDoctorId = '';
        $this->bookingDate = now()->format('Y-m-d');
        $this->bookingType = 'checkup';
    }

    public function confirmBooking()
    {
        $this->validate([
            'bookingDoctorId' => 'required|exists:users,id',
            'bookingDate' => 'required|date',
            'bookingType' => 'required|in:checkup,follow_up',
        ]);

        app(AppointmentService::class)->bookAppointment([
            'patient_id' => $this->bookingPatientId,
            'doctor_id' => $this->bookingDoctorId,
            'scheduled_at' => Carbon::parse($this->bookingDate)->startOfDay(),
            'status' => 'pending',
            'type' => $this->bookingType,
        ]);

        $this->cancelBooking();
        session()->flash('success', __('Patient appointment booked successfully.'));
    }
    
    public function exportCSV()
    {
        $patients = Patient::withCount(['appointments', 'visits', 'files'])
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                      ->orWhere('phone', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterDateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn($q) => $q->whereDate('created_at', '<=', $this->filterDateTo))
            ->when($this->filterHasFiles === 'yes', fn($q) => $q->has('files'))
            ->when($this->filterHasFiles === 'no', fn($q) => $q->doesntHave('files'))
            ->when($this->filterMinVisits !== '', fn($q) => $q->having('visits_count', '>=', (int)$this->filterMinVisits))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->streamDownload(function () use ($patients) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [
                __('ID'), 
                __('Name'), 
                __('Phone'), 
                __('Age'), 
                __('Visits'), 
                __('Files'), 
                __('Registration Date'), 
                __('Medical Tags')
            ]);

            foreach ($patients as $p) {
                fputcsv($file, [
                    $p->id,
                    $p->name,
                    $p->phone,
                    $p->age ?? '-',
                    $p->visits_count,
                    $p->files_count,
                    $p->created_at->format('Y-m-d'),
                    is_array($p->tags) ? implode(', ', $p->tags) : ''
                ]);
            }
            fclose($file);
        }, 'patients_archive_' . now()->format('Ymd_His') . '.csv');
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterDateFrom() { $this->resetPage(); }
    public function updatingFilterDateTo() { $this->resetPage(); }
    public function updatingFilterHasFiles() { $this->resetPage(); }
    public function updatingFilterMinVisits() { $this->resetPage(); }

    public function render(): mixed
    {
        $doctorId = auth()->user()->role === 'doctor' ? auth()->id() : auth()->user()->doctor_id;

        $basePatientQuery = Patient::where('doctor_id', $doctorId);

        $patients = (clone $basePatientQuery)->withCount(['appointments', 'visits', 'files'])
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                      ->orWhere('phone', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterDateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn($q) => $q->whereDate('created_at', '<=', $this->filterDateTo))
            ->when($this->filterHasFiles === 'yes', fn($q) => $q->has('files'))
            ->when($this->filterHasFiles === 'no', fn($q) => $q->doesntHave('files'))
            ->when($this->filterMinVisits !== '', fn($q) => $q->having('visits_count', '>=', (int)$this->filterMinVisits))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total' => (clone $basePatientQuery)->count(),
            'this_month' => (clone $basePatientQuery)->whereMonth('created_at', now()->month)->count(),
            'with_files' => (clone $basePatientQuery)->has('files')->count(),
            'total_visits' => \App\Models\Visit::when(auth()->user()->role === 'doctor', fn($q) => $q->where('doctor_id', auth()->id()))->count(),
        ];

        return view('livewire.shared.patients-list', [
            'patients' => $patients,
            'stats' => $stats,
            'doctors' => auth()->user()->role === 'doctor' 
                ? User::where('id', auth()->id())->get() 
                : User::where('role', 'doctor')->get()
        ])->layout('layouts.clinic', ['title' => __('Patients Archive')]);
    }
};
?>

<div class="space-y-6" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 animate-fade-in-up">
        <div class="bg-gradient-to-br from-white to-purple-50/30 p-6 rounded-3xl border border-purple-100 shadow-sm hover:shadow-md transition-all flex items-center gap-4 group">
            <div class="w-14 h-14 bg-purple-100/80 text-purple-600 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300 shadow-inner">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <p class="text-[11px] md:text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">{{ __('Total Patients') }}</p>
                <h3 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">{{ number_format($stats['total']) }}</h3>
            </div>
        </div>
        <div class="bg-gradient-to-br from-white to-blue-50/30 p-6 rounded-3xl border border-blue-100 shadow-sm hover:shadow-md transition-all flex items-center gap-4 group">
            <div class="w-14 h-14 bg-blue-100/80 text-blue-600 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300 shadow-inner">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <p class="text-[11px] md:text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">{{ __('New This Month') }}</p>
                <h3 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">{{ number_format($stats['this_month']) }}</h3>
            </div>
        </div>
        <div class="bg-gradient-to-br from-white to-rose-50/30 p-6 rounded-3xl border border-rose-100 shadow-sm hover:shadow-md transition-all flex items-center gap-4 group">
            <div class="w-14 h-14 bg-rose-100/80 text-rose-600 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300 shadow-inner">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div>
                <p class="text-[11px] md:text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">{{ __('With Medical Files') }}</p>
                <h3 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">{{ number_format($stats['with_files']) }}</h3>
            </div>
        </div>
        <div class="bg-gradient-to-br from-white to-emerald-50/30 p-6 rounded-3xl border border-emerald-100 shadow-sm hover:shadow-md transition-all flex items-center gap-4 group">
            <div class="w-14 h-14 bg-emerald-100/80 text-emerald-600 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300 shadow-inner">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
            <div>
                <p class="text-[11px] md:text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">{{ __('Total Visits') }}</p>
                <h3 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">{{ number_format($stats['total_visits']) }}</h3>
            </div>
        </div>
    </div>

    <!-- Header & Actions -->
    <div class="flex flex-col lg:flex-row items-center justify-between gap-4 bg-white p-4 md:p-6 rounded-3xl border border-gray-100 shadow-sm">
        <div class="relative w-full lg:w-[400px]">
            <div class="absolute inset-y-0 {{ app()->getLocale() === 'ar' ? 'right-0 pr-4' : 'left-0 pl-4' }} flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('Search by patient name or phone number...') }}" 
                   class="w-full bg-slate-50 border-gray-200 text-gray-800 text-sm rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white block {{ app()->getLocale() === 'ar' ? 'pr-11 pl-4' : 'pl-11 pr-4' }} py-3 transition-colors shadow-inner">
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3 w-full lg:w-auto">
            <button wire:click="$toggle('showAdvancedFilters')" class="flex-1 lg:flex-none px-4 py-3 bg-white hover:bg-gray-50 text-gray-700 font-bold rounded-xl border border-gray-200 transition-all flex items-center justify-center gap-2 text-sm shadow-sm hover:shadow">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                {{ __('Filters') }}
            </button>
            <button wire:click="exportCSV" class="flex-1 lg:flex-none px-4 py-3 bg-white hover:bg-emerald-50 hover:border-emerald-200 text-emerald-700 font-bold rounded-xl border border-gray-200 transition-all flex items-center justify-center gap-2 text-sm shadow-sm hover:shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                {{ __('Export CSV') }}
            </button>
            <button wire:click="$toggle('showAddPatient')" class="w-full lg:w-auto px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-purple-200 transition-all flex items-center justify-center gap-2 text-sm hover:-translate-y-0.5 group">
                <svg class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span class="inline-block">{{ $showAddPatient ? __('Close Form') : __('New Patient') }}</span>
            </button>
        </div>
    </div>

    <!-- Advanced Filters Panel -->
    @if($showAdvancedFilters)
    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm animate-fade-in-down">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">{{ __('Registration Date From') }}</label>
                <input type="date" wire:model.live="filterDateFrom" class="w-full bg-gray-50 border-gray-200 text-gray-800 text-sm rounded-xl focus:ring-2 focus:ring-purple-500 py-2.5 px-4">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">{{ __('Registration Date To') }}</label>
                <input type="date" wire:model.live="filterDateTo" class="w-full bg-gray-50 border-gray-200 text-gray-800 text-sm rounded-xl focus:ring-2 focus:ring-purple-500 py-2.5 px-4">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">{{ __('Medical Files') }}</label>
                <select wire:model.live="filterHasFiles" class="w-full bg-gray-50 border-gray-200 text-gray-800 text-sm rounded-xl focus:ring-2 focus:ring-purple-500 py-2.5 px-4">
                    <option value="">{{ __('All Patients') }}</option>
                    <option value="yes">{{ __('Has Files') }}</option>
                    <option value="no">{{ __('No Files') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">{{ __('Minimum Visits') }}</label>
                <input type="number" min="0" wire:model.live.debounce.500ms="filterMinVisits" placeholder="{{ __('e.g., 3') }}" class="w-full bg-gray-50 border-gray-200 text-gray-800 text-sm rounded-xl focus:ring-2 focus:ring-purple-500 py-2.5 px-4">
            </div>
        </div>
    </div>
    @endif

    @if (session()->has('success'))
        <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-bold text-sm">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Add Patient Form -->
    @if($showAddPatient)
    <form wire:submit="createPatient" class="bg-gradient-to-br from-purple-50 to-white p-6 md:p-8 rounded-3xl border border-purple-100 shadow-sm mb-6 animate-fade-in-down">
        <h3 class="text-lg font-bold text-purple-900 mb-6 flex items-center gap-2">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            {{ __('Add New Patient Record') }}
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700">{{ __('Full Name') }} <span class="text-red-500">*</span></label>
                <input wire:model="name" type="text" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 transition-colors">
                @error('name') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
            </div>
            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700">{{ __('Phone Number') }} <span class="text-red-500">*</span></label>
                <input wire:model="phone" type="text" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 transition-colors" dir="ltr">
                @error('phone') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
            </div>
            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700">{{ __('Age') }}</label>
                <input wire:model="age" type="number" min="0" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 transition-colors">
            </div>
            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700">{{ __('Address') }}</label>
                <input wire:model="address" type="text" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 transition-colors">
            </div>
        </div>
        <div class="mt-8 flex justify-end">
            <button type="submit" class="px-8 py-3 bg-purple-600 text-white font-bold rounded-xl shadow-lg shadow-purple-200 hover:bg-purple-700 hover:-translate-y-0.5 transition-all">
                {{ __('Save Patient') }}
            </button>
        </div>
    </form>
    @endif

    <!-- Patients List Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                <thead class="bg-gray-50 border-b border-gray-100 text-gray-500">
                    <tr>
                        <th class="px-6 py-4 text-sm font-bold w-16">#</th>
                        <th class="px-6 py-4 text-sm font-bold">{{ __('Patient Details') }}</th>
                        <th class="px-6 py-4 text-sm font-bold text-center">{{ __('Visits History') }}</th>
                        <th class="px-6 py-4 text-sm font-bold text-center">{{ __('Medical Files') }}</th>
                        <th class="px-6 py-4 text-sm font-bold text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($patients as $patient)
                    <tr wire:key="patient-{{ $patient->id }}" class="hover:bg-purple-50/30 transition-colors group">
                        <td class="px-6 py-5 text-sm font-bold text-gray-400">{{ $patient->id }}</td>
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-purple-100 to-purple-50 flex items-center justify-center text-purple-600 font-bold shrink-0">
                                    {{ mb_substr($patient->name, 0, 1, 'UTF-8') }}
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 group-hover:text-purple-700 transition-colors">{{ $patient->name }}</h4>
                                    <p class="text-sm text-gray-500" dir="ltr">{{ $patient->phone }}</p>
                                    @if($patient->tags && is_array($patient->tags))
                                    <div class="flex flex-wrap gap-1 mt-1.5">
                                        @foreach($patient->tags as $tag)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1 {{ app()->getLocale() === 'ar' ? 'ml-1 mr-0' : '' }}"></span>
                                                {{ mb_strlen($tag) > 20 ? mb_substr($tag, 0, 20).'...' : $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <div class="inline-flex items-center justify-center px-3 py-1 bg-green-50 text-green-700 rounded-lg text-xs font-bold border border-green-100">
                                {{ $patient->visits_count }} {{ __('Visits') }}
                            </div>
                        </td>
                        <td class="px-6 py-5 text-center">
                            @if($patient->files_count > 0)
                            <div class="inline-flex items-center justify-center px-3 py-1 bg-rose-50 text-rose-700 rounded-lg text-xs font-bold border border-rose-100 gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                {{ $patient->files_count }} {{ __('Files') }}
                            </div>
                            @else
                            <span class="text-xs text-gray-400 font-medium">{{ __('None') }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('patients.show', $patient->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-gray-100 text-gray-700 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-xs font-bold transition-all" title="{{ __('Open File') }}">
                                    <svg class="w-4 h-4 mr-1 {{ app()->getLocale() === 'ar' ? 'ml-1 mr-0' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    {{ __('Profile') }}
                                </a>
                                <button wire:click="openBooking({{ $patient->id }})" class="inline-flex items-center justify-center px-3 py-1.5 bg-purple-50 text-purple-700 hover:bg-purple-600 hover:text-white rounded-lg text-xs font-bold transition-all shadow-sm group-hover:shadow-md" title="{{ __('Add Booking') }}">
                                    <svg class="w-4 h-4 mr-1 {{ app()->getLocale() === 'ar' ? 'ml-1 mr-0' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    {{ __('Book') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center justify-center space-y-5">
                                <div class="relative w-24 h-24 flex items-center justify-center">
                                    <div class="absolute inset-0 bg-purple-100 rounded-full animate-ping opacity-30"></div>
                                    <div class="w-20 h-20 bg-purple-50 text-purple-600 rounded-full flex items-center justify-center relative z-10 border border-purple-100 shadow-sm">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-800 mb-1">{{ __('No patients found') }}</h3>
                                    <p class="text-gray-500 text-sm max-w-sm mx-auto leading-relaxed">{{ __('Get started by adding a new patient to the system or adjusting your search filters.') }}</p>
                                </div>
                                <div class="mt-4">
                                     <button wire:click="$set('showAddPatient', true)" class="px-5 py-2.5 bg-white border border-gray-200 hover:border-purple-300 hover:bg-purple-50 text-purple-700 font-bold rounded-xl shadow-sm transition-all flex items-center justify-center gap-2 text-sm mx-auto">
                                        {{ __('New Patient') }} &rarr;
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($patients->hasPages())
        <div class="p-6 border-t border-gray-50 bg-gray-50/50">
            {{ $patients->links() }}
        </div>
        @endif
    </div>

    <!-- Booking Modal -->
    @if($bookingPatientId)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm shadow-2xl transition-opacity animate-fade-in-down" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <div class="bg-white rounded-3xl p-6 md:p-8 max-w-md w-full border border-purple-100 shadow-2xl">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    {{ __('Rapid Booking') }}
                </h3>
                <button type="button" wire:click="cancelBooking" class="p-2 hover:bg-gray-100 rounded-full transition-colors text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form wire:submit="confirmBooking" class="space-y-5">
                @if(auth()->user()->role !== 'doctor')
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">{{ __('Select Doctor') }} <span class="text-red-500">*</span></label>
                    <select wire:model="bookingDoctorId" class="w-full px-4 py-3 bg-gray-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 transition-colors">
                        <option value="">{{ __('Choose a doctor...') }}</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}">{{ __('Dr.') }} {{ $doctor->name }}</option>
                        @endforeach
                    </select>
                    @error('bookingDoctorId') <span class="text-sm text-red-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>
                @endif
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">{{ __('Date') }} <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="bookingDate" class="w-full px-4 py-3 bg-gray-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 transition-colors">
                        @error('bookingDate') <span class="text-sm text-red-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">{{ __('Visit Type') }} <span class="text-red-500">*</span></label>
                        <select wire:model="bookingType" class="w-full px-4 py-3 bg-gray-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 transition-colors">
                            <option value="checkup">{{ __('Checkup') }}</option>
                            <option value="follow_up">{{ __('Follow-up') }}</option>
                        </select>
                    </div>
                </div>
                
                <div class="bg-purple-50 p-4 rounded-xl border border-purple-100 mt-2">
                    <p class="text-xs text-purple-800 font-medium text-center">
                        <svg class="w-4 h-4 inline-block mb-0.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ __('Patient will be added to the doctor\'s waitlist for the selected date.') }}
                    </p>
                </div>

                <div class="flex items-center gap-3 pt-6">
                    <button type="button" wire:click="cancelBooking" class="flex-1 px-4 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-50 hover:border-gray-300 transition-all text-sm">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="flex-[2] px-4 py-3 bg-purple-600 text-white rounded-xl font-bold font-bold shadow-lg shadow-purple-200 hover:bg-purple-700 hover:-translate-y-0.5 transition-all text-sm">
                        {{ __('Confirm Booking') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>

