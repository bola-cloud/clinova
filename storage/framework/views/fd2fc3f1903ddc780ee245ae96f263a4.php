<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight"><?php echo e(__('Income Statistics')); ?></h1>
            <p class="text-gray-500 mt-1"><?php echo e(__('Track clinic revenue from completed appointments.')); ?></p>
        </div>
        
        <div class="flex flex-wrap items-center gap-4">
            <!-- Doctor Filter (Admin Only) -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->role === 'admin'): ?>
            <div wire:ignore class="flex items-center gap-2 bg-white p-2 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex items-center gap-2 px-2" 
                     x-data="{
                        initSelect2() {
                            $(this.$refs.select).select2({
                                width: 'resolve',
                                dropdownAutoWidth: true
                            });
                            $(this.$refs.select).on('change', (e) => {
                                window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('doctorId', e.target.value);
                            });
                        }
                     }" 
                     x-init="initSelect2()">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider"><?php echo e(__('Doctor')); ?></span>
                    <select x-ref="select" class="border-none bg-transparent text-sm font-bold text-gray-700 focus:ring-0 p-0 cursor-pointer min-w-[150px]">
                        <option value=""><?php echo e(__('All Doctors')); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <option value="<?php echo e($doctor->id); ?>"><?php echo e($doctor->name); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <!-- Date Filters -->
            <div class="flex items-center gap-2 bg-white p-2 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex items-center gap-2 px-2">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider"><?php echo e(__('From')); ?></span>
                    <input type="date" wire:model.live="dateFrom" class="border-none bg-transparent text-sm font-bold text-gray-700 focus:ring-0 p-0 cursor-pointer">
                </div>
                <div class="w-px h-6 bg-gray-200"></div>
                <div class="flex items-center gap-2 px-2">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider"><?php echo e(__('To')); ?></span>
                    <input type="date" wire:model.live="dateTo" class="border-none bg-transparent text-sm font-bold text-gray-700 focus:ring-0 p-0 cursor-pointer">
                </div>
                <button wire:click="$set('dateFrom', '<?php echo e(now()->startOfMonth()->format('Y-m-d')); ?>'); $set('dateTo', '<?php echo e(now()->endOfMonth()->format('Y-m-d')); ?>')" class="p-2 hover:bg-slate-100 rounded-xl transition-colors text-purple-600" title="<?php echo e(__('This Month')); ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Income -->
        <div class="bg-gradient-to-br from-indigo-600 to-purple-600 rounded-3xl p-6 text-white shadow-xl shadow-indigo-200 relative overflow-hidden group">
            <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-white opacity-10 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-indigo-100 text-sm font-bold tracking-wider uppercase mb-1"><?php echo e(__('Total Income')); ?></p>
                    <h3 class="text-3xl font-black"><?php echo e(number_format($totalIncome, 2)); ?> <span class="text-lg font-medium opacity-80"><?php echo e(__('EGP')); ?></span></h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Checkups Income -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-400 text-xs font-bold tracking-wider uppercase mb-1"><?php echo e(__('Checkups Revenue')); ?></p>
                    <h3 class="text-2xl font-black text-gray-800"><?php echo e(number_format($checkupIncome, 2)); ?> <span class="text-sm text-gray-400 font-medium"><?php echo e(__('EGP')); ?></span></h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center border border-indigo-100">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Follow-ups Income -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-teal-500"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-400 text-xs font-bold tracking-wider uppercase mb-1"><?php echo e(__('Follow-ups Revenue')); ?></p>
                    <h3 class="text-2xl font-black text-gray-800"><?php echo e(number_format($followupIncome, 2)); ?> <span class="text-sm text-gray-400 font-medium"><?php echo e(__('EGP')); ?></span></h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center border border-teal-100">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </div>
            </div>
        </div>

        <!-- Total Patients Seen -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-400 text-xs font-bold tracking-wider uppercase mb-1"><?php echo e(__('Completed Visits')); ?></p>
                    <h3 class="text-2xl font-black text-gray-800"><?php echo e($appointmentsCount); ?> <span class="text-sm text-gray-400 font-medium"><?php echo e(__('Patients')); ?></span></h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center border border-blue-100">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Breakdown Chart -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
        <h3 class="text-lg font-bold text-gray-800 mb-6"><?php echo e(__('Daily Revenue Breakdown')); ?></h3>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($chartData) > 0 && array_sum($chartData) > 0): ?>
            <div class="h-64 flex items-end gap-2 px-2 pb-6 border-b border-gray-100 relative">
                <?php
                    $maxVal = max($chartData);
                ?>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $chartData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <?php
                        $heightPercentage = $maxVal > 0 ? ($val / $maxVal) * 100 : 0;
                        $label = $chartLabels[$index];
                    ?>
                    <div class="flex-1 flex flex-col items-center justify-end relative group h-full">
                        <div class="w-full bg-indigo-100 rounded-t-sm hover:bg-indigo-400 transition-colors relative" style="height: <?php echo e(max($heightPercentage, 2)); ?>%">
                            <!-- Tooltip -->
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block z-10 whitespace-nowrap bg-gray-900 text-white text-xs py-1 px-2 rounded-lg shadow-xl">
                                <?php echo e($val); ?> EGP
                            </div>
                        </div>
                        <!-- Axis Label -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($index % ceil(count($chartData) / 10) === 0 || $index === count($chartData) - 1): ?>
                            <span class="absolute top-full mt-2 text-[10px] text-gray-400 font-medium transform -rotate-45 origin-top-left rtl:origin-top-right whitespace-nowrap"><?php echo e($label); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        <?php else: ?>
            <div class="flex flex-col items-center justify-center h-64 text-gray-400">
                <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 12H4M20 12L14 18M20 12L14 6"></path></svg>
                <p><?php echo e(__('No income recorded for this period')); ?></p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Bola\Clinova\resources\views/livewire/admin/income-statistics.blade.php ENDPATH**/ ?>