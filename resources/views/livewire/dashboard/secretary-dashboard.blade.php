<?php
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Patient;
use App\Services\AppointmentService;
use App\Models\Appointment;
use App\Services\PatientService;
use Carbon\Carbon;

new class extends Component
{
    use WithPagination;

    public $search = '';
    public $showAddPatient = false;
    public $selectedDate;
    
    // Assigned Doctor
    public $assignedDoctorId;
    public $assignedDoctorName;

    // Booking Details
    public $bookingPatientId = null;
    public $bookingTime = '';
    public $bookingType = 'consultation';

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
                ->whereDate('scheduled_at', $date)
                ->orderBy('scheduled_at', 'asc')
                ->orderBy('queue_order', 'asc')
                ->paginate(15),
            'dayCount' => Appointment::where('doctor_id', $this->assignedDoctorId)
                ->whereDate('scheduled_at', $date)->count(),
            'pendingCount' => Appointment::where('doctor_id', $this->assignedDoctorId)
                ->whereDate('scheduled_at', $date)->where('status', 'pending')->count(),
            'preparedCount' => Appointment::where('doctor_id', $this->assignedDoctorId)
                ->whereDate('scheduled_at', $date)->where('status', 'checked-in')->count(),
            'doctors' => \App\Models\User::where('role', 'doctor')->get(),
        ];
    }

    public function createPatient()
    {
        $this->validate([
            'name' => 'required|min:3',
            'phone' => 'required|numeric',
        ]);

        $patient = app(PatientService::class)->createPatient([
            'name' => $this->name,
            'phone' => $this->phone,
            'age' => $this->age,
            'weight' => $this->weight,
            'address' => $this->address,
            'doctor_id' => $this->assignedDoctorId,
        ]);

        $this->reset(['name', 'phone', 'age', 'weight', 'address', 'showAddPatient']);
        $this->search = $patient->name;
    }

    public function checkIn($appointmentId)
    {
        $appointment = Appointment::findOrFail($appointmentId);
        app(AppointmentService::class)->updateStatus($appointment, 'checked-in');
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
            'bookingType' => 'required|in:consultation,followup',
        ]);

        $scheduledAt = Carbon::parse($this->selectedDate . ' ' . $this->bookingTime);

        app(AppointmentService::class)->bookAppointment([
            'patient_id' => $this->bookingPatientId,
            'doctor_id' => $this->assignedDoctorId,
            'scheduled_at' => $scheduledAt,
            'type' => $this->bookingType,
            'status' => 'pending'
        ]);

        $this->clearSearch();
        $this->resetPage();
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
    </style>
    @endPushOnce
    <div class="lg:col-span-2 space-y-8">
        <!-- Patient Control Center -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-lg">{{ __('Patients & Booking Management') }}</h3>
                <button wire:click="$toggle('showAddPatient')" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-base font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
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
                <div class="md:col-span-2">
                    <button type="submit" class="w-full py-3 bg-purple-600 text-white rounded-xl font-bold mt-2 shadow-lg shadow-purple-200">{{ __('Save Patient') }}</button>
                </div>
            </form>
            @endif

            <div class="relative group">
                <div class="absolute inset-y-0 {{ app()->getLocale() === 'ar' ? 'right-0 pr-4' : 'left-0 pl-4' }} flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400 group-focus-within:text-purple-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input wire:model.live="search" type="text" placeholder="{{ __('Search by patient name or phone to book...') }}" 
                       class="w-full {{ app()->getLocale() === 'ar' ? 'pr-11 pl-12' : 'pl-11 pr-12' }} py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-purple-500 transition-all shadow-inner text-lg">
                
                @if($search)
                <button wire:click="clearSearch" class="absolute inset-y-0 {{ app()->getLocale() === 'ar' ? 'left-0 pl-4' : 'right-0 pr-4' }} flex items-center text-gray-400 hover:text-red-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                @endif

                @if($search && count($patients) > 0)
                <div class="absolute w-full mt-3 bg-white border border-gray-100 rounded-3xl shadow-2xl z-20 overflow-hidden divide-y divide-gray-50 animate-fade-in-down">
                    @foreach($patients as $patient)
                    <div class="p-0 transition-all">
                        <div class="p-4 hover:bg-gray-50 flex items-center justify-between cursor-pointer" wire:click="selectForBooking({{ $patient->id }})">
                            <div class="flex items-center gap-4">
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
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-medium text-purple-600 group-hover:underline">{{ __('Record Appointment') }}</span>
                                <svg class="w-5 h-5 text-gray-300 transform translate-x-0 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </div>
                        </div>

                        @if($bookingPatientId == $patient->id)
                        <div class="bg-purple-50/50 p-5 border-t border-purple-100 animate-slide-in-top">
                            <div class="flex items-center gap-3 mb-4 text-purple-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span class="font-bold">{{ __('Book with Dr.') }} {{ $assignedDoctorName }}</span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="space-y-1">
                                    <label class="text-[11px] font-bold text-purple-700 uppercase">{{ __('Time') }}</label>
                                    <input wire:model="bookingTime" type="time" class="w-full px-3 py-2 bg-white border border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 shadow-sm text-sm">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[11px] font-bold text-purple-700 uppercase">{{ __('Type') }}</label>
                                    <select wire:model="bookingType" class="w-full px-3 py-2 bg-white border border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 shadow-sm text-sm">
                                        <option value="consultation">{{ __('Consultation Case') }}</option>
                                        <option value="followup">{{ __('Follow-up Case') }}</option>
                                    </select>
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

        <!-- Daily Schedule (Calendar View) -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden text-right">
            <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
                <h3 class="font-bold text-lg flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    {{ __('Daily Schedule') }}
                </h3>
                
                <div class="flex items-center gap-3 bg-gray-50 p-2 rounded-xl border border-gray-200">
                    <button wire:click="setDate('{{ \Carbon\Carbon::parse($selectedDate)->subDay()->format('Y-m-d') }}')" class="p-2 hover:bg-white rounded-lg transition-colors text-gray-500 hover:text-purple-600 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                    
                    <input type="date" wire:model.live="selectedDate" class="bg-transparent border-none text-sm font-bold text-gray-700 focus:ring-0 cursor-pointer">
                    
                    <button wire:click="setDate('{{ \Carbon\Carbon::parse($selectedDate)->addDay()->format('Y-m-d') }}')" class="p-2 hover:bg-white rounded-lg transition-colors text-gray-500 hover:text-purple-600 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-right" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
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
            <div class="text-5xl font-bold mb-6 tabular-nums">{{ $dayCount }}</div>
            <div class="space-y-3">
                <div class="flex items-center justify-between text-sm bg-white/10 p-3 rounded-xl border border-white/10">
                    <span>{{ __('Waiting for Preparation') }}</span>
                    <span class="font-bold">{{ $pendingCount }}</span>
                </div>
                <div class="flex items-center justify-between text-sm bg-white/10 p-3 rounded-xl border border-white/10">
                    <span>{{ __('Prepared Cases') }}</span>
                    <span class="font-bold">{{ $preparedCount }}</span>
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

