<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Patient;
use App\Models\User;
use App\Services\PatientService;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $showAddPatient = false;
    public $showAdvancedFilters = false;
    
    // Advanced Filters
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $filterHasFiles = '';
    public $filterMinVisits = '';
    public $filterDiagnosis = '';
    
    // New patient fields
    public $name, $phone, $age, $weight, $address, $gender;

    // Rapid booking fields
    public $bookingPatientId = null;
    public $bookingDoctorId = '';
    public $bookingDate = '';
    public $bookingTime = '';
    public $bookingType = 'checkup';

    // File Upload fields
    public $showUploadModal = false;
    public $uploadPatientId = null;
    public $newFile;
    public $fileType = 'lab';

    public function mount()
    {
        $this->bookingDate = now()->format('Y-m-d');
        $this->bookingTime = now()->addMinutes(5)->format('H:i');
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
            'bookingTime' => 'required',
            'bookingType' => 'required|in:checkup,follow_up',
        ]);

        app(AppointmentService::class)->bookAppointment([
            'patient_id' => $this->bookingPatientId,
            'doctor_id' => $this->bookingDoctorId,
            'scheduled_at' => Carbon::parse($this->bookingDate . ' ' . $this->bookingTime),
            'status' => 'pending',
            'type' => $this->bookingType,
        ]);

        $this->cancelBooking();
        session()->flash('success', __('Patient appointment booked successfully.'));
    }
    
    public function exportCSV()
    {
        $basePatientQuery = Patient::query();

        if (!auth()->user()->isAdmin()) {
            $doctorId = auth()->user()->isDoctor() ? auth()->id() : auth()->user()->doctor_id;
            $basePatientQuery->where('doctor_id', $doctorId);
        }

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
            ->when($this->filterDiagnosis !== '', function($q) {
                $q->whereHas('visits', function($vq) {
                    $vq->where('diagnosis', 'like', '%'.$this->filterDiagnosis.'%');
                });
            })
            ->orderBy('name', 'asc')
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
    public function updatingFilterDiagnosis() { $this->resetPage(); }

    public function uploadFile()
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();
        if (!$currentUser->isDoctor() && !$currentUser->isSecretary()) return;

        /** @var \App\Models\User $doctor */
        $doctor = $currentUser->isDoctor() ? $currentUser : $currentUser->assignedDoctor;

        $this->validate([
            'newFile' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
            'fileType' => 'required|in:investigation,lab,other',
        ]);

        $fileSize = $this->newFile->getSize();

        // Check storage limit
        if ($doctor && $doctor->max_storage_gb > 0) {
            $maxBytes = $doctor->max_storage_gb * 1024 * 1024 * 1024;
            if (($doctor->used_storage_bytes + $fileSize) > $maxBytes) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'newFile' => [__('Storage limit reached for this clinic. Please contact administration.')]
                ]);
            }
        }

        $patient = \App\Models\Patient::findOrFail($this->uploadPatientId);
        $this->authorize('update', $patient);

        $path = $this->newFile->store('patient_files', 'public');

        \App\Models\PatientFile::create([
            'patient_id' => $patient->id,
            'file_name' => $this->newFile->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $this->fileType,
            'uploaded_by' => auth()->id(),
        ]);

        // Update used_storage_bytes
        if ($doctor) {
            $doctor->increment('used_storage_bytes', $fileSize);
        }

        $this->showUploadModal = false;
        $this->uploadPatientId = null;
        $this->reset(['newFile']);
        session()->flash('message', __('File uploaded successfully.'));
    }

    public function openUploadModal($id)
    {
        $this->uploadPatientId = $id;
        $this->resetValidation();
        $this->reset(['newFile']);
        $this->fileType = 'lab';
        $this->showUploadModal = true;
    }

    public function closeUploadModal() { $this->showUploadModal = false; }

    public function render(): mixed
    {
        $basePatientQuery = Patient::query();

        if (!auth()->user()->isAdmin()) {
            $doctorId = auth()->user()->isDoctor() ? auth()->id() : auth()->user()->doctor_id;
            $basePatientQuery->where('doctor_id', $doctorId);
        }

        $patients = (clone $basePatientQuery)->withCount(['appointments', 'visits', 'files'])
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                      ->orWhere('phone', 'like', '%'.$this->search.'%')
                      ->orWhereHas('visits', function($vq) {
                          $vq->where('diagnosis', 'like', '%'.$this->search.'%');
                      });
                });
            })
            ->when($this->filterDateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn($q) => $q->whereDate('created_at', '<=', $this->filterDateTo))
            ->when($this->filterHasFiles === 'yes', fn($q) => $q->has('files'))
            ->when($this->filterHasFiles === 'no', fn($q) => $q->doesntHave('files'))
            ->when($this->filterMinVisits !== '', fn($q) => $q->having('visits_count', '>=', (int)$this->filterMinVisits))
            ->when($this->filterDiagnosis !== '', function($q) {
                $q->whereHas('visits', function($vq) {
                    $vq->where('diagnosis', 'like', '%'.$this->filterDiagnosis.'%');
                });
            })
            ->orderBy('name', 'asc')
            ->paginate(15);

        $stats = [
            'total' => (clone $basePatientQuery)->count(),
            'this_month' => (clone $basePatientQuery)->whereMonth('created_at', now()->month)->count(),
            'with_files' => (clone $basePatientQuery)->has('files')->count(),
            'total_visits' => \App\Models\Visit::when(!auth()->user()->isAdmin(), function($q) {
                $q->where('doctor_id', auth()->user()->isDoctor() ? auth()->id() : auth()->user()->doctor_id);
            })->count(),
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

<div class="space-y-6" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 animate-fade-in-up">
        <div class="bg-gradient-to-br from-white to-purple-50/30 p-6 rounded-3xl border border-purple-100 shadow-sm hover:shadow-md transition-all flex items-center gap-4 group">
            <div class="w-14 h-14 bg-purple-100/80 text-purple-600 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300 shadow-inner">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <p class="text-[11px] md:text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider"><?php echo e(__('Total Patients')); ?></p>
                <h3 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight"><?php echo e(number_format($stats['total'])); ?></h3>
            </div>
        </div>
        <div class="bg-gradient-to-br from-white to-blue-50/30 p-6 rounded-3xl border border-blue-100 shadow-sm hover:shadow-md transition-all flex items-center gap-4 group">
            <div class="w-14 h-14 bg-blue-100/80 text-blue-600 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300 shadow-inner">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <p class="text-[11px] md:text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider"><?php echo e(__('New This Month')); ?></p>
                <h3 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight"><?php echo e(number_format($stats['this_month'])); ?></h3>
            </div>
        </div>
        <div class="bg-gradient-to-br from-white to-rose-50/30 p-6 rounded-3xl border border-rose-100 shadow-sm hover:shadow-md transition-all flex items-center gap-4 group">
            <div class="w-14 h-14 bg-rose-100/80 text-rose-600 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300 shadow-inner">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div>
                <p class="text-[11px] md:text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider"><?php echo e(__('With Medical Files')); ?></p>
                <h3 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight"><?php echo e(number_format($stats['with_files'])); ?></h3>
            </div>
        </div>
        <div class="bg-gradient-to-br from-white to-emerald-50/30 p-6 rounded-3xl border border-emerald-100 shadow-sm hover:shadow-md transition-all flex items-center gap-4 group">
            <div class="w-14 h-14 bg-emerald-100/80 text-emerald-600 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300 shadow-inner">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
            <div>
                <p class="text-[11px] md:text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider"><?php echo e(__('Total Visits')); ?></p>
                <h3 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight"><?php echo e(number_format($stats['total_visits'])); ?></h3>
            </div>
        </div>
    </div>

    <!-- Header & Actions -->
    <div class="flex flex-col lg:flex-row items-center justify-between gap-4 bg-white p-4 md:p-6 rounded-3xl border border-gray-100 shadow-sm">
        <div class="relative w-full lg:w-[400px]">
            <div class="absolute inset-y-0 <?php echo e(app()->getLocale() === 'ar' ? 'right-0 pr-4' : 'left-0 pl-4'); ?> flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="<?php echo e(__('Search by patient name or phone number...')); ?>" 
                   class="w-full bg-slate-50 border-gray-200 text-gray-800 text-sm rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white block <?php echo e(app()->getLocale() === 'ar' ? 'pr-11 pl-4' : 'pl-11 pr-4'); ?> py-3 transition-colors shadow-inner">
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3 w-full lg:w-auto">
            <button wire:click="$toggle('showAdvancedFilters')" class="flex-1 lg:flex-none px-4 py-3 bg-white hover:bg-gray-50 text-gray-700 font-bold rounded-xl border border-gray-200 transition-all flex items-center justify-center gap-2 text-sm shadow-sm hover:shadow">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                <?php echo e(__('Filters')); ?>

            </button>
            <button wire:click="exportCSV" class="flex-1 lg:flex-none px-4 py-3 bg-white hover:bg-emerald-50 hover:border-emerald-200 text-emerald-700 font-bold rounded-xl border border-gray-200 transition-all flex items-center justify-center gap-2 text-sm shadow-sm hover:shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <?php echo e(__('Export CSV')); ?>

            </button>
            <button wire:click="$toggle('showAddPatient')" class="w-full lg:w-auto px-8 py-4 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-black rounded-2xl shadow-xl shadow-purple-200 transition-all flex items-center justify-center gap-3 text-base hover:-translate-y-1 hover:shadow-2xl active:scale-95 group">
                <svg class="w-6 h-6 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span class="inline-block"><?php echo e($showAddPatient ? __('Close Form') : __('New Patient')); ?></span>
            </button>
        </div>
    </div>

    <!-- Advanced Filters Panel -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showAdvancedFilters): ?>
    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm animate-fade-in-down">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider"><?php echo e(__('Date From')); ?></label>
                <input type="date" wire:model.live="filterDateFrom" class="w-full bg-gray-50 border-gray-200 text-gray-800 text-sm rounded-xl focus:ring-2 focus:ring-purple-500 py-2.5 px-4">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider"><?php echo e(__('Date To')); ?></label>
                <input type="date" wire:model.live="filterDateTo" class="w-full bg-gray-50 border-gray-200 text-gray-800 text-sm rounded-xl focus:ring-2 focus:ring-purple-500 py-2.5 px-4">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider"><?php echo e(__('Diagnosis')); ?></label>
                <input type="text" wire:model.live.debounce.500ms="filterDiagnosis" placeholder="<?php echo e(__('e.g., Diabetes')); ?>" class="w-full bg-gray-50 border-gray-200 text-gray-800 text-sm rounded-xl focus:ring-2 focus:ring-purple-500 py-2.5 px-4">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider"><?php echo e(__('Medical Files')); ?></label>
                <select wire:model.live="filterHasFiles" class="w-full bg-gray-50 border-gray-200 text-gray-800 text-sm rounded-xl focus:ring-2 focus:ring-purple-500 py-2.5 px-4">
                    <option value=""><?php echo e(__('All Patients')); ?></option>
                    <option value="yes"><?php echo e(__('Has Files')); ?></option>
                    <option value="no"><?php echo e(__('No Files')); ?></option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider"><?php echo e(__('Min. Visits')); ?></label>
                <input type="number" min="0" wire:model.live.debounce.500ms="filterMinVisits" placeholder="<?php echo e(__('e.g., 3')); ?>" class="w-full bg-gray-50 border-gray-200 text-gray-800 text-sm rounded-xl focus:ring-2 focus:ring-purple-500 py-2.5 px-4">
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
        <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-bold text-sm"><?php echo e(session('success')); ?></span>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Add Patient Form -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showAddPatient): ?>
    <form wire:submit="createPatient" class="bg-gradient-to-br from-purple-50 to-white p-6 md:p-8 rounded-3xl border border-purple-100 shadow-sm mb-6 animate-fade-in-down">
        <h3 class="text-lg font-bold text-purple-900 mb-6 flex items-center gap-2">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            <?php echo e(__('Add New Patient Record')); ?>

        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700"><?php echo e(__('Full Name')); ?> <span class="text-red-500">*</span></label>
                <input wire:model="name" type="text" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 transition-colors">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs font-bold"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700"><?php echo e(__('Phone Number')); ?> <span class="text-red-500">*</span></label>
                <input wire:model="phone" type="text" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 transition-colors" dir="ltr">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs font-bold"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700"><?php echo e(__('Age')); ?></label>
                <input wire:model="age" type="number" min="0" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 transition-colors">
            </div>
            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700"><?php echo e(__('Address')); ?></label>
                <input wire:model="address" type="text" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 transition-colors">
            </div>
        </div>
        <div class="mt-8 flex justify-end">
            <button type="submit" class="px-8 py-3 bg-purple-600 text-white font-bold rounded-xl shadow-lg shadow-purple-200 hover:bg-purple-700 hover:-translate-y-0.5 transition-all">
                <?php echo e(__('Save Patient')); ?>

            </button>
        </div>
    </form>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Patients List Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
                <thead class="bg-gray-50 border-b border-gray-100 text-gray-500">
                    <tr>
                        <th class="px-6 py-4 text-sm font-bold w-16">#</th>
                        <th class="px-6 py-4 text-sm font-bold"><?php echo e(__('Patient Details')); ?></th>
                        <th class="px-6 py-4 text-sm font-bold text-center"><?php echo e(__('Visits History')); ?></th>
                        <th class="px-6 py-4 text-sm font-bold text-center"><?php echo e(__('Medical Files')); ?></th>
                        <th class="px-6 py-4 text-sm font-bold text-center"><?php echo e(__('Actions')); ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $patients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processElementKey('patient-{{ $patient->id }}', get_defined_vars()); ?>wire:key="patient-<?php echo e($patient->id); ?>" class="hover:bg-purple-50/30 transition-colors group">
                        <td class="px-6 py-5 text-sm font-bold text-gray-400">
                            <?php echo e(($patients->currentPage() - 1) * $patients->perPage() + $loop->iteration); ?>

                        </td>
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-purple-100 to-purple-50 flex items-center justify-center text-purple-600 font-bold shrink-0">
                                    <?php echo e(mb_substr($patient->name, 0, 1, 'UTF-8')); ?>

                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 group-hover:text-purple-700 transition-colors"><?php echo e($patient->name); ?></h4>
                                    <p class="text-sm text-gray-500" dir="ltr"><?php echo e($patient->phone); ?></p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($patient->tags && is_array($patient->tags)): ?>
                                    <div class="flex flex-wrap gap-1 mt-1.5">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $patient->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1 <?php echo e(app()->getLocale() === 'ar' ? 'ml-1 mr-0' : ''); ?>"></span>
                                                <?php echo e(mb_strlen($tag) > 20 ? mb_substr($tag, 0, 20).'...' : $tag); ?>

                                            </span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <div class="inline-flex items-center justify-center px-3 py-1 bg-green-50 text-green-700 rounded-lg text-xs font-bold border border-green-100">
                                <?php echo e($patient->visits_count); ?> <?php echo e(__('Visits')); ?>

                            </div>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($patient->files_count > 0): ?>
                            <div class="inline-flex items-center justify-center px-3 py-1 bg-rose-50 text-rose-700 rounded-lg text-xs font-bold border border-rose-100 gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                <?php echo e($patient->files_count); ?> <?php echo e(__('Files')); ?>

                            </div>
                            <?php else: ?>
                            <span class="text-xs text-gray-400 font-medium"><?php echo e(__('None')); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="<?php echo e(route('patients.show', $patient->id)); ?>" class="inline-flex items-center justify-center w-10 h-10 bg-purple-100 text-purple-700 hover:bg-purple-600 hover:text-white rounded-xl transition-all shadow-sm border border-purple-200" title="<?php echo e(__('Open Profile')); ?>">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <button wire:click="openUploadModal(<?php echo e($patient->id); ?>)" class="inline-flex items-center justify-center w-10 h-10 bg-blue-100 text-blue-700 hover:bg-blue-600 hover:text-white rounded-xl transition-all shadow-sm border border-blue-200" title="<?php echo e(__('Upload File')); ?>">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                </button>
                                <a href="https://wa.me/<?php echo e(preg_replace('/[^0-9]/', '', $patient->phone)); ?>" target="_blank" class="inline-flex items-center justify-center w-10 h-10 bg-emerald-100 text-emerald-700 hover:bg-emerald-600 hover:text-white rounded-xl transition-all shadow-sm border border-emerald-200" title="<?php echo e(__('WhatsApp')); ?>">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    </a>
                                </div>
                        </td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
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
                                    <h3 class="text-xl font-bold text-gray-800 mb-1"><?php echo e(__('No patients found')); ?></h3>
                                    <p class="text-gray-500 text-sm max-w-sm mx-auto leading-relaxed"><?php echo e(__('Get started by adding a new patient to the system or adjusting your search filters.')); ?></p>
                                </div>
                                <div class="mt-4">
                                     <button wire:click="$set('showAddPatient', true)" class="px-5 py-2.5 bg-white border border-gray-200 hover:border-purple-300 hover:bg-purple-50 text-purple-700 font-bold rounded-xl shadow-sm transition-all flex items-center justify-center gap-2 text-sm mx-auto">
                                        <?php echo e(__('New Patient')); ?> &rarr;
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($patients->hasPages()): ?>
        <div class="p-6 border-t border-gray-50 bg-gray-50/50">
            <?php echo e($patients->links()); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Booking Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bookingPatientId): ?>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm shadow-2xl transition-opacity animate-fade-in-down" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
        <div class="bg-white rounded-3xl p-6 md:p-8 max-w-md w-full border border-purple-100 shadow-2xl">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <?php echo e(__('Rapid Booking')); ?>

                </h3>
                <button type="button" wire:click="cancelBooking" class="p-2 hover:bg-gray-100 rounded-full transition-colors text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form wire:submit="confirmBooking" class="space-y-5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isAdmin()): ?>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2"><?php echo e(__('Select Doctor')); ?> <span class="text-red-500">*</span></label>
                    <select wire:model="bookingDoctorId" class="w-full px-4 py-3 bg-gray-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 transition-colors">
                        <option value=""><?php echo e(__('Choose a doctor...')); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <option value="<?php echo e($doctor->id); ?>"><?php echo e(__('Dr.')); ?> <?php echo e($doctor->name); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['bookingDoctorId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-sm text-red-500 font-bold mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2"><?php echo e(__('Date')); ?> <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="bookingDate" class="w-full px-4 py-3 bg-gray-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 transition-colors">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['bookingDate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-sm text-red-500 font-bold mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2"><?php echo e(__('Visit Type')); ?> <span class="text-red-500">*</span></label>
                        <select wire:model="bookingType" class="w-full px-4 py-3 bg-gray-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 transition-colors">
                            <option value="checkup"><?php echo e(__('Checkup')); ?></option>
                            <option value="follow_up"><?php echo e(__('Follow-up')); ?></option>
                        </select>
                    </div>
                </div>
                
                <div class="bg-purple-50 p-4 rounded-xl border border-purple-100 mt-2">
                    <p class="text-xs text-purple-800 font-medium text-center">
                        <svg class="w-4 h-4 inline-block mb-0.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <?php echo e(__('Patient will be added to the doctor\'s waitlist for the selected date.')); ?>

                    </p>
                </div>

                <div class="flex items-center gap-3 pt-6">
                    <button type="button" wire:click="cancelBooking" class="flex-1 px-4 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-50 hover:border-gray-300 transition-all text-sm">
                        <?php echo e(__('Cancel')); ?>

                    </button>
                    <button type="submit" class="flex-[2] px-4 py-3 bg-purple-600 text-white rounded-xl font-bold font-bold shadow-lg shadow-purple-200 hover:bg-purple-700 hover:-translate-y-0.5 transition-all text-sm">
                        <?php echo e(__('Confirm Booking')); ?>

                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Upload File Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showUploadModal): ?>
    <div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processElementKey('upload-modal-container', get_defined_vars()); ?>wire:key="upload-modal-container" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div wire:click="closeUploadModal" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="bg-white rounded-[2rem] w-full max-w-lg shadow-2xl relative overflow-hidden animate-zoom-in p-8">
            <h3 class="text-xl font-bold mb-6 flex items-center gap-3 text-gray-900">
                <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                </div>
                <?php echo e(__('Upload Medical File')); ?>

            </h3>
            <form wire:submit.prevent="uploadFile" class="space-y-5">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase"><?php echo e(__('File Type')); ?></label>
                    <select wire:model="fileType" class="w-full bg-slate-50 border-gray-200 rounded-xl px-4 py-3">
                        <option value="lab"><?php echo e(__('Lab Result')); ?></option>
                        <option value="investigation"><?php echo e(__('Investigation')); ?></option>
                        <option value="other"><?php echo e(__('Other Document')); ?></option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase"><?php echo e(__('Choose File')); ?></label>
                    <input type="file" wire:model="newFile" class="w-full bg-slate-50 border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-500 file:me-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all cursor-pointer">
                    <div wire:loading wire:target="newFile" class="text-xs text-blue-600 font-bold mt-2"><?php echo e(__('Preparing file...')); ?></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newFile'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-500 block mt-1"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="pt-6 flex gap-3">
                    <button type="button" wire:click="closeUploadModal" class="flex-1 py-3 text-gray-500 font-bold hover:bg-gray-50 rounded-xl transition-colors"><?php echo e(__('Cancel')); ?></button>
                    <button type="submit" class="flex-[2] py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-200 transition-all flex justify-center items-center gap-2">
                        <span wire:loading.remove wire:target="uploadFile"><?php echo e(__('Upload')); ?></span>
                        <span wire:loading wire:target="uploadFile"><?php echo e(__('Saving...')); ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<?php /**PATH C:\Bola\Clinova\resources\views/livewire/shared/patients-list.blade.php ENDPATH**/ ?>