<div class="space-y-8 overflow-x-hidden" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>" x-data="{ isEditingPH: false, isEditingFH: false }">
    <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex items-center gap-6">
                <div class="w-20 h-20 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center text-3xl font-bold">
                    <?php echo e(mb_substr($patient->name, 0, 1, 'UTF-8')); ?>

                </div>
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-1"><?php echo e($patient->name); ?></h2>
                    <p class="text-gray-500 font-medium">
                        <?php echo e($patient->phone); ?> | 
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($patient->age_years): ?> <?php echo e($patient->age_years); ?> <?php echo e(__('Y')); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($patient->age_months): ?> <?php echo e($patient->age_months); ?> <?php echo e(__('M')); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($patient->age_days): ?> <?php echo e($patient->age_days); ?> <?php echo e(__('D')); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        | <?php echo e($patient->address); ?>

                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isDoctor() || auth()->user()->isAdmin()): ?>
                <button wire:click="deletePatient" 
                        wire:confirm="<?php echo e(__('Are you sure you want to permanently delete this patient record and all associated history? This action cannot be undone.')); ?>"
                        class="px-6 py-2 bg-rose-50 text-rose-600 rounded-xl font-bold hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                    <?php echo e(__('Delete Patient')); ?>

                </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <button wire:click="openEditModal" class="px-6 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 font-bold transition-colors shadow-sm"><?php echo e(__('Edit Data')); ?></button>
                <button wire:click="openBooking" class="px-6 py-2 bg-purple-600 text-white rounded-xl font-bold hover:shadow-lg hover:shadow-purple-200 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <?php echo e(__('Book Appointment')); ?>

                </button>
                <button wire:click="openVisitModal" class="px-6 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-bold hover:shadow-lg hover:shadow-emerald-200 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                    <?php echo e(__('Record New Visit')); ?>

                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
            <h3 class="text-xl font-bold text-gray-900 border-s-4 border-purple-600 ps-4"><?php echo e(__('Clinical Visit History')); ?></h3>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('visit_message')): ?>
                <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 text-sm rounded-2xl border border-emerald-100 flex items-center gap-3 animate-slide-in">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="font-bold"><?php echo e(session('visit_message')); ?></span>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $primaryVisits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $visit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 bg-gray-50 flex items-center justify-between border-b border-gray-100">
                    <div class="flex items-center gap-6">
                        <div>
                            <span class="text-sm text-gray-500"><?php echo e(__('Visit Date')); ?></span>
                            <div class="flex items-center gap-2">
                                <p class="font-bold"><?php echo e($visit->created_at->format('Y-m-d')); ?></p>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold <?php echo e($visit->type === 'checkup' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700'); ?>">
                                    <?php echo e(__($visit->type === 'checkup' ? 'Checkup' : 'Follow-up')); ?>

                                </span>
                            </div>
                        </div>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($visit->type === 'checkup' && auth()->user()->isDoctor()): ?>
                    <button wire:click="startFollowUp(<?php echo e($visit->id); ?>)" class="px-4 py-1.5 bg-white border border-emerald-200 text-emerald-600 rounded-lg text-xs font-bold hover:bg-emerald-50 transition-colors flex items-center gap-1.5 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <?php echo e(__('Add Follow-up')); ?>

                    </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h4 class="text-purple-900 font-bold text-sm mb-2"><?php echo e(__('Complaint')); ?></h4>
                        <p class="text-gray-700 leading-relaxed"><?php echo e($visit->complaint); ?></p>
                    </div>
                    <div>
                        <h4 class="text-purple-900 font-bold text-sm mb-2"><?php echo e(__('Diagnosis')); ?></h4>
                        <p class="text-gray-700 leading-relaxed"><?php echo e($visit->diagnosis); ?></p>
                    </div>
                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h4 class="text-purple-900 font-bold text-sm mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                <?php echo e(__('Investigation & Tests')); ?>

                            </h4>
                            <p class="text-gray-700 leading-relaxed whitespace-pre-line bg-slate-50 p-4 rounded-xl border border-slate-100"><?php echo e($visit->history ?: __('No data.')); ?></p>
                        </div>
                        <div>
                            <h4 class="text-purple-900 font-bold text-sm mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                <?php echo e(__('Treatment & Instructions')); ?>

                            </h4>
                            <p class="text-gray-700 leading-relaxed whitespace-pre-line bg-slate-50 p-4 rounded-xl border border-slate-100"><?php echo e($visit->treatment_text ?: __('No data.')); ?></p>
                        </div>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($visit->follow_up_notes): ?>
                    <div class="md:col-span-2 mt-4 p-4 bg-indigo-50/30 rounded-2xl border border-indigo-100/50">
                        <h4 class="text-[10px] font-black text-indigo-600 uppercase tracking-[0.2em] mb-2"><?php echo e(__('Follow Up Notes')); ?></h4>
                        <p class="text-sm text-indigo-900 leading-relaxed whitespace-pre-line"><?php echo e($visit->follow_up_notes); ?></p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($visit->specialty_data && count($visit->specialty_data) > 0): ?>
                    <div class="md:col-span-2 pt-4 border-t border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-purple-50/30 p-4 rounded-2xl border border-purple-100">
                            <?php 
                                $doctorSpecialty = $visit->doctor->specialty;
                            ?>
                            <h4 class="md:col-span-2 text-[10px] font-black text-purple-600 uppercase tracking-[0.2em] mb-1">
                                <?php echo e($doctorSpecialty ? $doctorSpecialty->name : __('Specialty Details')); ?>

                            </h4>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $visit->specialty_data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fieldId => $answer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <?php $field = \App\Models\SpecialtyField::find($fieldId); ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($field): ?>
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider"><?php echo e($field->label); ?></span>
                                    <span class="text-sm font-bold text-slate-800"><?php echo e(is_array($answer) ? implode(', ', $answer) : $answer); ?></span>
                                </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($visit->treatment_file_path): ?>
                    <div class="md:col-span-2">
                        <a href="<?php echo e($visit->treatment_file_path ? route('files.visit', $visit->id) : '#'); ?>" target="_blank" class="inline-flex items-center gap-2 text-purple-600 font-bold hover:underline">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <?php echo e(__('View Attached Treatment File')); ?>

                        </a>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($visit->followUps->count() > 0): ?>
            <div class="ms-8 mt-2 space-y-4 mb-8 border-s-4 border-emerald-100 ps-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $visit->followUps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $followUp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <div class="bg-emerald-50/20 rounded-2xl border border-emerald-100/50 overflow-hidden relative shadow-sm">
                    <div class="p-4 bg-emerald-50/50 flex items-center justify-between border-b border-emerald-100/20">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-emerald-800"><?php echo e(__('Follow-up Visit')); ?></p>
                                <p class="text-[10px] text-emerald-600 font-bold uppercase"><?php echo e($followUp->created_at->format('Y-m-d')); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="text-xs">
                            <h5 class="text-emerald-900 font-bold mb-1"><?php echo e(__('Complaint')); ?></h5>
                            <p class="text-gray-600"><?php echo e($followUp->complaint); ?></p>
                        </div>
                        <div class="text-xs">
                            <h5 class="text-emerald-900 font-bold mb-1"><?php echo e(__('Diagnosis')); ?></h5>
                            <p class="text-gray-600"><?php echo e($followUp->diagnosis); ?></p>
                        </div>
                        <div class="md:col-span-2 text-xs">
                            <h5 class="text-emerald-900 font-bold mb-1"><?php echo e(__('Treatment & Instructions')); ?></h5>
                            <p class="text-gray-600 whitespace-pre-line"><?php echo e($followUp->treatment_text ?: __('No data.')); ?></p>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($followUp->follow_up_notes): ?>
                        <div class="md:col-span-2 text-xs mt-2 p-3 bg-white rounded-xl border border-emerald-100/50">
                            <h5 class="text-emerald-900 font-bold mb-1"><?php echo e(__('Follow Up Notes')); ?></h5>
                            <p class="text-gray-600 whitespace-pre-line italic"><?php echo e($followUp->follow_up_notes); ?></p>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <div class="bg-white p-12 text-center rounded-2xl border-2 border-dashed border-gray-100">
                <p class="text-gray-400"><?php echo e(__('No previous visits recorded for this patient.')); ?></p>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="space-y-8">
            <!-- Medical Files & Investigations -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <h3 class="font-bold text-lg mb-4"><?php echo e(__('Medical Files & Investigations')); ?></h3>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('file_message')): ?>
                    <div class="mb-4 p-3 bg-green-50 text-green-700 text-sm rounded-xl border border-green-100">
                        <?php echo e(session('file_message')); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                <div class="space-y-4 mb-6 max-h-[300px] overflow-y-auto custom-scrollbar pr-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $allFiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="p-2 <?php echo e($file['type'] === 'investigation' ? 'bg-blue-100 text-blue-600' : ($file['type'] === 'lab' ? 'bg-rose-100 text-rose-600' : 'bg-purple-100 text-purple-600')); ?> rounded-lg shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-sm font-bold text-gray-800 truncate" title="<?php echo e($file['name']); ?>"><?php echo e(Str::limit($file['name'], 20)); ?></span>
                                <span class="text-[10px] text-gray-500 uppercase font-bold"><?php echo e(__($file['type'] === 'investigation' ? 'Investigation' : ($file['type'] === 'lab' ? 'Lab Result' : ($file['type'] === 'prescription' ? 'Prescription' : 'Other')))); ?> • <?php echo e($file['date']->format('Y-m-d')); ?></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <a href="<?php echo e($file['url']); ?>" target="_blank" class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-black hover:text-white transition-all shadow-sm group/btn" title="<?php echo e(__('View')); ?>">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($file['source'] === 'patient_file' && (auth()->user()->isDoctor() || auth()->user()->isSecretary())): ?>
                            <button wire:click="deleteFile(<?php echo e($file['id']); ?>)" wire:confirm="<?php echo e(__('Are you sure?')); ?>" class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all shadow-sm" title="<?php echo e(__('Delete')); ?>">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="text-sm text-gray-400 text-center py-4"><?php echo e(__('No files uploaded yet.')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isDoctor() || auth()->user()->isSecretary()): ?>
                <!-- Upload Form -->
                <div class="pt-4 border-t border-dashed border-gray-200 space-y-3 relative">
                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        <select wire:model="fileType" class="w-full sm:w-1/3 px-3 py-2 text-sm bg-gray-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500">
                            <option value="lab"><?php echo e(__('Lab Result')); ?></option>
                            <option value="investigation"><?php echo e(__('Investigation')); ?></option>
                            <option value="other"><?php echo e(__('Other Document')); ?></option>
                        </select>
                        <div class="relative w-full sm:w-2/3 h-10 bg-purple-50 border-2 border-dashed border-purple-200 rounded-xl flex items-center justify-center group hover:bg-purple-100 transition-all cursor-pointer">
                            <input type="file" wire:model.live="uploads" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            <div class="flex items-center gap-2 text-purple-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                <span class="text-xs font-black uppercase tracking-widest"><?php echo e(__('Select Files')); ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($patientFiles): ?>
                    <div class="grid grid-cols-1 gap-2 mt-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $patientFiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <div class="flex items-center justify-between p-2 bg-white rounded-lg border border-purple-100 shadow-sm animate-fade-in" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processElementKey('temp-file-{{ $file[\'id\'] }}', get_defined_vars()); ?>wire:key="temp-file-<?php echo e($file['id']); ?>">
                            <div class="flex items-center gap-2 overflow-hidden">
                                <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                </div>
                                <span class="text-xs font-bold text-gray-700 truncate max-w-[150px]"><?php echo e($file['name']); ?></span>
                            </div>
                            <button type="button" wire:click="deletePatientFile('<?php echo e($file['id']); ?>')" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <div wire:loading wire:target="uploads" class="text-[10px] text-purple-600 font-black uppercase tracking-widest animate-pulse"><?php echo e(__('Uploading to temporary storage...')); ?></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['uploads.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-500 font-bold block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <button type="button" wire:click="uploadFile" wire:loading.attr="disabled" wire:target="uploadFile, uploads" class="w-full py-3 bg-purple-600 text-white rounded-xl text-xs font-black uppercase tracking-[0.2em] shadow-lg shadow-purple-200 hover:bg-purple-700 hover:-translate-y-0.5 transition-all disabled:opacity-50">
                        <?php echo e(__('Save Files Permanently')); ?>

                    </button>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <!-- Family History -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm relative" x-data="{ isEditing: false }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg"><?php echo e(__('Family Medical History')); ?></h3>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isDoctor()): ?>
                    <button @click="isEditing = true" x-show="!isEditing" class="text-purple-600 hover:bg-purple-50 p-2 rounded-lg transition-colors" title="<?php echo e(__('Edit')); ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div x-show="isEditing" x-cloak>
                    <form wire:submit.prevent="saveFamilyHistory" class="space-y-3">
                        <div class="relative">
                            <textarea wire:model.live="familyHistoryEdit" rows="4" class="w-full px-4 py-3 bg-gray-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500" placeholder="<?php echo e(__('Record hereditary diseases...')); ?>"></textarea>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($familyHistoryEdit) && count($familyHistorySuggestions) > 0): ?>
                            <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 max-h-40 overflow-y-auto">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $familyHistorySuggestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suggestion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <button type="button" wire:click="selectSuggestionFor('familyHistoryEdit', '<?php echo e(addslashes($suggestion)); ?>')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <?php echo e($suggestion); ?>

                                    </button>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="flex items-center gap-2 justify-end">
                            <button type="button" @click="isEditing = false" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors"><?php echo e(__('Cancel')); ?></button>
                            <button type="submit" class="px-4 py-2 text-sm font-bold bg-purple-600 text-white rounded-xl shadow-lg shadow-purple-200 hover:bg-purple-700 transition-colors"><?php echo e(__('Save')); ?></button>
                        </div>
                    </form>
                </div>
                <div x-show="!isEditing">
                    <p class="text-gray-600 text-sm leading-relaxed whitespace-pre-line">
                        <?php echo e($patient->family_history ?: __('No data recorded.')); ?>

                    </p>
                </div>
            </div>

            <!-- Personal History -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm relative" x-data="{ isEditingPH: false }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg"><?php echo e(__('Personal Medical History')); ?></h3>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isDoctor()): ?>
                    <button @click="isEditingPH = true" x-show="!isEditingPH" class="text-purple-600 hover:bg-purple-50 p-2 rounded-lg transition-colors" title="<?php echo e(__('Edit')); ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div x-show="isEditingPH" x-cloak>
                    <form wire:submit.prevent="savePersonalHistory" class="space-y-3">
                        <div class="relative">
                            <textarea wire:model.live="personalHistoryEdit" rows="4" class="w-full px-4 py-3 bg-gray-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500" placeholder="<?php echo e(__('Record chronic diseases, allergies, etc...')); ?>"></textarea>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($personalHistoryEdit) && count($personalHistorySuggestions) > 0): ?>
                            <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 max-h-40 overflow-y-auto">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $personalHistorySuggestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suggestion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <button type="button" wire:click="selectSuggestionFor('personalHistoryEdit', '<?php echo e(addslashes($suggestion)); ?>')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <?php echo e($suggestion); ?>

                                    </button>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="flex items-center gap-2 justify-end">
                            <button type="button" @click="isEditingPH = false" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors"><?php echo e(__('Cancel')); ?></button>
                            <button type="submit" class="px-4 py-2 text-sm font-bold bg-purple-600 text-white rounded-xl shadow-lg shadow-purple-200 hover:bg-purple-700 transition-colors"><?php echo e(__('Save')); ?></button>
                        </div>
                    </form>
                </div>
                <div x-show="!isEditingPH">
                    <p class="text-gray-600 text-sm leading-relaxed whitespace-pre-line">
                        <?php echo e($patient->personal_history ?: __('No data recorded.')); ?>

                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Record New Visit Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showVisitModal): ?>
    <div class="fixed inset-0 z-[60] overflow-y-auto flex justify-center items-start p-4 sm:p-6">
        <div wire:click="closeVisitModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
        <div class="bg-white rounded-[2.5rem] w-full max-w-2xl shadow-2xl relative mx-auto my-8 animate-zoom-in">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full -translate-y-16 translate-x-16 blur-2xl"></div>
            
            <form wire:submit.prevent="saveVisit" class="relative">
                <div class="p-8 md:p-10">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-2xl font-black text-gray-900 flex items-center gap-3">
                            <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center shadow-inner">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                            </div>
                            <?php echo e($visitType === 'checkup' ? __('Record Clinical Visit') : __('Record Follow-up Visit')); ?>

                        </h3>
                        <button type="button" wire:click="closeVisitModal" class="p-3 hover:bg-gray-100 rounded-full transition-colors text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest"><?php echo e(__('Patient Complaint')); ?></label>
                            <div class="relative">
                                <textarea wire:model.live="complaint" rows="3" placeholder="<?php echo e(__('What is the patient suffering from?')); ?>" 
                                        class="w-full bg-slate-50 border-gray-200 rounded-2xl py-4 px-5 text-sm focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"></textarea>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($complaint) && count($complaintSuggestions) > 0): ?>
                                <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 max-h-40 overflow-y-auto">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $complaintSuggestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suggestion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <button type="button" wire:click="selectSuggestionFor('complaint', '<?php echo e(addslashes($suggestion)); ?>')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <?php echo e($suggestion); ?>

                                        </button>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['complaint'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-rose-500 text-xs font-bold"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest"><?php echo e(__('Preliminary Diagnosis')); ?></label>
                            <div class="relative">
                                <input type="text" wire:model.live="diagnosis" placeholder="<?php echo e(__('Enter diagnosis...')); ?>"
                                    class="w-full bg-slate-50 border-gray-200 rounded-2xl py-4 px-5 text-sm focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($diagnosis) && count($diagnosisSuggestions) > 0): ?>
                                <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 max-h-40 overflow-y-auto">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $diagnosisSuggestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suggestion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <button type="button" wire:click="selectSuggestionFor('diagnosis', '<?php echo e(addslashes($suggestion)); ?>')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <?php echo e($suggestion); ?>

                                        </button>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['diagnosis'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-rose-500 text-xs font-bold"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>


                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($specialtyFields) > 0): ?>
                        <div class="md:col-span-2 space-y-4 pt-4 border-t border-dashed border-gray-200">
                            <?php
                                $doctor = auth()->user()->isDoctor() ? auth()->user() : \App\Models\User::find(auth()->user()->doctor_id);
                            ?>
                            <h4 class="text-[10px] font-black text-emerald-600 uppercase tracking-[0.2em]"><?php echo e($doctor->specialty->name); ?> - <?php echo e(__('Extra Details')); ?></h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $specialtyFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <div class="space-y-2 <?php echo e($field->type === 'multi_select' ? 'md:col-span-2' : ''); ?>">
                                    <label class="text-xs font-black text-gray-500 uppercase tracking-widest"><?php echo e($field->label); ?></label>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($field->type === 'text'): ?>
                                        <input type="text" wire:model="dynamicAnswers.<?php echo e($field->id); ?>" class="w-full bg-slate-50 border-gray-200 rounded-2xl py-3 px-5 text-sm focus:ring-4 focus:ring-emerald-500/10 transition-all">
                                    <?php elseif($field->type === 'select'): ?>
                                        <select wire:model="dynamicAnswers.<?php echo e($field->id); ?>" class="w-full bg-slate-50 border-gray-200 rounded-2xl py-3 px-5 text-sm focus:ring-4 focus:ring-emerald-500/10 transition-all">
                                            <option value=""><?php echo e(__('Select...')); ?></option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $field->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                                <option value="<?php echo e($opt); ?>"><?php echo e($opt); ?></option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </select>
                                    <?php elseif($field->type === 'multi_select'): ?>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 bg-slate-50 p-4 rounded-2xl border border-gray-100">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $field->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                            <label class="flex items-center gap-3 cursor-pointer group">
                                                <input type="checkbox" wire:model="dynamicAnswers.<?php echo e($field->id); ?>" value="<?php echo e($opt); ?>" class="w-5 h-5 text-emerald-600 bg-white border-gray-200 rounded-xl focus:ring-emerald-500/20 transition-all">
                                                <span class="text-xs font-bold text-gray-600 group-hover:text-emerald-700 transition-colors"><?php echo e($opt); ?></span>
                                            </label>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </div>
                                     <?php elseif($field->type === 'date'): ?>
                                        <input type="date" wire:model="dynamicAnswers.<?php echo e($field->id); ?>" class="w-full bg-slate-50 border-gray-200 rounded-2xl py-3 px-5 text-sm focus:ring-4 focus:ring-emerald-500/10 transition-all">
                                    <?php elseif($field->type === 'number'): ?>
                                        <input type="number" wire:model="dynamicAnswers.<?php echo e($field->id); ?>" class="w-full bg-slate-50 border-gray-200 rounded-2xl py-3 px-5 text-sm focus:ring-4 focus:ring-emerald-500/10 transition-all">
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['dynamicAnswers.' . $field->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-rose-500 text-[10px] font-bold"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest"><?php echo e(__('Investigations & Tests')); ?></label>
                            <div class="relative">
                                <textarea wire:model.live="investigation" rows="4" placeholder="<?php echo e(__('Requested labs, etc.')); ?>"
                                          class="w-full bg-slate-50 border-gray-200 rounded-2xl py-4 px-5 text-sm focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"></textarea>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($investigation) && count($investigationSuggestions) > 0): ?>
                                <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 max-h-40 overflow-y-auto">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $investigationSuggestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suggestion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <button type="button" wire:click="selectSuggestionFor('investigation', '<?php echo e(addslashes($suggestion)); ?>')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <?php echo e($suggestion); ?>

                                        </button>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest"><?php echo e(__('Treatment Plan')); ?></label>
                            <div class="relative">
                                <textarea wire:model.live="treatmentText" rows="4" placeholder="<?php echo e(__('Instructions...')); ?>"
                                          class="w-full bg-slate-50 border-gray-200 rounded-2xl py-4 px-5 text-sm focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"></textarea>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($treatmentText) && count($treatmentSuggestions) > 0): ?>
                                <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 max-h-40 overflow-y-auto">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $treatmentSuggestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suggestion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <button type="button" wire:click="selectSuggestionFor('treatmentText', '<?php echo e(addslashes($suggestion)); ?>')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <?php echo e($suggestion); ?>

                                        </button>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>

                        <div class="md:col-span-2 space-y-2 pt-4 border-t border-dashed border-gray-200">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest"><?php echo e(__('Follow Up Notes')); ?></label>
                            <textarea wire:model.live="followUpNotes" rows="3" placeholder="<?php echo e(__('Notes regarding patient progress...')); ?>"
                                      class="w-full bg-indigo-50/50 border-indigo-100 rounded-2xl py-4 px-5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all"></textarea>
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest"><?php echo e(__('Attach Files')); ?></label>
                            <div class="relative w-full h-32 bg-slate-50 border-2 border-dashed border-slate-200 rounded-[2rem] flex flex-col items-center justify-center group hover:bg-slate-100 transition-all cursor-pointer overflow-hidden">
                                <input type="file" wire:model.live="uploads" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div class="flex flex-col items-center gap-2 text-slate-400 group-hover:text-emerald-600 transition-colors">
                                    <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-[0.2em]"><?php echo e(__('Select Files (Max 2MB)')); ?></span>
                                </div>
                                <div wire:loading wire:target="uploads" class="absolute inset-0 bg-slate-50/90 backdrop-blur-sm flex items-center justify-center z-20">
                                    <div class="flex items-center gap-2">
                                        <svg class="animate-spin h-5 w-5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        <span class="text-xs font-black text-emerald-700 uppercase tracking-widest"><?php echo e(__('Uploading...')); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($patientFiles): ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $patientFiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <div class="flex items-center justify-between p-3 bg-white rounded-2xl border border-emerald-100 shadow-sm animate-fade-in" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processElementKey('visit-temp-file-{{ $file[\'id\'] }}', get_defined_vars()); ?>wire:key="visit-temp-file-<?php echo e($file['id']); ?>">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="text-xs font-black text-slate-700 truncate"><?php echo e($file['name']); ?></span>
                                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest"><?php echo e(__('Temp Ready')); ?></span>
                                        </div>
                                    </div>
                                    <button type="button" wire:click="deletePatientFile('<?php echo e($file['id']); ?>')" class="p-2 text-rose-500 hover:bg-rose-50 rounded-xl transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['uploads.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-rose-500 text-xs font-bold block mt-1"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-10 flex gap-4">
                        <button type="button" wire:click="closeVisitModal" class="flex-1 py-4 text-slate-500 font-bold"><?php echo e(__('Cancel')); ?></button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="uploads, saveVisit" class="flex-[2] py-4 bg-emerald-600 text-white rounded-2xl font-bold shadow-xl disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="uploads, saveVisit"><?php echo e(__('Save Visit Record')); ?></span>
                            <span wire:loading wire:target="uploads, saveVisit" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span><?php echo e(__('Please wait...')); ?></span>
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Booking Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showBookingModal): ?>
    <div class="fixed inset-0 z-[60] flex justify-center items-start overflow-y-auto p-4 md:p-10">
        <div wire:click="closeBookingModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="bg-white rounded-[2rem] w-full max-w-lg shadow-2xl relative overflow-hidden animate-zoom-in my-8">
            <div class="p-8">
                <h3 class="text-xl font-bold mb-6"><?php echo e(__('Book Appointment for')); ?> <?php echo e($patient->name); ?></h3>
                <div class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase"><?php echo e(__('Date')); ?></label>
                            <input type="date" wire:model="bookingDate" class="w-full bg-slate-50 border-gray-200 rounded-xl px-4 py-3">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase"><?php echo e(__('Time')); ?></label>
                            <input wire:model="bookingTime" type="time" class="w-full bg-slate-50 border-gray-200 rounded-xl px-4 py-3">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase"><?php echo e(__('Type')); ?></label>
                            <select wire:model="bookingType" class="w-full bg-slate-50 border-gray-200 rounded-xl px-4 py-3">
                                <option value="checkup"><?php echo e(__('Checkup')); ?></option>
                                <option value="follow_up"><?php echo e(__('Follow-up')); ?></option>
                            </select>
                        </div>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isAdmin()): ?>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase"><?php echo e(__('Doctor')); ?></label>
                        <select wire:model="bookingDoctorId" class="w-full bg-slate-50 border-gray-200 rounded-xl px-4 py-3">
                            <option value=""><?php echo e(__('Select Doctor')); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <option value="<?php echo e($doc->id); ?>"><?php echo e($doc->name); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                    <?php else: ?>
                    <div>
                        <span class="block text-[10px] font-black uppercase text-purple-600 mb-1 tracking-widest"><?php echo e(__('Doctor')); ?></span>
                        <?php $doctor = \App\Models\User::find($bookingDoctorId); ?>
                        <div class="p-4 bg-purple-50 rounded-2xl border border-purple-100 flex items-center justify-between shadow-inner">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center font-bold shadow-lg text-xs">
                                    <?php echo e(mb_substr($doctor->name ?? '?', 0, 1)); ?>

                                </div>
                                <span class="font-bold text-purple-900 text-sm"><?php echo e($doctor->name ?? __('Unknown')); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="pt-6 flex gap-3">
                        <button wire:click="closeBookingModal" class="flex-1 py-3 text-gray-500 font-bold"><?php echo e(__('Cancel')); ?></button>
                        <button wire:click="confirmBooking" class="flex-[2] py-3 bg-indigo-600 text-white rounded-xl font-bold shadow-lg"><?php echo e(__('Confirm Booking')); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Edit Patient Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showEditModal): ?>
    <div class="fixed inset-0 z-[60] flex justify-center items-start overflow-y-auto p-4 md:p-10">
        <div wire:click="closeEditModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="bg-white rounded-[2.5rem] w-full max-w-lg shadow-2xl relative overflow-hidden animate-zoom-in p-8 my-8">
            <h3 class="text-xl font-bold mb-6"><?php echo e(__('Edit Patient Data')); ?></h3>
            <form wire:submit.prevent="savePatientData" class="space-y-5">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase"><?php echo e(__('Full Name')); ?></label>
                    <input type="text" wire:model="editName" class="w-full bg-slate-50 border-gray-200 rounded-xl px-4 py-3">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase font-black"><?php echo e(__('Phone')); ?></label>
                        <input type="text" wire:model="editPhone" class="w-full bg-slate-50 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-purple-500 transition-all font-bold">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase font-black"><?php echo e(__('Weight')); ?> (<?php echo e(__('kg')); ?>)</label>
                        <input type="number" wire:model="editWeight" class="w-full bg-slate-50 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-purple-500 transition-all font-bold">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase font-black mb-2 block"><?php echo e(__('Age')); ?> (<?php echo e(__('Y')); ?> - <?php echo e(__('M')); ?> - <?php echo e(__('D')); ?>)</label>
                    <div class="grid grid-cols-3 gap-3">
                        <input type="number" wire:model="editAgeYears" placeholder="<?php echo e(__('Y')); ?>" class="w-full bg-slate-50 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-purple-500 transition-all font-bold text-center">
                        <input type="number" wire:model="editAgeMonths" placeholder="<?php echo e(__('M')); ?>" class="w-full bg-slate-50 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-purple-500 transition-all font-bold text-center">
                        <input type="number" wire:model="editAgeDays" placeholder="<?php echo e(__('D')); ?>" class="w-full bg-slate-50 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-purple-500 transition-all font-bold text-center">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase"><?php echo e(__('Personal History')); ?></label>
                    <textarea wire:model="personalHistoryEdit" rows="3" class="w-full bg-slate-50 border-gray-200 rounded-xl px-4 py-3"></textarea>
                </div>
                <div class="pt-6 flex gap-3">
                    <button type="button" wire:click="closeEditModal" class="flex-1 py-3 text-gray-500 font-bold"><?php echo e(__('Cancel')); ?></button>
                    <button type="submit" class="flex-[2] py-3 bg-purple-600 text-white rounded-xl font-bold shadow-lg"><?php echo e(__('Update Records')); ?></button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Bola\Clinova\resources\views/livewire/shared/patient-profile.blade.php ENDPATH**/ ?>