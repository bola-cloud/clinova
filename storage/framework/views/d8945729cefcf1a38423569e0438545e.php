<?php

use Livewire\Volt\Component;
use App\Models\Specialty;
use App\Models\SpecialtyField;

?>

<div class="space-y-6">
    <div class="flex items-center justify-between bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight"><?php echo e(__('Specialties Management')); ?></h2>
            <p class="text-gray-500 font-medium mt-1"><?php echo e(__('Define medical specialties and their custom visit fields.')); ?></p>
        </div>
        <button wire:click="openSpecialtyModal()" class="px-8 py-4 bg-slate-900 text-white rounded-2xl font-black text-sm shadow-xl hover:bg-black hover:-translate-y-1 transition-all flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <?php echo e(__('Add New Specialty')); ?>

        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Specialties List -->
        <div class="lg:col-span-1 space-y-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $specialties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $specialty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
            <div wire:click="selectSpecialty(<?php echo e($specialty->id); ?>)" 
                 class="group p-6 rounded-[2rem] border transition-all cursor-pointer <?php echo e(($selectedSpecialty && $selectedSpecialty->id == $specialty->id) ? 'bg-purple-600 border-purple-600 shadow-xl shadow-purple-100' : 'bg-white border-gray-100 hover:border-purple-200'); ?>">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-lg <?php echo e(($selectedSpecialty && $selectedSpecialty->id == $specialty->id) ? 'bg-white/20 text-white' : 'bg-purple-50 text-purple-600'); ?>">
                            <?php echo e(mb_substr($specialty->name, 0, 1)); ?>

                        </div>
                        <div>
                            <h4 class="font-black <?php echo e(($selectedSpecialty && $selectedSpecialty->id == $specialty->id) ? 'text-white' : 'text-slate-900'); ?>"><?php echo e($specialty->name); ?></h4>
                            <p class="text-xs font-bold <?php echo e(($selectedSpecialty && $selectedSpecialty->id == $specialty->id) ? 'text-purple-100' : 'text-gray-400'); ?> uppercase tracking-widest"><?php echo e($specialty->fields->count()); ?> <?php echo e(__('Fields')); ?></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button wire:click.stop="openSpecialtyModal(<?php echo e($specialty->id); ?>)" class="p-2 rounded-xl <?php echo e(($selectedSpecialty && $selectedSpecialty->id == $specialty->id) ? 'bg-white/20 text-white hover:bg-white/30' : 'bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-600'); ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </button>
                        <button wire:click.stop="deleteSpecialty(<?php echo e($specialty->id); ?>)" wire:confirm="<?php echo e(__('Are you sure you want to delete this specialty and all its fields?')); ?>" class="p-2 rounded-xl <?php echo e(($selectedSpecialty && $selectedSpecialty->id == $specialty->id) ? 'bg-white/20 text-white hover:bg-rose-500' : 'bg-slate-50 text-slate-400 hover:bg-rose-50 hover:text-rose-600'); ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>

        <!-- Field List -->
        <div class="lg:col-span-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedSpecialty): ?>
            <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden animate-slide-in">
                <div class="p-8 border-b border-gray-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight"><?php echo e($selectedSpecialty->name); ?> - <?php echo e(__('Custom Fields')); ?></h3>
                        <p class="text-gray-500 font-medium text-sm"><?php echo e(__('These fields will appear in visit forms for doctors with this specialty.')); ?></p>
                    </div>
                    <button wire:click="openFieldModal()" class="px-6 py-3 bg-purple-600 text-white rounded-2xl font-black text-xs shadow-lg shadow-purple-100 hover:bg-purple-700 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <?php echo e(__('Add Field')); ?>

                    </button>
                </div>
                <div class="p-8">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedSpecialty->fields->count() > 0): ?>
                    <div class="space-y-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $selectedSpecialty->fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <div class="flex items-center justify-between p-6 bg-slate-50 rounded-[2rem] border border-slate-100 group">
                            <div class="flex items-center gap-6">
                                <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 group-hover:text-purple-600 transition-colors">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($field->type === 'text'): ?>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    <?php else: ?>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div>
                                    <h5 class="font-bold text-slate-900"><?php echo e($field->label); ?></h5>
                                    <div class="flex items-center gap-3">
                                        <span class="text-[10px] uppercase font-black tracking-widest text-slate-400">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($field->type === 'text'): ?>
                                                <?php echo e(__('Text Field')); ?>

                                            <?php elseif($field->type === 'select'): ?>
                                                <?php echo e(__('Single Selection')); ?>

                                            <?php else: ?>
                                                <?php echo e(__('Multiple Selection')); ?>

                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($field->type, ['select', 'multi_select'])): ?>
                                        <span class="text-[10px] text-purple-600 font-bold bg-purple-50 px-2 py-0.5 rounded-lg"><?php echo e(count($field->options)); ?> <?php echo e(__('Options')); ?></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button wire:click="openFieldModal(<?php echo e($field->id); ?>)" class="p-3 bg-white rounded-xl border border-slate-200 text-slate-400 hover:text-slate-600 hover:border-slate-300 transition-all shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                <button wire:click="deleteField(<?php echo e($field->id); ?>)" wire:confirm="<?php echo e(__('Delete this field?')); ?>" class="p-3 bg-white rounded-xl border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-100 hover:bg-rose-50 transition-all shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-300">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h4 class="font-bold text-slate-900 mb-1"><?php echo e(__('No dynamic fields yet')); ?></h4>
                        <p class="text-sm text-gray-500"><?php echo e(__('Add custom fields to collect specific data for this specialty.')); ?></p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="h-full min-h-[400px] flex flex-col items-center justify-center bg-white rounded-[2.5rem] border border-gray-100 border-dashed text-center p-12">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-6 text-slate-300">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <h3 class="text-xl font-black text-slate-900 mb-2"><?php echo e(__('Select any specialty')); ?></h3>
                <p class="text-gray-500 max-w-xs"><?php echo e(__('Choose a specialty from the left to manage its custom metadata fields.')); ?></p>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- Specialty Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showSpecialtyModal): ?>
    <div class="fixed inset-0 z-[60] overflow-y-auto flex justify-center items-start p-4 sm:p-6">
        <div wire:click="$set('showSpecialtyModal', false)" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
        <div class="bg-white rounded-[2.5rem] w-full max-w-lg shadow-2xl relative mx-auto my-8 animate-zoom-in">
            <div class="p-10">
                <h3 class="text-2xl font-black text-slate-900 mb-8"><?php echo e($editingSpecialtyId ? __('Edit Specialty') : __('New Specialty')); ?></h3>
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-500 uppercase tracking-widest"><?php echo e(__('Specialty Name')); ?></label>
                        <input type="text" wire:model="specialtyName" class="w-full bg-slate-50 border-gray-100 rounded-2xl py-4 px-6 text-sm focus:ring-4 focus:ring-purple-500/10 transition-all" placeholder="<?php echo e(__('e.g., Cardiology, Pediatrics...')); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['specialtyName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-rose-500 text-xs font-bold"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="pt-6 flex gap-4">
                        <button type="button" wire:click="$set('showSpecialtyModal', false)" class="flex-1 py-4 text-slate-500 font-bold hover:text-slate-700 transition-colors"><?php echo e(__('Cancel')); ?></button>
                        <button wire:click="saveSpecialty" class="flex-[2] py-4 bg-slate-900 text-white rounded-2xl font-black shadow-xl hover:bg-black transition-all">
                            <?php echo e(__('Save Specialty')); ?>

                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Field Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showFieldModal): ?>
    <div class="fixed inset-0 z-[60] overflow-y-auto flex justify-center items-start p-4 sm:p-6">
        <div wire:click="$set('showFieldModal', false)" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
        <div class="bg-white rounded-[2.5rem] w-full max-w-xl shadow-2xl relative mx-auto my-8 animate-zoom-in">
            <div class="p-10">
                <h3 class="text-2xl font-black text-slate-900 mb-8"><?php echo e($editingFieldId ? __('Edit Field') : __('Add New Field')); ?></h3>
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-500 uppercase tracking-widest"><?php echo e(__('Field Label')); ?></label>
                        <input type="text" wire:model="fieldLabel" class="w-full bg-slate-50 border-gray-100 rounded-2xl py-4 px-6 text-sm focus:ring-4 focus:ring-purple-500/10 transition-all" placeholder="<?php echo e(__('e.g., Blood Pressure, Symptoms...')); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['fieldLabel'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-rose-500 text-xs font-bold"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-500 uppercase tracking-widest"><?php echo e(__('Field Type')); ?></label>
                        <div class="grid grid-cols-3 gap-4">
                            <button wire:click="$set('fieldType', 'text')" 
                                    class="p-4 rounded-2xl border-2 transition-all flex flex-col items-center gap-2 <?php echo e($fieldType === 'text' ? 'bg-purple-50 border-purple-600 text-purple-700' : 'bg-slate-50 border-transparent text-slate-400 grayscale'); ?>">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                <span class="text-[10px] font-black"><?php echo e(__('Text Field')); ?></span>
                            </button>
                            <button wire:click="$set('fieldType', 'select')" 
                                    class="p-4 rounded-2xl border-2 transition-all flex flex-col items-center gap-2 <?php echo e($fieldType === 'select' ? 'bg-purple-50 border-purple-600 text-purple-700' : 'bg-slate-50 border-transparent text-slate-400 grayscale'); ?>">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                                <span class="text-[10px] font-black"><?php echo e(__('Single Choice')); ?></span>
                            </button>
                            <button wire:click="$set('fieldType', 'multi_select')" 
                                    class="p-4 rounded-2xl border-2 transition-all flex flex-col items-center gap-2 <?php echo e($fieldType === 'multi_select' ? 'bg-purple-50 border-purple-600 text-purple-700' : 'bg-slate-50 border-transparent text-slate-400 grayscale'); ?>">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                <span class="text-[10px] font-black"><?php echo e(__('Multi-Select')); ?></span>
                            </button>
                        </div>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($fieldType, ['select', 'multi_select'])): ?>
                    <div class="space-y-4 pt-4 border-t border-dashed border-gray-100 animate-slide-in">
                        <label class="text-xs font-black text-gray-500 uppercase tracking-widest"><?php echo e(__('Options')); ?></label>
                        <div class="flex gap-2">
                            <input type="text" wire:model="newOption" wire:keydown.enter="addOption" class="flex-1 bg-slate-50 border-gray-100 rounded-2xl py-4 px-6 text-sm focus:ring-4 focus:ring-purple-500/10 transition-all" placeholder="<?php echo e(__('Add option...')); ?>">
                            <button wire:click="addOption" class="px-6 bg-purple-600 text-white rounded-2xl font-black text-sm hover:bg-purple-700 transition-all">
                                <?php echo e(__('Add')); ?>

                            </button>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newOption'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-rose-500 text-xs font-bold"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <div class="flex flex-wrap gap-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $fieldOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <div class="bg-purple-50 border border-purple-100 px-4 py-2 rounded-xl text-sm font-bold text-purple-700 flex items-center gap-3 group">
                                <?php echo e($opt); ?>

                                <button wire:click="removeOption(<?php echo e($index); ?>)" class="text-purple-300 hover:text-rose-500 transition-colors">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                </button>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="pt-6 flex gap-4">
                        <button type="button" wire:click="$set('showFieldModal', false)" class="flex-1 py-4 text-slate-500 font-bold hover:text-slate-700 transition-colors"><?php echo e(__('Cancel')); ?></button>
                        <button wire:click="saveField" class="flex-[2] py-4 bg-purple-600 text-white rounded-2xl font-black shadow-xl shadow-purple-100 hover:bg-purple-700 transition-all">
                            <?php echo e(__('Save Field')); ?>

                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\Bola\Clinova\resources\views\livewire/dashboard/specialty-manager.blade.php ENDPATH**/ ?>