<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(isset($title) ? __($title) : 'Clinova'); ?></title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('Clinova Logo.png')); ?>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        body { font-family: 'Outfit', 'Cairo', sans-serif; }
        .glass-panel { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.05); }
        
        @keyframes morph {
            0% { border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; }
            50% { border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%; }
            100% { border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; }
        }
        
        @keyframes drift {
            0% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(30px, -50px) rotate(10deg); }
            100% { transform: translate(0, 0) rotate(0deg); }
        }

        @keyframes sweep {
            0% { transform: translateX(-100%) skewX(-15deg); opacity: 0; }
            50% { opacity: 0.1; }
            100% { transform: translateX(200%) skewX(-15deg); opacity: 0; }
        }

        .animate-morph { animation: morph 8s ease-in-out infinite; }
        .animate-drift { animation: drift 15s ease-in-out infinite; }
        .navbar-sweep::after {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 30%; height: 100%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.4), transparent);
            animation: sweep 5s infinite;
        }
        [x-cloak] { display: none !important; }
        .p-10.pb-4.flex.flex-col.items-center.gap-4{
            margin: 20px;
        }

        /* Premium Select2 Styling */
        .select2-container--default .select2-selection--single {
            background-color: transparent;
            border: none;
            height: auto;
            display: flex;
            align-items: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #374151; /* gray-700 */
            font-weight: 700;
            font-size: 0.875rem; /* text-sm */
            padding-left: 0;
            padding-right: 20px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 20px;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
        }
        .select2-dropdown {
            border-radius: 1.5rem;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            padding: 0.5rem;
        }
        .select2-results__option {
            border-radius: 0.75rem;
            margin-bottom: 2px;
            padding: 8px 12px;
            font-size: 0.875rem;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #6366f1; /* indigo-500 */
        }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="bg-[#f8fafc] text-slate-800 overflow-x-hidden selection:bg-purple-500 selection:text-white" x-data="{ mobileMenuOpen: false }">
    <div class="flex h-screen overflow-hidden">
            <?php
                $role = auth()->user()->role;
                $links = [];
                if ($role === 'admin') {
                    $links = [
                        ['name' => __('Doctor Management'), 'route' => 'admin.dashboard', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                        ['name' => __('Global Patient Archive'), 'route' => 'admin.patients', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                        ['name' => __('Income Statistics'), 'route' => 'admin.statistics', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                        ['name' => __('System Revenue'), 'route' => 'admin.revenue', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['name' => __('Control Panel'), 'route' => 'admin.settings', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 002.572-1.065z']
                    ];
                } elseif ($role === 'doctor') {
                    $links = [
                        ['name' => __('Dashboard'), 'route' => 'doctor.dashboard', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                        ['name' => __('Waitlist'), 'route' => 'appointments.index', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['name' => __('Patient Archive'), 'route' => 'patients.index', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ['name' => __('Income Statistics'), 'route' => 'doctor.statistics', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                        ['name' => __('Settings'), 'route' => 'doctor.settings', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 002.572-1.065z']
                    ];
                } elseif ($role === 'secretary') {
                    $links = [
                        ['name' => __('Dashboard'), 'route' => 'secretary.dashboard', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                        ['name' => __('Bookings'), 'route' => 'appointments.index', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['name' => __('Patients Archive'), 'route' => 'patients.index', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                        ['name' => __('Settings'), 'route' => 'secretary.settings', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 002.572-1.065z']
                    ];
                }
            ?>
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileMenuOpen = false"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[55] md:hidden"></div>

        <!-- Mobile Sidebar -->
        <aside x-show="mobileMenuOpen"
               x-cloak
               x-transition:enter="transition ease-out duration-300 transform"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200 transform"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full"
               class="fixed inset-y-0 left-0 w-72 bg-gradient-to-b from-[#8A2BE2] via-[#4A26AB] to-[#0C3E8A] text-white z-[60] flex flex-col shadow-2xl md:hidden"
               dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
            <!-- Reusing Sidebar Content -->
            <div class="p-10 pb-4 flex flex-col items-center gap-4">
                <div class="w-20 h-20 bg-white rounded-[2rem] flex items-center justify-center shadow-lg overflow-hidden relative">
                    <img src="<?php echo e(asset('Clinova Logo.png')); ?>" alt="Clinova" class="w-14 h-14 object-contain">
                </div>
                <div class="text-center">
                    <h1 class="text-2xl font-black text-white leading-none">
                        <?php echo e(\App\Models\Setting::get('clinic_name', 'Clinova')); ?>

                    </h1>
                    <p class="text-[8px] text-purple-200 mt-1 uppercase tracking-widest font-black italic"><?php echo e(__('Smart Clinic')); ?></p>
                </div>
            </div>

            <nav class="flex-1 px-4 space-y-2 mt-6 overflow-y-auto">
                <div class="px-4 text-xs font-bold text-purple-300 uppercase tracking-wider mb-4 opacity-70"><?php echo e(__('Main Menu')); ?></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <a href="<?php echo e(route($link['route'])); ?>" 
                       class="flex items-center gap-4 px-4 py-3.5 rounded-2xl transition-all duration-300 font-medium
                              <?php echo e(request()->routeIs($link['route']) ? 'bg-white text-[#4A26AB]' : 'text-purple-100 hover:bg-white/10'); ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($link['icon']); ?>"></path></svg>
                        <span><?php echo e($link['name']); ?></span>
                    </a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </nav>

            <div class="p-6 border-t border-white/10 glass-panel m-4 rounded-3xl mt-auto">
                <div class="grid grid-cols-2 gap-2">
                    <a href="<?php echo e(route('lang.switch', 'ar')); ?>" class="flex items-center justify-center p-2 rounded-xl text-xs font-bold <?php echo e(app()->getLocale() === 'ar' ? 'bg-white text-[#4A26AB]' : 'bg-white/5 text-purple-100'); ?>">العربية</a>
                    <a href="<?php echo e(route('lang.switch', 'en')); ?>" class="flex items-center justify-center p-2 rounded-xl text-xs font-bold <?php echo e(app()->getLocale() === 'en' ? 'bg-white text-[#4A26AB]' : 'bg-white/5 text-purple-100'); ?>">English</a>
                </div>
            </div>
        </aside>

        <!-- Premium Desktop Sidebar -->
        <aside class="w-72 bg-gradient-to-b from-[#8A2BE2] via-[#4A26AB] to-[#0C3E8A] text-white shrink-0 hidden md:flex flex-col shadow-2xl relative z-20">
            <!-- Logo Section -->
            <div class="p-10 pb-4 flex flex-col items-center gap-4">
                <div class="w-24 h-24 bg-white rounded-[2rem] flex items-center justify-center shadow-[0_15px_45px_rgba(0,0,0,0.2)] border-2 border-white/20 group hover:scale-110 transition-all duration-500 overflow-hidden relative group/logo">
                    <div class="absolute inset-0 bg-gradient-to-br from-white via-white to-purple-50 opacity-0 group-hover/logo:opacity-100 transition-opacity"></div>
                    <img src="<?php echo e(asset('Clinova Logo.png')); ?>" alt="Clinova" class="w-16 h-16 object-contain relative z-10 drop-shadow-lg group-hover/logo:rotate-3 transition-transform">
                </div>
                <div class="text-center">
                    <h1 class="text-3xl font-black tracking-tight text-white leading-none drop-shadow-md">
                        <?php echo e(\App\Models\Setting::get('clinic_name', 'Clinova')); ?>

                    </h1>
                    <p class="text-[10px] text-purple-200 mt-1 uppercase tracking-[0.3em] opacity-80 font-black italic"><?php echo e(__('Smart Clinic')); ?></p>
                </div>
            </div>

            <nav class="flex-1 px-4 space-y-2 mt-6 overflow-y-auto custom-scrollbar">
                <div class="px-4 text-xs font-bold text-purple-300 uppercase tracking-wider mb-4 opacity-70"><?php echo e(__('Main Menu')); ?></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <a href="<?php echo e(route($link['route'])); ?>" 
                       class="flex items-center gap-4 px-4 py-3.5 rounded-2xl transition-all duration-300 font-medium group
                              <?php echo e(request()->routeIs($link['route']) 
                                  ? 'bg-white text-[#4A26AB] shadow-[0_8px_30px_rgb(255,255,255,0.12)]' 
                                  : 'text-purple-100 hover:bg-white/10 hover:translate-x-1'); ?>">
                        <svg class="w-5 h-5 <?php echo e(request()->routeIs($link['route']) ? 'text-[#4A26AB]' : 'text-purple-300 group-hover:text-white'); ?> transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($link['icon']); ?>"></path>
                        </svg>
                        <span><?php echo e($link['name']); ?></span>
                    </a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </nav>

            <div class="p-6 border-t border-white/10 space-y-4 glass-panel m-4 rounded-3xl mt-auto">
                <?php
                    $user = auth()->user();
                    $displayUser = $user->isSecretary() ? $user->assignedDoctor : $user;
                    $profileImage = $displayUser?->profile_image;
                ?>
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-purple-400 to-pink-300 flex items-center justify-center font-bold text-white shadow-inner border border-white/20 overflow-hidden">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profileImage): ?>
                            <img src="<?php echo e(asset('storage/' . $profileImage)); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?php echo e($displayUser->name[0] ?? ($user->name[0] ?? 'U')); ?>

                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold truncate text-white"><?php echo e(auth()->user()->name ?? 'User'); ?></p>
                        <p class="text-xs text-purple-200 truncate font-medium">
                            <?php echo e($role === 'admin' ? __('Administration') : ($role === 'doctor' ? __('Medical Staff') : __('Desk Staff'))); ?>

                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 mt-4">
                    <a href="<?php echo e(route('lang.switch', 'ar')); ?>" class="flex items-center justify-center p-2 rounded-xl text-xs font-bold <?php echo e(app()->getLocale() === 'ar' ? 'bg-white text-[#4A26AB] shadow-md' : 'bg-white/5 text-purple-100 hover:bg-white/20'); ?> transition-all">
                        العربية
                    </a>
                    <a href="<?php echo e(route('lang.switch', 'en')); ?>" class="flex items-center justify-center p-2 rounded-xl text-xs font-bold <?php echo e(app()->getLocale() === 'en' ? 'bg-white text-[#4A26AB] shadow-md' : 'bg-white/5 text-purple-100 hover:bg-white/20'); ?> transition-all">
                        English
                    </a>
                </div>
                
                <form method="POST" action="<?php echo e(route('logout')); ?>" class="mt-2">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="w-full flex items-center justify-center gap-2 p-3 hover:bg-white/10 rounded-xl text-rose-200 hover:text-rose-100 transition-colors text-sm font-bold border border-rose-300/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span><?php echo e(__('Logout')); ?></span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 bg-[#f4f7fc] overflow-y-auto overflow-x-hidden relative">
            <!-- Decorative Background Blobs (Soft Radial Gradients) -->
            <div class="fixed top-0 right-0 w-[800px] h-[800px] animate-morph animate-drift -z-10 pointer-events-none opacity-50 blur-3xl" style="background: radial-gradient(circle, rgba(139, 92, 246, 0.08) 0%, transparent 70%);"></div>
            <div class="fixed bottom-0 left-0 w-[700px] h-[700px] animate-morph animate-drift -z-10 pointer-events-none opacity-40 blur-3xl" style="background: radial-gradient(circle, rgba(59, 130, 246, 0.06) 0%, transparent 70%); animation-delay: -5s;"></div>
            <div class="absolute top-0 right-0 w-[1000px] h-[1000px] -z-10 pointer-events-none -translate-y-1/2 translate-x-1/3 blur-[100px]" style="background: radial-gradient(circle, rgba(99, 102, 241, 0.05) 0%, transparent 70%);"></div>
            
            <!-- Header -->
            <header class="h-24 bg-white/95 backdrop-blur-2xl border-b border-gray-100 flex items-center px-8 md:px-12 sticky top-0 z-40 shadow-sm transition-all duration-300">
                <div class="flex items-center justify-between w-full h-full py-2 relative">
                    <!-- Left: Title & Mobile Toggle -->
                    <div class="flex items-center gap-4 w-1/4">
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-gray-500 hover:text-[#4A26AB] transition-colors rounded-xl hover:bg-purple-50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <div class="hidden lg:flex flex-col">
                            <h2 class="text-[10px] font-black text-purple-600 tracking-[0.3em] uppercase opacity-50">
                                <?php echo e(isset($title) ? __($title) : __('Dashboard')); ?>

                            </h2>
                        </div>
                    </div>
                    
                    <!-- Center: Centered Doctor Name -->
                    <div class="flex flex-col items-center justify-center text-center cursor-default w-2/4">
                        <span class="text-[10px] font-black text-purple-600/60 uppercase tracking-widest leading-none mb-1">
                            <?php echo e(__('Welcome Doctor')); ?>

                        </span>
                        <h1 class="text-2xl font-black tracking-tight leading-none text-slate-900">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isSecretary()): ?>
                                <?php echo e(__('Dr.')); ?> <?php echo e(auth()->user()->assignedDoctor->name ?? ''); ?>

                            <?php else: ?>
                                <?php echo e(__('Dr.')); ?> <?php echo e(auth()->user()->name); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </h1>
                    </div>

                    <!-- Right: Premium Status -->
                    <div class="flex items-center justify-end gap-6 w-1/4">
                        <div class="flex items-center gap-3 px-4 py-2.5 bg-white shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] rounded-2xl border border-emerald-100/30 group/status">
                            <div class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]"></span>
                            </div>
                            <div class="flex flex-col text-right">
                                <span class="text-emerald-600 text-[10px] font-black uppercase tracking-wider leading-none mb-1 opacity-80"><?php echo e(__('System Status')); ?></span>
                                <span class="text-slate-900 text-xs font-black uppercase tracking-widest leading-none"><?php echo e(__('Online')); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="p-6 md:p-10 z-0">
                <div class="max-w-7xl mx-auto drop-shadow-sm">
                    <?php echo e($slot); ?>

                </div>
            </div>
        </main>
    </div>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>

    <script>
    function queueSortable() {
        return {
            draggedNode: null,
            dragStart(event, id) {
                this.draggedNode = event.target.closest('tr');
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', id);
                setTimeout(() => {
                    this.draggedNode.classList.add('opacity-50', 'bg-purple-100');
                }, 0);
            },
            dragEnd(event) {
                if (this.draggedNode) {
                    this.draggedNode.classList.remove('opacity-50', 'bg-purple-100');
                    this.draggedNode = null;
                }
                this.clearBorders();
            },
            dragOver(event) {
                const target = event.target.closest('tr');
                if (target && target !== this.draggedNode && target.dataset.id) {
                    const rect = target.getBoundingClientRect();
                    const offset = rect.y + (rect.height / 2);
                    this.clearBorders();
                    if (event.clientY - offset > 0) {
                        target.style.borderBottom = '2px solid #9333ea';
                    } else {
                        target.style.borderTop = '2px solid #9333ea';
                    }
                }
            },
            drop(event, $wire) {
                this.clearBorders();
                const draggedId = event.dataTransfer.getData('text/plain');
                const target = event.target.closest('tr');
                
                if (target && target !== this.draggedNode && target.dataset.id) {
                    const targetId = target.dataset.id;
                    const rect = target.getBoundingClientRect();
                    const offset = rect.y + (rect.height / 2);
                    
                    let ids = Array.from(document.querySelectorAll('tr[data-id]')).map(el => el.dataset.id);
                    ids = ids.filter(id => id !== draggedId);
                    
                    const targetIndex = ids.indexOf(targetId);
                    
                    if (event.clientY - offset > 0) {
                        ids.splice(targetIndex + 1, 0, draggedId);
                    } else {
                        ids.splice(targetIndex, 0, draggedId);
                    }
                    
                    $wire.updateQueueOrder(ids);
                }
            },
            clearBorders() {
                document.querySelectorAll('tr[data-id]').forEach(tr => {
                    tr.style.borderTop = '';
                    tr.style.borderBottom = '';
                });
            }
        }
    }
    </script>
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('shared.doctor-reminders');

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1829721221-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
</body>
</html>
<?php /**PATH C:\Bola\Clinova\resources\views/layouts/clinic.blade.php ENDPATH**/ ?>