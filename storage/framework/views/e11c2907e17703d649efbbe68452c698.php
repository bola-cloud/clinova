<div class="p-6 space-y-8 animate-fade-in text-right" dir="rtl">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 bg-gradient-to-tr from-purple-600 to-indigo-700 text-white rounded-3xl flex items-center justify-center font-black text-2xl shadow-xl border-4 border-white">
                <?php echo e(mb_substr($doctor->name, 0, 1)); ?>

            </div>
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight"><?php echo e(__('Manage Subscription')); ?></h1>
                <p class="text-slate-500 font-bold flex items-center gap-2">
                    <span class="text-indigo-600"><?php echo e($doctor->name); ?></span>
                    <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                    <span><?php echo e($doctor->email); ?></span>
                </p>
            </div>
        </div>
        <a href="<?php echo e(route('admin.dashboard')); ?>" wire:navigate class="px-6 py-3 bg-white text-slate-900 border border-slate-200 rounded-2xl font-black text-sm flex items-center gap-2 hover:bg-slate-50 transition-all shadow-sm">
            <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l7-7-7-7"></path></svg>
            <?php echo e(__('Back to Dashboard')); ?>

        </a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-bold"><?php echo e(session('success')); ?></span>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Current Status & New Subscription Form -->
        <div class="lg:col-span-1 space-y-8">
            <!-- Current Status Card -->
            <div class="bg-slate-900 text-white p-8 rounded-[2.5rem] shadow-2xl relative overflow-hidden group">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-purple-500/10 rounded-full blur-3xl group-hover:bg-purple-500/20 transition-all duration-700"></div>
                
                <div class="relative z-10 space-y-6">
                    <div class="flex justify-between items-center">
                        <span class="px-4 py-1 bg-white/10 backdrop-blur-md rounded-full text-[10px] font-black uppercase tracking-widest border border-white/10">
                            <?php echo e(__('Current Status')); ?>

                        </span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($doctor->subscription_active): ?>
                            <span class="w-3 h-3 bg-emerald-500 rounded-full shadow-[0_0_15px_rgba(16,185,129,0.5)] animate-pulse"></span>
                        <?php else: ?>
                            <span class="w-3 h-3 bg-rose-500 rounded-full shadow-[0_0_15px_rgba(244,63,94,0.5)]"></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div>
                        <h4 class="text-slate-400 text-xs font-black uppercase tracking-widest mb-1"><?php echo e(__('Subscription Plan')); ?></h4>
                        <p class="text-3xl font-black capitalize tracking-tight"><?php echo e(__($doctor->subscription_plan ?: 'None')); ?></p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h4 class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1"><?php echo e(__('Expiry Date')); ?></h4>
                            <p class="text-sm font-black"><?php echo e($doctor->subscription_expires_at?->format('Y-m-d') ?: 'N/A'); ?></p>
                        </div>
                        <div>
                            <h4 class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1"><?php echo e(__('Payment Status')); ?></h4>
                            <p class="text-sm font-black <?php echo e($doctor->is_paid ? 'text-emerald-400' : 'text-amber-400'); ?>">
                                <?php echo e($doctor->is_paid ? __('Collected') : __('Not Collected')); ?>

                            </p>
                        </div>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($doctor->subscription_expires_at): ?>
                        <?php
                            $daysLeft = now()->diffInDays($doctor->subscription_expires_at, false);
                        ?>
                        <div class="pt-4 border-t border-white/10">
                            <div class="flex justify-between text-[10px] font-black uppercase mb-2">
                                <span><?php echo e(__('Days Remaining')); ?></span>
                                <span class="<?php echo e($daysLeft > 5 ? 'text-emerald-400' : 'text-rose-400'); ?>"><?php echo e(max(0, $daysLeft)); ?> <?php echo e(__('Days')); ?></span>
                            </div>
                            <div class="h-2 bg-white/5 rounded-full overflow-hidden">
                                <div class="h-full <?php echo e($daysLeft > 5 ? 'bg-emerald-500' : 'bg-rose-500'); ?> rounded-full transition-all duration-1000" style="width: <?php echo e(min(100, max(0, ($daysLeft / 30) * 100))); ?>%"></div>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <!-- New Subscription Form -->
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-100/50">
                <h3 class="text-xl font-black text-slate-900 mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    <?php echo e(__('Extend Subscription')); ?>

                </h3>

                <form wire:submit="saveSubscription" class="space-y-5">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mr-1"><?php echo e(__('Plan')); ?></label>
                        <select wire:model.live="plan" class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-5 text-sm font-black focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                            <option value="trial"><?php echo e(__('Free Trial')); ?></option>
                            <option value="monthly"><?php echo e(__('Standard Monthly')); ?></option>
                            <option value="yearly"><?php echo e(__('Standard Yearly')); ?></option>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['plan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-rose-500 text-[10px] font-black"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mr-1"><?php echo e(__('Start Date')); ?></label>
                        <input type="date" wire:model="start_date" class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-5 text-sm font-black focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['start_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-rose-500 text-[10px] font-black"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan === 'trial'): ?>
                    <div class="space-y-2 animate-slide-down">
                        <label class="text-[10px] font-black text-amber-600 uppercase tracking-widest mr-1"><?php echo e(__('Trial Duration (Days)')); ?></label>
                        <input type="number" wire:model="trial_days" class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-5 text-sm font-black focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['trial_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-rose-500 text-[10px] font-black"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mr-1"><?php echo e(__('Payment Price')); ?> (EGP)</label>
                        <input type="number" step="0.01" wire:model="price" class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-5 text-sm font-black focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-rose-500 text-[10px] font-black"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100 group transition-all hover:bg-emerald-50/50 hover:border-emerald-100">
                        <input type="checkbox" wire:model="is_paid" class="w-5 h-5 rounded-lg text-emerald-600 focus:ring-emerald-500 border-slate-300 transition-all">
                        <span class="text-sm font-black text-slate-900 group-hover:text-emerald-700 transition-all"><?php echo e(__('Payment Collected')); ?></span>
                    </div>

                    <button type="submit" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black text-sm shadow-xl shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-1 active:translate-y-0 transition-all flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        <?php echo e(__('Confirm Subscription')); ?>

                    </button>
                </form>
            </div>
        </div>

        <!-- History Table -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl overflow-hidden h-full">
                <div class="p-8 border-b border-slate-50 bg-slate-50/30 flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-black text-slate-900 tracking-tight"><?php echo e(__('Subscription History')); ?></h2>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1"><?php echo e(__('Past payments & upcoming slots')); ?></p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right">
                        <thead class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">
                            <tr>
                                <th class="px-8 py-5"><?php echo e(__('Plan')); ?></th>
                                <th class="px-8 py-5"><?php echo e(__('Period')); ?></th>
                                <th class="px-8 py-5 text-center"><?php echo e(__('Amount')); ?></th>
                                <th class="px-8 py-5 text-center"><?php echo e(__('Status')); ?></th>
                                <th class="px-8 py-5 text-center"><?php echo e(__('Actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <tr class="hover:bg-indigo-50/20 transition-all group">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center
                                            <?php echo e($record->plan_name === 'trial' ? 'bg-amber-100 text-amber-600' : ''); ?>

                                            <?php echo e($record->plan_name === 'monthly' ? 'bg-indigo-100 text-indigo-600' : ''); ?>

                                            <?php echo e($record->plan_name === 'yearly' ? 'bg-purple-100 text-purple-600' : ''); ?>

                                        ">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($record->plan_name === 'trial'): ?>
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <?php else: ?>
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <span class="font-black text-slate-900 capitalize"><?php echo e(__($record->plan_name)); ?></span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-slate-900" dir="ltr">
                                            <?php echo e($record->start_date->format('Y-m-d')); ?> → <?php echo e($record->end_date->format('Y-m-d')); ?>

                                        </span>
                                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">
                                            <?php echo e($record->start_date->diffInDays($record->end_date)); ?> <?php echo e(__('Days')); ?>

                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center font-black text-slate-900 tracking-tighter">
                                    <?php echo e(number_format($record->amount, 2)); ?> <span class="text-[10px] text-slate-400">EGP</span>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <button wire:click="togglePaidStatus(<?php echo e($record->id); ?>)" class="group/paid relative inline-flex">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($record->is_paid): ?>
                                            <span class="px-4 py-1.5 bg-emerald-100 text-emerald-700 text-[10px] font-black rounded-full uppercase tracking-widest shadow-sm group-hover/paid:bg-emerald-600 group-hover/paid:text-white transition-all">
                                                <?php echo e(__('Paid Status')); ?>

                                            </span>
                                        <?php else: ?>
                                            <span class="px-4 py-1.5 bg-rose-100 text-rose-700 text-[10px] font-black rounded-full uppercase tracking-widest shadow-sm group-hover/paid:bg-rose-600 group-hover/paid:text-white transition-all">
                                                <?php echo e(__('Pending Status')); ?>

                                            </span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </button>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <button onclick="confirm('<?php echo e(__('Are you sure?')); ?>') || event.stopImmediatePropagation()" 
                                            wire:click="deleteSubscription(<?php echo e($record->id); ?>)" 
                                            class="p-2 text-slate-300 hover:text-rose-500 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </td>
                            </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="5" class="px-8 py-32 text-center">
                                    <div class="flex flex-col items-center gap-4 text-slate-300">
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="font-black italic text-lg uppercase tracking-widest"><?php echo e(__('No history recorded for this doctor.')); ?></p>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($history->hasPages()): ?>
                <div class="p-8 border-t border-slate-50 bg-slate-50/30">
                    <?php echo e($history->links()); ?>

                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\Bola\Clinova\resources\views/livewire/admin/doctor-subscription-manager.blade.php ENDPATH**/ ?>