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
    public $showCreatePatientForm = false;
    public $newPatientName = '';
    public $newPatientPhone = '';
    public $selectedPatient = null;
    public $bookingDate = '';
    public $bookingTime = '';
    public $bookingType = 'checkup';
    public $bookingDoctorId = '';
    public $splitByType = false;

    public function mount()
    {
        $this->dateFilter = now()->format('Y-m-d');
        $this->bookingDate = now()->format('Y-m-d');
        $this->bookingTime = now()->addMinutes(15)->format('H:i');
        if (auth()->user()->isDoctor()) {
            $this->bookingDoctorId = auth()->id();
        } elseif (auth()->user()->isSecretary()) {
            $this->bookingDoctorId = auth()->user()->doctor_id;
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
        $this->showCreatePatientForm = false;
        $this->newPatientName = '';
        $this->newPatientPhone = '';
    }

    public function quickCreateAndSelect()
    {
        $this->validate([
            'newPatientName' => 'required|min:3',
            'newPatientPhone' => 'required|numeric',
        ]);

        $doctorId = auth()->user()->isDoctor() ? auth()->id() : auth()->user()->doctor_id;

        $patient = app(\App\Services\PatientService::class)->createPatient([
            'name' => $this->newPatientName,
            'phone' => $this->newPatientPhone,
            'doctor_id' => $doctorId,
        ]);

        $this->selectedPatient = $patient;
        $this->showCreatePatientForm = false;
        $this->newPatientName = '';
        $this->newPatientPhone = '';
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
            'scheduled_at' => Carbon::parse($this->bookingDate . ' ' . $this->bookingTime),
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

        if (auth()->user()->isDoctor()) {
            $query->where('doctor_id', auth()->id())
                  ->whereHas('patient', fn($q) => $q->where('doctor_id', auth()->id()));
        } elseif (auth()->user()->isSecretary()) {
            $query->where('doctor_id', auth()->user()->doctor_id)
                  ->whereHas('patient', fn($q) => $q->where('doctor_id', auth()->user()->doctor_id));
        } elseif ($this->doctorFilter) {
            $query->where('doctor_id', $this->doctorFilter)
                  ->whereHas('patient', fn($q) => $q->where('doctor_id', $this->doctorFilter));
        }

        if ($this->patientSearch) {
            $query->whereHas('patient', function($q) {
                $q->where('name', 'like', '%' . $this->patientSearch . '%')
                  ->orWhere('phone', 'like', '%' . $this->patientSearch . '%');
            });
        }

        return view('livewire.shared.appointments-list', [
            'appointments' => $query->paginate(20),
            'doctors' => User::where('role', 'doctor')->get(),
            'patients' => $this->patientSearch 
                ? \App\Models\Patient::where(function($q) {
                    $q->where('name', 'like', '%' . $this->patientSearch . '%')
                      ->orWhere('phone', 'like', '%' . $this->patientSearch . '%');
                })
                ->when(!auth()->user()->isAdmin(), function($q) {
                    $doctorId = auth()->user()->isDoctor() ? auth()->id() : auth()->user()->doctor_id;
                    $q->where(function($sq) use ($doctorId) {
                        $sq->where('doctor_id', $doctorId)
                          ->orWhereHas('appointments', fn($query) => $query->where('doctor_id', $doctorId))
                          ->orWhereHas('visits', fn($query) => $query->where('doctor_id', $doctorId));
                    });
                })
                ->limit(5)->get() 
                : [],
        ])->layout('layouts.clinic', ['title' => __('Appointments Management')]);
    }
};
?>

<div class="space-y-6" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
    <!-- Rapid Booking Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showBookingModal): ?>
    <div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processElementKey('booking-modal-container', get_defined_vars()); ?>wire:key="booking-modal-container" class="fixed inset-0 z-[60] flex justify-center items-start overflow-y-auto p-4 md:p-10">
        <div wire:click="closeBookingModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="bg-white rounded-[2rem] w-full max-w-lg shadow-2xl relative overflow-hidden animate-zoom-in my-8">
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
            
            <div class="p-8 text-right">
                <div class="flex items-center justify-between mb-8 flex-row-reverse">
                    <h3 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <?php echo e(__('Book Appointment')); ?>

                    </h3>
                    <button wire:click="closeBookingModal" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="space-y-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$selectedPatient): ?>
                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest block"><?php echo e(__('Search Patient')); ?></label>
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.300ms="patientSearch" 
                                       placeholder="<?php echo e(__('Search by patient name or phone...')); ?>"
                                       class="w-full bg-slate-50 border-gray-200 rounded-2xl py-4 px-5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($patients)): ?>
                                    <div class="absolute z-[70] w-full mt-2 bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden py-2">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $patients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                            <button wire:click="selectPatient(<?php echo e($patient->id); ?>)" class="w-full px-5 py-3 hover:bg-slate-50 flex items-center justify-between transition-colors text-right">
                                                <div class="text-right">
                                                    <p class="font-bold text-gray-900"><?php echo e($patient->name); ?></p>
                                                    <p class="text-xs text-gray-500"><?php echo e($patient->phone); ?></p>
                                                </div>
                                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                            </button>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selectedPatient'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-rose-500 text-xs font-bold"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($patientSearch && empty($patients)): ?>
                                <div class="mt-4 p-4 bg-purple-50 rounded-2xl border border-purple-100 text-center">
                                    <p class="text-sm text-purple-800 font-bold mb-3"><?php echo e(__('Patient not found.')); ?></p>
                                    <button type="button" wire:click="$toggle('showCreatePatientForm')" class="px-4 py-2 bg-purple-600 text-white rounded-xl text-xs font-bold shadow-md hover:bg-purple-700 transition-all">
                                        <?php echo e($showCreatePatientForm ? __('Cancel') : __('Add as New Patient')); ?>

                                    </button>
                                </div>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showCreatePatientForm): ?>
                                    <div class="mt-4 space-y-4 p-4 bg-white rounded-2xl border border-purple-100 animate-slide-in-top">
                                        <div class="space-y-1">
                                            <label class="text-[10px] font-black text-purple-600 uppercase tracking-widest"><?php echo e(__('Patient Name')); ?></label>
                                            <input type="text" wire:model="newPatientName" class="w-full bg-slate-50 border-gray-200 rounded-xl py-2 px-3 text-sm focus:ring-2 focus:ring-purple-500">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newPatientName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-500 font-bold block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[10px] font-black text-purple-600 uppercase tracking-widest"><?php echo e(__('Phone Number')); ?></label>
                                            <input type="text" wire:model="newPatientPhone" class="w-full bg-slate-50 border-gray-200 rounded-xl py-2 px-3 text-sm focus:ring-2 focus:ring-purple-500">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newPatientPhone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-500 font-bold block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <button type="button" wire:click="quickCreateAndSelect" class="w-full py-2 bg-emerald-600 text-white rounded-xl text-xs font-black shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition-all">
                                            <?php echo e(__('Create & Select')); ?>

                                        </button>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-4 bg-indigo-50 rounded-2xl border border-indigo-100 flex items-center justify-between flex-row-reverse">
                            <div class="flex items-center gap-4 flex-row-reverse">
                                <div class="w-12 h-12 bg-white text-indigo-600 rounded-xl flex items-center justify-center font-black shadow-sm">
                                    <?php echo e(mb_substr($selectedPatient->name, 0, 1)); ?>

                                </div>
                                <div class="text-right">
                                    <p class="font-black text-indigo-900"><?php echo e($selectedPatient->name); ?></p>
                                    <p class="text-xs text-indigo-600 font-bold"><?php echo e($selectedPatient->phone); ?></p>
                                </div>
                            </div>
                            <button wire:click="$set('selectedPatient', null)" class="text-xs font-black text-rose-600 hover:text-rose-700 underline uppercase tracking-tighter">
                                <?php echo e(__('Change')); ?>

                            </button>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest block"><?php echo e(__('Date')); ?></label>
                            <input type="date" wire:model="bookingDate" class="w-full bg-slate-50 border-gray-200 rounded-2xl py-3 px-4 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest block"><?php echo e(__('Time')); ?></label>
                            <input type="time" wire:model="bookingTime" class="w-full bg-slate-50 border-gray-200 rounded-2xl py-3 px-4 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-500 uppercase tracking-widest block"><?php echo e(__('Visit Type')); ?></label>
                        <select wire:model="bookingType" class="w-full bg-slate-50 border-gray-200 rounded-2xl py-3 px-4 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                            <option value="checkup"><?php echo e(__('Checkup')); ?></option>
                            <option value="follow_up"><?php echo e(__('Follow-up')); ?></option>
                        </select>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isAdmin()): ?>
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-500 uppercase tracking-widest block"><?php echo e(__('Doctor')); ?></label>
                        <select wire:model="bookingDoctorId" class="w-full bg-slate-50 border-gray-200 rounded-2xl py-3 px-4 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                            <option value=""><?php echo e(__('Choose a doctor...')); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <option value="<?php echo e($doctor->id); ?>"><?php echo e(__('Dr.')); ?> <?php echo e($doctor->name); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['bookingDoctorId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-rose-500 text-xs font-bold"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="pt-4 flex gap-4">
                        <button type="button" wire:click="closeBookingModal" class="flex-1 py-4 px-6 bg-slate-100 hover:bg-slate-200 text-slate-700 font-black rounded-2xl transition-all uppercase tracking-widest text-xs">
                            <?php echo e(__('Cancel')); ?>

                        </button>
                        <button type="button" wire:click="confirmBooking" class="flex-[2] py-4 px-6 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-black rounded-2xl shadow-xl shadow-indigo-100 transition-all uppercase tracking-widest text-xs hover:-translate-y-1">
                            <?php echo e(__('Confirm Booking')); ?>

                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <!-- Filters -->
    <div class="bg-white p-4 md:p-6 rounded-3xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow mb-8">
        <div class="flex flex-col gap-6 w-full">
            <div class="flex flex-wrap lg:flex-nowrap items-end gap-4 md:gap-5 w-full">
            <!-- Patient Search Filter -->
            <div class="w-full sm:w-[calc(50%-10px)] lg:w-auto flex-1 space-y-1.5 focus-within:text-purple-600 transition-colors">
                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider"><?php echo e(__('Search Patient')); ?></label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="patientSearch" placeholder="<?php echo e(__('Name or phone...')); ?>" class="w-full bg-slate-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm py-2.5 px-4 shadow-inner transition-all hover:border-purple-300">
                </div>
            </div>

            <!-- Date Filter -->
            <div class="w-full sm:w-[calc(50%-10px)] lg:w-auto flex-1 space-y-1.5 focus-within:text-purple-600 transition-colors">
                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider"><?php echo e(__('Date')); ?></label>
                <div class="relative">
                    <input type="date" wire:model.live="dateFilter" class="w-full bg-slate-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm py-2.5 px-4 shadow-inner transition-all hover:border-purple-300">
                </div>
            </div>
            
            <!-- Status Filter -->
            <div class="w-full sm:w-[calc(50%-10px)] lg:w-auto flex-1 space-y-1.5 focus-within:text-purple-600 transition-colors">
                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider"><?php echo e(__('Status')); ?></label>
                <select wire:model.live="statusFilter" class="w-full bg-slate-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm py-2.5 px-4 shadow-inner transition-all hover:border-purple-300">
                    <option value=""><?php echo e(__('All Statuses')); ?></option>
                    <option value="pending"><?php echo e(__('Pending (Waitlist)')); ?></option>
                    <option value="checked-in"><?php echo e(__('Checked In (Processing)')); ?></option>
                    <option value="seen"><?php echo e(__('Seen (Completed)')); ?></option>
                </select>
            </div>

            <!-- Doctor Filter -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!auth()->user()->isDoctor() && !auth()->user()->isSecretary()): ?>
            <div class="w-full sm:w-[calc(50%-10px)] lg:w-auto flex-1 space-y-1.5 focus-within:text-purple-600 transition-colors">
                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider"><?php echo e(__('Doctor')); ?></label>
                <select wire:model.live="doctorFilter" class="w-full bg-slate-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm py-2.5 px-4 shadow-inner transition-all hover:border-purple-300">
                    <option value=""><?php echo e(__('All Doctors')); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <option value="<?php echo e($doctor->id); ?>"><?php echo e(__('Dr.')); ?> <?php echo e($doctor->name); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <!-- View Mode Filter -->
            <div class="w-full sm:w-[calc(50%-10px)] lg:w-auto flex-1 space-y-1.5 focus-within:text-purple-600 transition-colors flex flex-col justify-end">
                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2 block"><?php echo e(__('View Mode')); ?></label>
                <div class="flex items-center gap-1 bg-slate-50 border border-gray-200 rounded-xl p-1 shadow-inner h-[42px]">
                    <button wire:click="$set('splitByType', false)" class="flex-1 py-1 px-2 rounded-lg text-xs font-bold transition-all <?php echo e(!$splitByType ? 'bg-white text-purple-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'); ?>">
                        <?php echo e(__('Combined')); ?>

                    </button>
                    <button wire:click="$set('splitByType', true)" class="flex-1 py-1 px-2 rounded-lg text-xs font-bold transition-all <?php echo e($splitByType ? 'bg-white text-purple-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'); ?>">
                        <?php echo e(__('Split Tables')); ?>

                    </button>
                </div>
            </div>
            
            <!-- Action Button -->
            <div class="w-full sm:w-[calc(50%-10px)] lg:w-auto flex-1 mt-2 lg:mt-0">
                <button wire:click="openBookingModal" class="w-full h-[42px] px-6 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-100 transition-all flex items-center justify-center gap-2 text-sm hover:-translate-y-0.5 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <?php echo e(__('Book Appointment')); ?>

                </button>
            </div>
            </div>
        </div>
    </div>

    <!-- Appointments List Table -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($splitByType): ?>
        <div class="space-y-8">
            <!-- Checkups -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-indigo-900"><?php echo e(__('Checkups')); ?></h3>
                    <span class="ml-auto bg-indigo-200 text-indigo-800 text-xs font-bold px-2 py-1 rounded-full"><?php echo e($appointments->where('type', 'checkup')->count()); ?></span>
                </div>
                <div class="overflow-x-auto">
                    <?php echo $__env->make('livewire.shared.partials.appointments-table', ['appointments' => $appointments->where('type', 'checkup'), 'tableId' => 'checkups-table'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>

            <!-- Follow-ups -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-teal-50 px-6 py-4 border-b border-teal-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-teal-100 text-teal-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-teal-900"><?php echo e(__('Follow-ups')); ?></h3>
                    <span class="ml-auto bg-teal-200 text-teal-800 text-xs font-bold px-2 py-1 rounded-full"><?php echo e($appointments->where('type', 'follow_up')->count()); ?></span>
                </div>
                <div class="overflow-x-auto">
                    <?php echo $__env->make('livewire.shared.partials.appointments-table', ['appointments' => $appointments->where('type', 'follow_up'), 'tableId' => 'followups-table'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <?php echo $__env->make('livewire.shared.partials.appointments-table', ['appointments' => $appointments, 'tableId' => 'unified-table'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($appointments->hasPages()): ?>
    <div class="mt-6 p-6 border-t border-gray-50 bg-white rounded-3xl shadow-sm border border-gray-100">
        <?php echo e($appointments->links()); ?>

    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div>
</div>
<?php /**PATH C:\Bola\Clinova\resources\views/livewire/shared/appointments-list.blade.php ENDPATH**/ ?>