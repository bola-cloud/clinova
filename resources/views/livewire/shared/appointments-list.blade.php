<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;

new class extends Component
{
    use WithPagination;

    public $dateFilter = '';
    public $statusFilter = '';
    public $doctorFilter = '';
    public $showBookingModal = false;
    public $patientSearch = '';
    public $selectedPatient = null;
    public $bookingDate = '';
    public $bookingType = 'checkup';
    public $bookingDoctorId = '';
    public $splitByType = false;

    public function mount()
    {
        $this->dateFilter = now()->format('Y-m-d');
        $this->bookingDate = now()->format('Y-m-d');
        if (auth()->user()->role === 'doctor') {
            $this->bookingDoctorId = auth()->id();
        }
    }

    public function updatedPatientSearch()
    {
        $this->selectedPatient = null;
    }

    public function selectPatient($patientId)
    {
        $this->selectedPatient = \App\Models\Patient::find($patientId);
        $this->patientSearch = '';
    }

    public function openBookingModal()
    {
        $this->showBookingModal = true;
        $this->bookingDate = $this->dateFilter ?: now()->format('Y-m-d');
    }

    public function closeBookingModal()
    {
        $this->showBookingModal = false;
        $this->selectedPatient = null;
        $this->patientSearch = '';
    }

    public function confirmBooking()
    {
        $this->validate([
            'selectedPatient' => 'required',
            'bookingDate' => 'required|date',
            'bookingType' => 'required|in:checkup,follow_up',
            'bookingDoctorId' => 'required|exists:users,id',
        ], [
            'selectedPatient.required' => __('Please select a patient'),
        ]);

        app(\App\Services\AppointmentService::class)->bookAppointment([
            'patient_id' => $this->selectedPatient->id,
            'doctor_id' => $this->bookingDoctorId,
            'scheduled_at' => $this->bookingDate,
            'type' => $this->bookingType,
        ]);

        $this->closeBookingModal();
        session()->flash('success', __('Patient appointment booked successfully.'));
    }

    public function updatingDateFilter() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }
    public function updatingDoctorFilter() { $this->resetPage(); }

    public function updateQueueOrder($orderedIds)
    {
        app(\App\Services\AppointmentService::class)->reorderQueue($orderedIds);
    }

    public function markAsSeen($id)
    {
        $appointment = \App\Models\Appointment::find($id);
        if ($appointment) {
            app(\App\Services\AppointmentService::class)->updateStatus($appointment, 'seen');
            $this->resetPage();
        }
    }

    public function render(): mixed
    {
        $query = Appointment::with(['patient', 'doctor'])
            ->orderBy('queue_order', 'asc')
            ->orderBy('scheduled_at', 'asc');

        if ($this->dateFilter) {
            $query->whereDate('scheduled_at', Carbon::parse($this->dateFilter));
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if (auth()->user()->role === 'doctor') {
            $query->where('doctor_id', auth()->id());
        } elseif ($this->doctorFilter) {
            $query->where('doctor_id', $this->doctorFilter);
        }

        return view('livewire.shared.appointments-list', [
            'appointments' => $query->paginate(20),
            'doctors' => User::where('role', 'doctor')->get(),
            'patients' => $this->patientSearch 
                ? \App\Models\Patient::where(function($q) {
                    $q->where('name', 'like', '%' . $this->patientSearch . '%')
                      ->orWhere('phone', 'like', '%' . $this->patientSearch . '%');
                })
                ->when(auth()->user()->role === 'doctor', function($q) {
                    $q->whereHas('appointments', function($query) {
                        $query->where('doctor_id', auth()->id());
                    })->orWhereHas('visits', function($query) {
                        $query->where('doctor_id', auth()->id());
                    });
                })
                ->limit(5)->get() 
                : [],
        ])->layout('layouts.clinic', ['title' => __('Appointments Management')]);
    }
};
?>

<div class="space-y-6" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Rapid Booking Modal -->
    @if($showBookingModal)
    <div wire:key="booking-modal-container" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div wire:click="closeBookingModal" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="bg-white rounded-[2rem] w-full max-w-lg shadow-2xl relative overflow-hidden animate-zoom-in">
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
            
            <div class="p-8 text-right">
                <div class="flex items-center justify-between mb-8 flex-row-reverse">
                    <h3 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        {{ __('Book Appointment') }}
                    </h3>
                    <button wire:click="closeBookingModal" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="space-y-6">
                    @if(!$selectedPatient)
                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest block">{{ __('Search Patient') }}</label>
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.300ms="patientSearch" 
                                       placeholder="{{ __('Search by patient name or phone...') }}"
                                       class="w-full bg-slate-50 border-gray-200 rounded-2xl py-4 px-5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                                
                                @if(!empty($patients))
                                    <div class="absolute z-[70] w-full mt-2 bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden py-2">
                                        @foreach($patients as $patient)
                                            <button wire:click="selectPatient({{ $patient->id }})" class="w-full px-5 py-3 hover:bg-slate-50 flex items-center justify-between transition-colors text-right">
                                                <div class="text-right">
                                                    <p class="font-bold text-gray-900">{{ $patient->name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $patient->phone }}</p>
                                                </div>
                                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            @error('selectedPatient') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <div class="p-4 bg-indigo-50 rounded-2xl border border-indigo-100 flex items-center justify-between flex-row-reverse">
                            <div class="flex items-center gap-4 flex-row-reverse">
                                <div class="w-12 h-12 bg-white text-indigo-600 rounded-xl flex items-center justify-center font-black shadow-sm">
                                    {{ mb_substr($selectedPatient->name, 0, 1) }}
                                </div>
                                <div class="text-right">
                                    <p class="font-black text-indigo-900">{{ $selectedPatient->name }}</p>
                                    <p class="text-xs text-indigo-600 font-bold">{{ $selectedPatient->phone }}</p>
                                </div>
                            </div>
                            <button wire:click="$set('selectedPatient', null)" class="text-xs font-black text-rose-600 hover:text-rose-700 underline uppercase tracking-tighter">
                                {{ __('Change') }}
                            </button>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest block">{{ __('Date') }}</label>
                            <input type="date" wire:model="bookingDate" class="w-full bg-slate-50 border-gray-200 rounded-2xl py-3 px-4 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest block">{{ __('Visit Type') }}</label>
                            <select wire:model="bookingType" class="w-full bg-slate-50 border-gray-200 rounded-2xl py-3 px-4 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                                <option value="checkup">{{ __('Checkup') }}</option>
                                <option value="follow_up">{{ __('Follow-up') }}</option>
                            </select>
                        </div>
                    </div>

                    @if(auth()->user()->role !== 'doctor')
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-500 uppercase tracking-widest block">{{ __('Doctor') }}</label>
                        <select wire:model="bookingDoctorId" class="w-full bg-slate-50 border-gray-200 rounded-2xl py-3 px-4 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                            <option value="">{{ __('Choose a doctor...') }}</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}">{{ __('Dr.') }} {{ $doctor->name }}</option>
                            @endforeach
                        </select>
                        @error('bookingDoctorId') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
                    </div>
                    @endif

                    <div class="pt-4 flex gap-4">
                        <button type="button" wire:click="closeBookingModal" class="flex-1 py-4 px-6 bg-slate-100 hover:bg-slate-200 text-slate-700 font-black rounded-2xl transition-all uppercase tracking-widest text-xs">
                            {{ __('Cancel') }}
                        </button>
                        <button type="button" wire:click="confirmBooking" class="flex-[2] py-4 px-6 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-black rounded-2xl shadow-xl shadow-indigo-100 transition-all uppercase tracking-widest text-xs hover:-translate-y-1">
                            {{ __('Confirm Booking') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    <!-- Filters -->
    <div class="bg-white p-4 md:p-6 rounded-3xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow mb-8">
        <div class="flex flex-col gap-6 w-full">
            <div class="flex flex-wrap lg:flex-nowrap items-end gap-4 md:gap-5 w-full">
            <!-- Date Filter -->
            <div class="w-full sm:w-[calc(50%-10px)] lg:w-auto flex-1 space-y-1.5 focus-within:text-purple-600 transition-colors">
                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">{{ __('Date') }}</label>
                <div class="relative">
                    <input type="date" wire:model.live="dateFilter" class="w-full bg-slate-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm py-2.5 px-4 shadow-inner transition-all hover:border-purple-300">
                </div>
            </div>
            
            <!-- Status Filter -->
            <div class="w-full sm:w-[calc(50%-10px)] lg:w-auto flex-1 space-y-1.5 focus-within:text-purple-600 transition-colors">
                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">{{ __('Status') }}</label>
                <select wire:model.live="statusFilter" class="w-full bg-slate-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm py-2.5 px-4 shadow-inner transition-all hover:border-purple-300">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="pending">{{ __('Pending (Waitlist)') }}</option>
                    <option value="checked-in">{{ __('Checked In (Processing)') }}</option>
                    <option value="seen">{{ __('Seen (Completed)') }}</option>
                </select>
            </div>

            <!-- Doctor Filter -->
            @if(auth()->user()->role !== 'doctor')
            <div class="w-full sm:w-[calc(50%-10px)] lg:w-auto flex-1 space-y-1.5 focus-within:text-purple-600 transition-colors">
                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">{{ __('Doctor') }}</label>
                <select wire:model.live="doctorFilter" class="w-full bg-slate-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm py-2.5 px-4 shadow-inner transition-all hover:border-purple-300">
                    <option value="">{{ __('All Doctors') }}</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}">{{ __('Dr.') }} {{ $doctor->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- View Mode Filter -->
            <div class="w-full sm:w-[calc(50%-10px)] lg:w-auto flex-1 space-y-1.5 focus-within:text-purple-600 transition-colors flex flex-col justify-end">
                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2 block">{{ __('View Mode') }}</label>
                <div class="flex items-center gap-1 bg-slate-50 border border-gray-200 rounded-xl p-1 shadow-inner h-[42px]">
                    <button wire:click="$set('splitByType', false)" class="flex-1 py-1 px-2 rounded-lg text-xs font-bold transition-all {{ !$splitByType ? 'bg-white text-purple-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        {{ __('Combined') }}
                    </button>
                    <button wire:click="$set('splitByType', true)" class="flex-1 py-1 px-2 rounded-lg text-xs font-bold transition-all {{ $splitByType ? 'bg-white text-purple-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        {{ __('Split Tables') }}
                    </button>
                </div>
            </div>
            
            <!-- Action Button -->
            <div class="w-full sm:w-[calc(50%-10px)] lg:w-auto flex-1 mt-2 lg:mt-0">
                <button wire:click="openBookingModal" class="w-full h-[42px] px-6 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-100 transition-all flex items-center justify-center gap-2 text-sm hover:-translate-y-0.5 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    {{ __('Book Appointment') }}
                </button>
            </div>
            </div>
        </div>
    </div>

    <!-- Appointments List Table -->
    @if($splitByType)
        <div class="space-y-8">
            <!-- Checkups -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-indigo-900">{{ __('Checkups') }}</h3>
                    <span class="ml-auto bg-indigo-200 text-indigo-800 text-xs font-bold px-2 py-1 rounded-full">{{ $appointments->where('type', 'checkup')->count() }}</span>
                </div>
                <div class="overflow-x-auto">
                    @include('livewire.shared.partials.appointments-table', ['appointments' => $appointments->where('type', 'checkup'), 'tableId' => 'checkups-table'])
                </div>
            </div>

            <!-- Follow-ups -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-teal-50 px-6 py-4 border-b border-teal-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-teal-100 text-teal-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-teal-900">{{ __('Follow-ups') }}</h3>
                    <span class="ml-auto bg-teal-200 text-teal-800 text-xs font-bold px-2 py-1 rounded-full">{{ $appointments->where('type', 'follow_up')->count() }}</span>
                </div>
                <div class="overflow-x-auto">
                    @include('livewire.shared.partials.appointments-table', ['appointments' => $appointments->where('type', 'follow_up'), 'tableId' => 'followups-table'])
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                @include('livewire.shared.partials.appointments-table', ['appointments' => $appointments, 'tableId' => 'unified-table'])
            </div>
        </div>
    @endif

    @if($appointments->hasPages())
    <div class="mt-6 p-6 border-t border-gray-50 bg-white rounded-3xl shadow-sm border border-gray-100">
        {{ $appointments->links() }}
    </div>
    @endif

</div>
</div>
