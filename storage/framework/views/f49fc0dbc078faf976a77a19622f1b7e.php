<table class="w-full text-right" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>" id="<?php echo e($tableId ?? 'appointments-table'); ?>">
    <thead class="bg-gray-50 border-b border-gray-100 text-gray-500">
        <tr>
            <th class="px-4 py-4 text-sm font-bold w-12 text-center">#</th>
            <th class="px-4 py-4 text-sm font-bold w-32"><?php echo e(__('Date & Time')); ?></th>
            <th class="px-4 py-4 text-sm font-bold"><?php echo e(__('Patient')); ?></th>
            <th class="px-4 py-4 text-sm font-bold text-center"><?php echo e(__('Visit Type')); ?></th>
            <th class="px-4 py-4 text-sm font-bold text-center"><?php echo e(__('Status')); ?></th>
            <th class="px-4 py-4 text-sm font-bold text-center"><?php echo e(__('Actions')); ?></th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-50"
           x-data="queueSortable()"
           @dragover.prevent="dragOver($event)"
           @drop.prevent="drop($event, $wire)">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $appointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $appointment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
        <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processElementKey('appointment-{{ $appointment->id }}-{{ $tableId ?? \'table\' }}', get_defined_vars()); ?>wire:key="appointment-<?php echo e($appointment->id); ?>-<?php echo e($tableId ?? 'table'); ?>"
            class="hover:bg-purple-50/30 transition-colors <?php echo e($appointment->status === 'seen' ? 'opacity-70' : ''); ?>"
            data-id="<?php echo e($appointment->id); ?>"
            draggable="true"
            @dragstart="dragStart($event, <?php echo e($appointment->id); ?>)"
            @dragend="dragEnd($event)">
            <td class="px-4 py-4 text-center text-xs font-bold text-gray-400">
                <?php echo e($loop->iteration); ?>

            </td>
            <td class="px-4 py-4">
                <div class="font-bold text-purple-700"><?php echo e($appointment->scheduled_at->format('H:i')); ?></div>
                <div class="text-xs text-gray-500"><?php echo e($appointment->scheduled_at->format('Y-m-d')); ?></div>
            </td>
            <td class="px-4 py-4">
                <a href="<?php echo e(route('patients.show', $appointment->patient_id)); ?>" class="font-bold text-gray-900 hover:text-purple-600 transition-colors block">
                    <?php echo e($appointment->patient->name); ?>

                </a>
                <span class="text-xs text-gray-500" dir="ltr"><?php echo e($appointment->patient->phone); ?></span>
            </td>
            <td class="px-4 py-4 text-center">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($appointment->type === 'follow_up'): ?>
                    <span class="inline-flex items-center px-2.5 py-1 bg-teal-50 text-teal-700 rounded-md text-[11px] font-bold border border-teal-100">
                        <?php echo e(__('Follow-up')); ?>

                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-md text-[11px] font-bold border border-indigo-100">
                        <?php echo e(__('Checkup')); ?>

                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </td>
            <td class="px-4 py-4 text-center">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($appointment->status === 'checked-in'): ?>
                    <span class="inline-flex items-center px-3 py-1 bg-blue-50 text-blue-700 rounded-lg text-xs font-bold border border-blue-100">
                        <div class="w-1.5 h-1.5 bg-blue-500 rounded-full me-1.5"></div>
                        <?php echo e(__('Checked In')); ?>

                    </span>
                <?php elseif($appointment->status === 'seen'): ?>
                    <span class="inline-flex items-center px-3 py-1 bg-green-50 text-green-700 rounded-lg text-xs font-bold border border-green-100">
                        <div class="w-1.5 h-1.5 bg-green-500 rounded-full me-1.5"></div>
                        <?php echo e(__('Seen')); ?>

                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center px-3 py-1 bg-amber-50 text-amber-700 rounded-lg text-xs font-bold border border-amber-100">
                        <div class="w-1.5 h-1.5 bg-amber-500 rounded-full me-1.5 animate-pulse"></div>
                        <?php echo e(__('Pending')); ?>

                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </td>
            <td class="px-4 py-4 text-center">
                <div class="flex items-center justify-center gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($appointment->status !== 'seen'): ?>
                        <button wire:click="markAsSeen(<?php echo e($appointment->id); ?>)" class="p-2 text-emerald-600 bg-emerald-50 hover:bg-emerald-100 hover:text-emerald-700 rounded-lg transition-colors shadow-sm border border-emerald-100" title="<?php echo e(__('Mark as Completed')); ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_array($appointment->audit_log) && count($appointment->audit_log) > 0): ?>
                        <?php
                            $logs = $appointment->audit_log;
                            $lastLog = is_array($logs) ? end($logs) : null;
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($lastLog['action'])): ?>
                            <div class="text-[10px] text-gray-500 group relative inline-block cursor-help bg-gray-100 p-2 rounded-lg ml-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                
                                <!-- Tooltip -->
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block w-48 bg-gray-900 text-white text-[10px] rounded-lg p-2 shadow-xl z-20 text-left" dir="ltr">
                                    <div><strong>Action:</strong> <?php echo e($lastLog['action']); ?></div>
                                    <div><strong>By:</strong> <?php echo e($lastLog['by_user_name'] ?? $lastLog['user_name'] ?? 'System'); ?></div>
                                    <div><strong>Time:</strong> <?php echo e(\Carbon\Carbon::parse($lastLog['timestamp'])->format('Y-m-d H:i')); ?></div>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </td>
        </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <tr>
            <td colspan="6" class="px-6 py-12 text-center">
                <div class="flex flex-col items-center justify-center space-y-3">
                    <div class="w-16 h-16 bg-gradient-to-tr from-gray-50 to-gray-100 text-gray-400 rounded-full flex items-center justify-center shadow-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <p class="text-gray-500 text-sm"><?php echo e(__('No appointments found in this category')); ?></p>
                </div>
            </td>
        </tr>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </tbody>
</table>
<?php /**PATH C:\Bola\Clinova\resources\views/livewire/shared/partials/appointments-table.blade.php ENDPATH**/ ?>