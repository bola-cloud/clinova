<?php
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Patient;
use App\Services\AppointmentService;
use App\Models\Appointment;
use App\Models\PatientFile;
use App\Services\PatientService;
use Carbon\Carbon;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $search = '';
    public $showAddPatient = false;
    public $selectedDate;
    
    // Assigned Doctor
    public $assignedDoctorId;
    public $assignedDoctorName;

    // Booking Details
    public $bookingPatientId = null;
    public $bookingTime = '';
    public $bookingType = 'checkup';
    public $patientFiles = [];

    // New patient fields
    public $name, $phone, $age, $weight, $address, $gender;
    
    // Edit Appointment fields
    public $editingAppointmentId = null;
    public $editDoctorId;
    public $editTime;

    public function mount()
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->bookingTime = now()->addMinutes(15)->format('H:i');
        
        $user = auth()->user();
        if ($user->doctor_id) {
            $this->assignedDoctorId = $user->doctor_id;
            $this->assignedDoctorName = $user->assignedDoctor->name;
        } else {
            // Fallback to first doctor if none assigned (safety)
            $firstDoctor = \App\Models\User::where('role', 'doctor')->first();
            $this->assignedDoctorId = $firstDoctor?->id;
            $this->assignedDoctorName = $firstDoctor?->name;
        }
    }

    public function with()
    {
        $date = Carbon::parse($this->selectedDate);

        return [
            'patients' => $this->search ? Patient::where('doctor_id', $this->assignedDoctorId)->where(function($q) {
                $q->where('name', 'like', '%'.$this->search.'%')->orWhere('phone', 'like', '%'.$this->search.'%');
            })->take(5)->get() : [],
            'dailyAppointments' => Appointment::with(['patient', 'doctor'])
                ->where('doctor_id', $this->assignedDoctorId)
                ->whereHas('patient', function ($query) {
                    $query->where('doctor_id', $this->assignedDoctorId);
                })
                ->whereBetween('scheduled_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
                ->orderBy('scheduled_at', 'asc')
                ->orderBy('queue_order', 'asc')
                ->paginate(15),
            'stats' => Appointment::where('doctor_id', $this->assignedDoctorId)
                ->whereBetween('scheduled_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
                ->selectRaw('count(*) as total, sum(case when status = "pending" then 1 else 0 end) as pending, sum(case when status = "checked-in" then 1 else 0 end) as prepared')
                ->first(),
            'doctors' => \App\Models\User::where('role', 'doctor')->get(),
        ];
    }

    public function createPatient()
    {
        $this->validate([
            'name' => 'required|min:3',
            'phone' => 'required|numeric',
            'patientFiles.*' => 'nullable|file|max:10240', // 10MB max
        ]);

        $patient = app(PatientService::class)->createPatient([
            'name' => $this->name,
            'phone' => $this->phone,
            'age' => $this->age,
            'weight' => $this->weight,
            'address' => $this->address,
            'doctor_id' => $this->assignedDoctorId,
        ]);

        // Handle File Uploads
        if ($this->patientFiles) {
            foreach ($this->patientFiles as $file) {
                $path = $file->store("patients/{$patient->id}/files", 'public');
                
                PatientFile::create([
                    'patient_id' => $patient->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        $this->reset(['name', 'phone', 'age', 'weight', 'address', 'showAddPatient', 'patientFiles']);
        $this->search = $patient->name;
        session()->flash('message', __('Patient created successfully with files.'));
    }

    public function checkIn($appointmentId)
    {
        $appointment = Appointment::findOrFail($appointmentId);
        app(AppointmentService::class)->updateStatus($appointment, 'checked-in');
        session()->flash('message', __('Patient prepared successfully.'));
    }

    public function selectForBooking($patientId)
    {
        $this->bookingPatientId = $patientId;
        // Default time to current time rounded to next 5 mins if it's today
        if ($this->selectedDate === now()->format('Y-m-d')) {
            $this->bookingTime = now()->addMinutes(5)->format('H:i');
        } else {
            $this->bookingTime = '09:00';
        }
    }

    public function confirmBooking()
    {
        $this->validate([
            'bookingTime' => 'required',
            'bookingType' => 'required|in:checkup,follow_up',
        ]);

        $scheduledAt = Carbon::parse($this->selectedDate . ' ' . $this->bookingTime);

        try {
            app(AppointmentService::class)->bookAppointment([
                'patient_id' => $this->bookingPatientId,
                'doctor_id' => $this->assignedDoctorId,
                'scheduled_at' => $scheduledAt,
                'type' => $this->bookingType,
                'status' => 'pending'
            ]);

            $this->clearSearch();
            $this->resetPage();
            session()->flash('message', __('Appointment booked successfully.'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('bookingTime', $e->getMessage());
        }
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->bookingPatientId = null;
    }
    
    public function setDate($date)
    {
        $this->selectedDate = $date;
        $this->resetPage();
    }
    
    public function editAppointment($id)
    {
        $appointment = Appointment::findOrFail($id);
        $this->editingAppointmentId = $appointment->id;
        $this->editDoctorId = $appointment->doctor_id;
        $this->editTime = $appointment->scheduled_at->format('H:i');
    }

    public function saveAppointment()
    {
        $this->validate([
            'editDoctorId' => 'required|exists:users,id',
            'editTime' => 'required|date_format:H:i',
        ]);

        $appointment = Appointment::findOrFail($this->editingAppointmentId);
        
        $newScheduledAt = Carbon::parse($this->selectedDate . ' ' . $this->editTime);
        
        $auditLog = $appointment->audit_log ?? [];
        $auditLog[] = [
            'action' => 'modified',
            'by_user_id' => auth()->id(),
            'by_user_name' => auth()->user()->name,
            'timestamp' => now()->toDateTimeString(),
            'changes' => [
                'doctor_id_from' => $appointment->doctor_id,
                'doctor_id_to' => $this->assignedDoctorId ?? $this->editDoctorId,
                'time_from' => $appointment->scheduled_at->toDateTimeString(),
                'time_to' => $newScheduledAt->toDateTimeString(),
            ]
        ];

        $appointment->update([
            'doctor_id' => $this->assignedDoctorId ?? $this->editDoctorId,
            'scheduled_at' => $newScheduledAt,
            'audit_log' => $auditLog
        ]);

        $this->editingAppointmentId = null;
    }
    
    public function cancelEdit()
    {
        $this->editingAppointmentId = null;
    }
};
?><div class="grid grid-cols-1 lg:grid-cols-3 gap-8" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    @pushOnce('styles')
    <style>
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideInTop {
            from { opacity: 0; max-height: 0; overflow: hidden; }
            to { opacity: 1; max-height: 500px; overflow: visible; }
        }
        .animate-fade-in-down {
            animation: fadeInDown 0.3s ease-out forwards;
        }
        .animate-slide-in-top {
            animation: slideInTop 0.4s ease-out forwards;
        }
        .custom-scrollbar::-webkit-scrollbar { height: 4px; width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9c27b0; }
    </style>
    @endPushOnce
    <div class="lg:col-span-2 space-y-8">
        @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 text-emerald-700 rounded-2xl border border-emerald-100 flex items-center gap-3 animate-fade-in-down">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-bold text-sm">{{ session('message') }}</span>
        </div>
        @endif

        @if (session()->has('error'))
        <div class="p-4 bg-rose-50 text-rose-700 rounded-2xl border border-rose-100 flex items-center gap-3 animate-fade-in-down">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-bold text-sm">{{ session('error') }}</span>
        </div>
        @endif

        <!-- Patient Control Center -->
        <div class="bg-white p-4 md:p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
                <h3 class="font-bold text-lg">{{ __('Patients & Booking Management') }}</h3>
                <button wire:click="$toggle('showAddPatient')" class="w-full sm:w-auto px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-base font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
                    {{ $showAddPatient ? __('Close') : __('New Patient') . ' +' }}
                </button>
            </div>

            @if($showAddPatient)
            <form wire:submit="createPatient" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8 p-4 bg-purple-50 rounded-2xl border border-purple-100">
                <div class="space-y-1">
                    <label class="text-sm font-bold text-purple-900">{{ __('Full Name') }} *</label>
                    <input wire:model="name" type="text" class="w-full px-4 py-2 rounded-lg border-none focus:ring-2 focus:ring-purple-500">
                    @error('name') <span class="text-sm text-red-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-bold text-purple-900">{{ __('Phone') }} *</label>
                    <input wire:model="phone" type="text" class="w-full px-4 py-2 rounded-lg border-none focus:ring-2 focus:ring-purple-500">
                    @error('phone') <span class="text-sm text-red-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-bold text-purple-900">{{ __('Age') }}</label>
                    <input wire:model="age" type="number" class="w-full px-4 py-2 rounded-lg border-none focus:ring-2 focus:ring-purple-500">
                    @error('age') <span class="text-sm text-red-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-bold text-purple-900">{{ __('Address') }}</label>
                    <input wire:model="address" type="text" class="w-full px-4 py-2 rounded-lg border-none focus:ring-2 focus:ring-purple-500">
                    @error('address') <span class="text-sm text-red-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2 space-y-2">
                    <label class="text-sm font-bold text-purple-900">{{ __('Attach Files (ID, Reports, etc.)') }}</label>
                    <div class="relative group">
                        <input type="file" wire:model="patientFiles" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        <div class="w-full px-4 py-3 bg-white border-2 border-dashed border-purple-200 rounded-xl flex items-center justify-center gap-3 text-purple-600 group-hover:border-purple-400 transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            <span class="font-bold">{{ __('Click or drag to upload files') }}</span>
                            <div wire:loading wire:target="patientFiles">
                                <svg class="animate-spin h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                        </div>
                    </div>
                    @error('patientFiles.*') <span class="text-xs text-red-500 font-bold block mt-1">{{ $message }}</span> @enderror
                    
                    @if($patientFiles)
                    <div class="flex flex-wrap gap-2 mt-3">
                        @foreach($patientFiles as $index => $file)
                        <div class="flex items-center gap-2 px-3 py-1 bg-purple-100 text-purple-700 rounded-lg text-xs font-bold">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            {{ Str::limit($file->getClientOriginalName(), 15) }}
                            <button type="button" wire:click="$set('patientFiles.{{ $index }}', null)" class="text-red-500 hover:text-red-700">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="w-full py-3 bg-purple-600 text-white rounded-xl font-bold mt-2 shadow-lg shadow-purple-200" wire:loading.attr="disabled">
                        {{ __('Save Patient Data') }}
                    </button>
                </div>
            </form>
            @endif

            <div class="relative group">
                <div class="absolute inset-y-0 {{ app()->getLocale() === 'ar' ? 'right-0 pr-4' : 'left-0 pl-4' }} flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400 group-focus-within:text-purple-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input wire:model.live.debounce.500ms="search" type="text" placeholder="{{ __('Search by patient name or phone to book...') }}" 
                       class="w-full {{ app()->getLocale() === 'ar' ? 'pr-11 pl-12' : 'pl-11 pr-12' }} py-3.5 md:py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-purple-500 transition-all shadow-inner text-base md:text-lg">
                
                @if($search)
                <button wire:click="clearSearch" class="absolute inset-y-0 {{ app()->getLocale() === 'ar' ? 'left-0 pl-4' : 'right-0 pr-4' }} flex items-center text-gray-400 hover:text-red-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                @endif

                @if($search && count($patients) > 0)
                <div class="absolute left-0 right-0 mt-3 bg-white border border-gray-100 rounded-2xl shadow-2xl z-20 overflow-x-auto divide-y divide-gray-50 animate-fade-in-down mx-0 sm:mx-0 custom-scrollbar">
                    @foreach($patients as $patient)
                    <div class="px-0 transition-all">
                        <div class="p-4 hover:bg-gray-50 flex items-center justify-between cursor-pointer min-w-[500px]" wire:click="selectForBooking({{ $patient->id }})">
                            <div class="flex items-center gap-5">
                                <div class="w-12 h-12 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-xl">
                                    {{ mb_substr($patient->name, 0, 1) }}
                                </div>
                                <div>
                                    <span class="block font-bold text-gray-900 text-lg">{{ $patient->name }}</span>
                                    <div class="flex items-center gap-3 mt-1">
                                        <span class="text-sm text-gray-500 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                            {{ $patient->phone }}
                                        </span>
                                        <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-md">{{ __('ID') }}: #{{ $patient->id }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <div class="px-5 py-2 bg-purple-600 text-white rounded-full text-xs font-black shadow-md shadow-purple-200 flex items-center gap-2 group-hover:bg-purple-700 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                    {{ __('Record Appointment') }}
                                </div>
                                <svg class="w-5 h-5 text-purple-300 group-hover:text-purple-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </div>
                        </div>

                        @if($bookingPatientId == $patient->id)
                        <div class="bg-gradient-to-br from-white to-purple-50/20 p-6 border-t border-purple-100/50 animate-slide-in-top rounded-b-2xl">
                            <div class="flex items-center gap-4 mb-5 text-purple-900 bg-white/50 w-fit px-4 py-2.5 rounded-2xl border border-purple-100/50 shadow-sm">
                                <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center shadow-lg transform -rotate-3 group-hover:rotate-0 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-black uppercase tracking-[0.2em] opacity-50 leading-none mb-1">{{ __('Appointment Registration') }}</span>
                                    <span class="font-black text-base tracking-tight leading-none text-purple-900">{{ __('Book with Dr.') }} {{ $assignedDoctorName }}</span>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="space-y-1">
                                    <label class="text-[11px] font-bold text-purple-700 uppercase">{{ __('Time') }}</label>
                                    <input wire:model="bookingTime" type="time" class="w-full px-3 py-2 bg-white border border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 shadow-sm text-sm">
                                    @error('bookingTime') <span class="text-[10px] text-red-500 font-bold block mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[11px] font-bold text-purple-700 uppercase">{{ __('Type') }}</label>
                                    <select wire:model="bookingType" class="w-full px-3 py-2 bg-white border border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 shadow-sm text-sm">
                                        <option value="checkup">{{ __('Consultation Case') }}</option>
                                        <option value="follow_up">{{ __('Follow-up Case') }}</option>
                                    </select>
                                    @error('bookingType') <span class="text-[10px] text-red-500 font-bold block mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div class="flex items-end">
                                    <button wire:click="confirmBooking" class="w-full py-2 bg-purple-600 text-white rounded-xl font-bold shadow-lg shadow-purple-100 hover:bg-purple-700 transition-all flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        {{ __('Confirm') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                    <div class="p-3 bg-gray-50 text-center text-xs text-gray-400">
                        {{ __('To register a new patient, use the "Add Patient" button above.') }}
                    </div>
                </div>
                @elseif($search)
                <div class="absolute w-full mt-2 bg-white p-4 text-center text-gray-400 border border-gray-100 rounded-2xl shadow-xl z-20">
                    {{ __('No results found. You can add a new patient from the button above.') }}
                </div>
                @endif
            </div>
        </div>

        <!-- Appointments List -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-4 md:p-6 border-b border-gray-50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">{{ __('Appointments Schedule') }}</h4>
                        <p class="text-xs text-gray-500">{{ __('Manage and track daily patient queue') }}</p>
                    </div>
                </div>
                
                <div class="flex items-center bg-gray-50 p-1 rounded-xl w-full sm:w-auto">
                    <button wire:click="$set('selectedDate', '{{ now()->subDay()->toDateString() }}')" class="p-2 hover:bg-white rounded-lg transition-all text-gray-400 hover:text-purple-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                    <input type="date" wire:model.live="selectedDate" class="bg-transparent border-none text-sm font-bold text-gray-700 focus:ring-0 px-2 text-center w-full">
                    <button wire:click="$set('selectedDate', '{{ now()->addDay()->toDateString() }}')" class="p-2 hover:bg-white rounded-lg transition-all text-gray-400 hover:text-purple-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                    <thead class="bg-gray-50 text-gray-500 text-sm">
                        <tr>
                            <th class="px-6 py-4 font-medium w-24">{{ __('Time') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('Patient') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('Status') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($dailyAppointments as $appointment)
                        <tr class="hover:bg-gray-50/50 transition-colors {{ $appointment->status === 'seen' ? 'opacity-60' : '' }}">
                            <td class="px-6 py-4 font-bold text-purple-700">
                                {{ $appointment->scheduled_at->format('H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('patients.show', $appointment->patient_id) }}" class="block font-bold hover:text-purple-600 transition-colors">{{ $appointment->patient->name }}</a>
                                <span class="text-xs text-gray-400">{{ $appointment->patient->phone }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($appointment->status === 'checked-in')
                                    <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs rounded-full font-bold">{{ __('Checked In') }}</span>
                                @elseif($appointment->status === 'seen')
                                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs rounded-full font-bold">{{ __('Seen') }}</span>
                                @else
                                    <span class="px-3 py-1 bg-amber-100 text-amber-700 text-xs rounded-full font-bold">{{ __('Pending') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 flex items-center gap-3">
                                @if($appointment->status === 'pending')
                                <button wire:click="checkIn({{ $appointment->id }})" class="text-white bg-purple-600 px-3 py-1.5 rounded-lg font-bold text-xs hover:bg-purple-700 shadow-sm shadow-purple-200 transition-all">{{ __('Prepare') }}</button>
                                @endif
                                <button wire:click="editAppointment({{ $appointment->id }})" class="text-gray-400 hover:text-purple-600 transition-colors" title="{{ __('Edit') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <p>{{ __('No appointments scheduled for this date.') }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($dailyAppointments->hasPages())
            <div class="p-6 border-t border-gray-100">
                {{ $dailyAppointments->links() }}
            </div>
            @endif
        </div>
    </div>

    <div class="space-y-8">
        <!-- Quick Stats -->
        <div class="bg-gradient-to-br from-purple-600 to-indigo-700 text-white p-8 rounded-[2rem] shadow-xl shadow-purple-200">
            <h4 class="opacity-80 mb-2 font-medium">{{ __('Total Bookings Today') }}</h4>
            <div class="text-5xl font-bold mb-6 tabular-nums">{{ $stats->total }}</div>
            <div class="space-y-3">
                <div class="flex items-center justify-between text-sm bg-white/10 p-3 rounded-xl border border-white/10">
                    <span>{{ __('Waiting for Preparation') }}</span>
                    <span class="font-bold">{{ $stats->pending }}</span>
                </div>
                <div class="flex items-center justify-between text-sm bg-white/10 p-3 rounded-xl border border-white/10">
                    <span>{{ __('Prepared Cases') }}</span>
                    <span class="font-bold">{{ $stats->prepared }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Appointment Modal -->
    @if($editingAppointmentId)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <div class="bg-white rounded-3xl p-6 shadow-2xl max-w-md w-full border border-purple-100">
            <h3 class="text-xl font-bold text-gray-900 mb-6">{{ __('Edit Appointment') }}</h3>
            
            <form wire:submit="saveAppointment" class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">{{ __('Select Doctor') }}</label>
                    <select wire:model="editDoctorId" class="w-full px-4 py-2 bg-gray-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500">
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}">{{ __('Dr.') }} {{ $doctor->name }}</option>
                        @endforeach
                    </select>
                    @error('editDoctorId') <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">{{ __('Time') }}</label>
                    <input type="time" wire:model="editTime" class="w-full px-4 py-2 bg-gray-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500">
                    @error('editTime') <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div class="flex items-center gap-3 pt-4">
                    <button type="button" wire:click="cancelEdit" class="flex-1 px-4 py-2 border border-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-50 transition-colors">{{ __('Cancel') }}</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-xl font-bold shadow-lg shadow-purple-200 hover:bg-purple-700 transition-colors">{{ __('Save Changes') }}</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>

