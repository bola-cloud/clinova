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

?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
    <?php if (! $__env->hasRenderedOnce('6febb901-e746-4e41-9137-eb789bf63a53')): $__env->markAsRenderedOnce('6febb901-e746-4e41-9137-eb789bf63a53');
$__env->startPush('styles'); ?>
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
    <?php $__env->stopPush(); endif; ?>
    <div class="lg:col-span-2 space-y-8">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
        <div class="p-4 bg-emerald-50 text-emerald-700 rounded-2xl border border-emerald-100 flex items-center gap-3 animate-fade-in-down">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-bold text-sm"><?php echo e(session('message')); ?></span>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
        <div class="p-4 bg-rose-50 text-rose-700 rounded-2xl border border-rose-100 flex items-center gap-3 animate-fade-in-down">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-bold text-sm"><?php echo e(session('error')); ?></span>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Patient Control Center -->
        <div class="bg-white p-4 md:p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
                <h3 class="font-bold text-lg"><?php echo e(__('Patients & Booking Management')); ?></h3>
                <button wire:click="$toggle('showAddPatient')" class="w-full sm:w-auto px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-base font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
                    <?php echo e($showAddPatient ? __('Close') : __('New Patient') . ' +'); ?>

                </button>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showAddPatient): ?>
            <form wire:submit="createPatient" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8 p-4 bg-purple-50 rounded-2xl border border-purple-100">
                <div class="space-y-1">
                    <label class="text-sm font-bold text-purple-900"><?php echo e(__('Full Name')); ?> *</label>
                    <input wire:model="name" type="text" class="w-full px-4 py-2 rounded-lg border-none focus:ring-2 focus:ring-purple-500">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-sm text-red-500 font-bold mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-bold text-purple-900"><?php echo e(__('Phone')); ?> *</label>
                    <input wire:model="phone" type="text" class="w-full px-4 py-2 rounded-lg border-none focus:ring-2 focus:ring-purple-500">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-sm text-red-500 font-bold mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-bold text-purple-900"><?php echo e(__('Age')); ?></label>
                    <input wire:model="age" type="number" class="w-full px-4 py-2 rounded-lg border-none focus:ring-2 focus:ring-purple-500">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['age'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-sm text-red-500 font-bold mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-bold text-purple-900"><?php echo e(__('Address')); ?></label>
                    <input wire:model="address" type="text" class="w-full px-4 py-2 rounded-lg border-none focus:ring-2 focus:ring-purple-500">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-sm text-red-500 font-bold mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="md:col-span-2 space-y-2">
                    <label class="text-sm font-bold text-purple-900"><?php echo e(__('Attach Files (ID, Reports, etc.)')); ?></label>
                    <div class="relative group">
                        <input type="file" wire:model="patientFiles" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        <div class="w-full px-4 py-3 bg-white border-2 border-dashed border-purple-200 rounded-xl flex items-center justify-center gap-3 text-purple-600 group-hover:border-purple-400 transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            <span class="font-bold"><?php echo e(__('Click or drag to upload files')); ?></span>
                            <div wire:loading wire:target="patientFiles">
                                <svg class="animate-spin h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                        </div>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['patientFiles.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-500 font-bold block mt-1"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($patientFiles): ?>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $patientFiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <div class="flex items-center gap-2 px-3 py-1 bg-purple-100 text-purple-700 rounded-lg text-xs font-bold">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            <?php echo e(Str::limit($file->getClientOriginalName(), 15)); ?>

                            <button type="button" wire:click="$set('patientFiles.<?php echo e($index); ?>', null)" class="text-red-500 hover:text-red-700">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            </button>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="w-full py-3 bg-purple-600 text-white rounded-xl font-bold mt-2 shadow-lg shadow-purple-200" wire:loading.attr="disabled">
                        <?php echo e(__('Save Patient Data')); ?>

                    </button>
                </div>
            </form>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="relative group">
                <div class="absolute inset-y-0 <?php echo e(app()->getLocale() === 'ar' ? 'right-0 pr-4' : 'left-0 pl-4'); ?> flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400 group-focus-within:text-purple-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input wire:model.live.debounce.500ms="search" type="text" placeholder="<?php echo e(__('Search by patient name or phone to book...')); ?>" 
                       class="w-full <?php echo e(app()->getLocale() === 'ar' ? 'pr-11 pl-12' : 'pl-11 pr-12'); ?> py-3.5 md:py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-purple-500 transition-all shadow-inner text-base md:text-lg">
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search): ?>
                <button wire:click="clearSearch" class="absolute inset-y-0 <?php echo e(app()->getLocale() === 'ar' ? 'left-0 pl-4' : 'right-0 pr-4'); ?> flex items-center text-gray-400 hover:text-red-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search && count($patients) > 0): ?>
                <div class="absolute left-0 right-0 mt-3 bg-white border border-gray-100 rounded-2xl shadow-2xl z-20 overflow-x-auto divide-y divide-gray-50 animate-fade-in-down mx-0 sm:mx-0 custom-scrollbar">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $patients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <div class="px-0 transition-all">
                        <div class="p-4 hover:bg-gray-50 flex items-center justify-between cursor-pointer min-w-[500px]" wire:click="selectForBooking(<?php echo e($patient->id); ?>)">
                            <div class="flex items-center gap-5">
                                <div class="w-12 h-12 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-xl">
                                    <?php echo e(mb_substr($patient->name, 0, 1)); ?>

                                </div>
                                <div>
                                    <span class="block font-bold text-gray-900 text-lg"><?php echo e($patient->name); ?></span>
                                    <div class="flex items-center gap-3 mt-1">
                                        <span class="text-sm text-gray-500 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                            <?php echo e($patient->phone); ?>

                                        </span>
                                        <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-md"><?php echo e(__('ID')); ?>: #<?php echo e($patient->id); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <div class="px-5 py-2 bg-purple-600 text-white rounded-full text-xs font-black shadow-md shadow-purple-200 flex items-center gap-2 group-hover:bg-purple-700 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                    <?php echo e(__('Record Appointment')); ?>

                                </div>
                                <svg class="w-5 h-5 text-purple-300 group-hover:text-purple-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </div>
                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bookingPatientId == $patient->id): ?>
                        <div class="bg-gradient-to-br from-white to-purple-50/20 p-6 border-t border-purple-100/50 animate-slide-in-top rounded-b-2xl">
                            <div class="flex items-center gap-4 mb-5 text-purple-900 bg-white/50 w-fit px-4 py-2.5 rounded-2xl border border-purple-100/50 shadow-sm">
                                <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center shadow-lg transform -rotate-3 group-hover:rotate-0 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-black uppercase tracking-[0.2em] opacity-50 leading-none mb-1"><?php echo e(__('Appointment Registration')); ?></span>
                                    <span class="font-black text-base tracking-tight leading-none text-purple-900"><?php echo e(__('Book with Dr.')); ?> <?php echo e($assignedDoctorName); ?></span>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="space-y-1">
                                    <label class="text-[11px] font-bold text-purple-700 uppercase"><?php echo e(__('Time')); ?></label>
                                    <input wire:model="bookingTime" type="time" class="w-full px-3 py-2 bg-white border border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 shadow-sm text-sm">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['bookingTime'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-[10px] text-red-500 font-bold block mt-1"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[11px] font-bold text-purple-700 uppercase"><?php echo e(__('Type')); ?></label>
                                    <select wire:model="bookingType" class="w-full px-3 py-2 bg-white border border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 shadow-sm text-sm">
                                        <option value="checkup"><?php echo e(__('Consultation Case')); ?></option>
                                        <option value="follow_up"><?php echo e(__('Follow-up Case')); ?></option>
                                    </select>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['bookingType'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-[10px] text-red-500 font-bold block mt-1"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="flex items-end">
                                    <button wire:click="confirmBooking" class="w-full py-2 bg-purple-600 text-white rounded-xl font-bold shadow-lg shadow-purple-100 hover:bg-purple-700 transition-all flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <?php echo e(__('Confirm')); ?>

                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="p-3 bg-gray-50 text-center text-xs text-gray-400">
                        <?php echo e(__('To register a new patient, use the "Add Patient" button above.')); ?>

                    </div>
                </div>
                <?php elseif($search): ?>
                <div class="absolute w-full mt-2 bg-white p-4 text-center text-gray-400 border border-gray-100 rounded-2xl shadow-xl z-20">
                    <?php echo e(__('No results found. You can add a new patient from the button above.')); ?>

                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                        <h4 class="font-bold text-gray-900"><?php echo e(__('Appointments Schedule')); ?></h4>
                        <p class="text-xs text-gray-500"><?php echo e(__('Manage and track daily patient queue')); ?></p>
                    </div>
                </div>
                
                <div class="flex items-center bg-gray-50 p-1 rounded-xl w-full sm:w-auto">
                    <button wire:click="$set('selectedDate', '<?php echo e(now()->subDay()->toDateString()); ?>')" class="p-2 hover:bg-white rounded-lg transition-all text-gray-400 hover:text-purple-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                    <input type="date" wire:model.live="selectedDate" class="bg-transparent border-none text-sm font-bold text-gray-700 focus:ring-0 px-2 text-center w-full">
                    <button wire:click="$set('selectedDate', '<?php echo e(now()->addDay()->toDateString()); ?>')" class="p-2 hover:bg-white rounded-lg transition-all text-gray-400 hover:text-purple-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
                    <thead class="bg-gray-50 text-gray-500 text-sm">
                        <tr>
                            <th class="px-6 py-4 font-medium w-24"><?php echo e(__('Time')); ?></th>
                            <th class="px-6 py-4 font-medium"><?php echo e(__('Patient')); ?></th>
                            <th class="px-6 py-4 font-medium"><?php echo e(__('Status')); ?></th>
                            <th class="px-6 py-4 font-medium"><?php echo e(__('Actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $dailyAppointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $appointment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <tr class="hover:bg-gray-50/50 transition-colors <?php echo e($appointment->status === 'seen' ? 'opacity-60' : ''); ?>">
                            <td class="px-6 py-4 font-bold text-purple-700">
                                <?php echo e($appointment->scheduled_at->format('H:i')); ?>

                            </td>
                            <td class="px-6 py-4">
                                <a href="<?php echo e(route('patients.show', $appointment->patient_id)); ?>" class="block font-bold hover:text-purple-600 transition-colors"><?php echo e($appointment->patient->name); ?></a>
                                <span class="text-xs text-gray-400"><?php echo e($appointment->patient->phone); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($appointment->status === 'checked-in'): ?>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs rounded-full font-bold"><?php echo e(__('Checked In')); ?></span>
                                <?php elseif($appointment->status === 'seen'): ?>
                                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs rounded-full font-bold"><?php echo e(__('Seen')); ?></span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-amber-100 text-amber-700 text-xs rounded-full font-bold"><?php echo e(__('Pending')); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-6 py-4 flex items-center gap-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($appointment->status === 'pending'): ?>
                                <button wire:click="checkIn(<?php echo e($appointment->id); ?>)" class="text-white bg-purple-600 px-3 py-1.5 rounded-lg font-bold text-xs hover:bg-purple-700 shadow-sm shadow-purple-200 transition-all"><?php echo e(__('Prepare')); ?></button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <button wire:click="editAppointment(<?php echo e($appointment->id); ?>)" class="text-gray-400 hover:text-purple-600 transition-colors" title="<?php echo e(__('Edit')); ?>">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                            </td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <p><?php echo e(__('No appointments scheduled for this date.')); ?></p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dailyAppointments->hasPages()): ?>
            <div class="p-6 border-t border-gray-100">
                <?php echo e($dailyAppointments->links()); ?>

            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <div class="space-y-8">
        <!-- Quick Stats -->
        <div class="bg-gradient-to-br from-purple-600 to-indigo-700 text-white p-8 rounded-[2rem] shadow-xl shadow-purple-200">
            <h4 class="opacity-80 mb-2 font-medium"><?php echo e(__('Total Bookings Today')); ?></h4>
            <div class="text-5xl font-bold mb-6 tabular-nums"><?php echo e($stats->total); ?></div>
            <div class="space-y-3">
                <div class="flex items-center justify-between text-sm bg-white/10 p-3 rounded-xl border border-white/10">
                    <span><?php echo e(__('Waiting for Preparation')); ?></span>
                    <span class="font-bold"><?php echo e($stats->pending); ?></span>
                </div>
                <div class="flex items-center justify-between text-sm bg-white/10 p-3 rounded-xl border border-white/10">
                    <span><?php echo e(__('Prepared Cases')); ?></span>
                    <span class="font-bold"><?php echo e($stats->prepared); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Appointment Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editingAppointmentId): ?>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
        <div class="bg-white rounded-3xl p-6 shadow-2xl max-w-md w-full border border-purple-100">
            <h3 class="text-xl font-bold text-gray-900 mb-6"><?php echo e(__('Edit Appointment')); ?></h3>
            
            <form wire:submit="saveAppointment" class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1"><?php echo e(__('Select Doctor')); ?></label>
                    <select wire:model="editDoctorId" class="w-full px-4 py-2 bg-gray-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <option value="<?php echo e($doctor->id); ?>"><?php echo e(__('Dr.')); ?> <?php echo e($doctor->name); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['editDoctorId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-sm text-red-500 mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1"><?php echo e(__('Time')); ?></label>
                    <input type="time" wire:model="editTime" class="w-full px-4 py-2 bg-gray-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['editTime'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-sm text-red-500 mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                
                <div class="flex items-center gap-3 pt-4">
                    <button type="button" wire:click="cancelEdit" class="flex-1 px-4 py-2 border border-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-50 transition-colors"><?php echo e(__('Cancel')); ?></button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-xl font-bold shadow-lg shadow-purple-200 hover:bg-purple-700 transition-colors"><?php echo e(__('Save Changes')); ?></button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\Bola\Clinova\resources\views\livewire/dashboard/secretary-dashboard.blade.php ENDPATH**/ ?>