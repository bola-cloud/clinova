<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($title) ? __($title) : 'Clinova' }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { font-family: 'Outfit', 'Cairo', sans-serif; }
        .glass-panel { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.05); }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 overflow-x-hidden selection:bg-purple-500 selection:text-white">
    <div class="flex h-screen overflow-hidden">
        
        <!-- Premium Sidebar -->
        <aside class="w-72 bg-gradient-to-b from-[#8A2BE2] via-[#4A26AB] to-[#0C3E8A] text-white shrink-0 hidden md:flex flex-col shadow-2xl relative z-20">
            <!-- Logo Section -->
            <div class="p-8 pb-6 flex items-center gap-4">
                <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-lg text-[#4A26AB] transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <span class="text-3xl font-extrabold tracking-tighter">C</span>
                </div>
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-white leading-none">Clinova</h1>
                    <p class="text-[10px] text-purple-200 mt-1 uppercase tracking-widest opacity-80 font-medium">{{ __('Run Your Clinic Smarter') }}</p>
                </div>
            </div>
            
            <!-- Navigation Roles -->
            @php
                $role = auth()->user()->role;
                $links = [];
                if ($role === 'admin') {
                    $links = [
                        ['name' => __('Dashboard'), 'route' => 'admin.dashboard', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                        ['name' => __('Patients Archive'), 'route' => 'patients.index', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ['name' => __('Appointments Management'), 'route' => 'appointments.index', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['name' => __('Income Statistics'), 'route' => 'admin.statistics', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                        ['name' => __('Settings'), 'route' => 'admin.dashboard', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 002.572-1.065z']
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
                        ['name' => __('Patients Archive'), 'route' => 'patients.index', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z']
                    ];
                }
            @endphp

            <nav class="flex-1 px-4 space-y-2 mt-6 overflow-y-auto custom-scrollbar">
                <div class="px-4 text-xs font-bold text-purple-300 uppercase tracking-wider mb-4 opacity-70">{{ __('Main Menu') }}</div>
                @foreach($links as $link)
                    <a href="{{ route($link['route']) }}" 
                       class="flex items-center gap-4 px-4 py-3.5 rounded-2xl transition-all duration-300 font-medium group
                              {{ request()->routeIs($link['route']) 
                                  ? 'bg-white text-[#4A26AB] shadow-[0_8px_30px_rgb(255,255,255,0.12)]' 
                                  : 'text-purple-100 hover:bg-white/10 hover:translate-x-1' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs($link['route']) ? 'text-[#4A26AB]' : 'text-purple-300 group-hover:text-white' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}"></path>
                        </svg>
                        <span>{{ $link['name'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="p-6 border-t border-white/10 space-y-4 glass-panel m-4 rounded-3xl mt-auto">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-purple-400 to-pink-300 flex items-center justify-center font-bold text-white shadow-inner border border-white/20">
                        {{ auth()->user()->name[0] ?? 'U' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold truncate text-white">{{ auth()->user()->name ?? 'User' }}</p>
                        <p class="text-xs text-purple-200 truncate font-medium">
                            {{ $role === 'admin' ? __('Administration') : ($role === 'doctor' ? __('Medical Staff') : __('Desk Staff')) }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 mt-4">
                    <a href="{{ route('lang.switch', 'ar') }}" class="flex items-center justify-center p-2 rounded-xl text-xs font-bold {{ app()->getLocale() === 'ar' ? 'bg-white text-[#4A26AB] shadow-md' : 'bg-white/5 text-purple-100 hover:bg-white/20' }} transition-all">
                        العربية
                    </a>
                    <a href="{{ route('lang.switch', 'en') }}" class="flex items-center justify-center p-2 rounded-xl text-xs font-bold {{ app()->getLocale() === 'en' ? 'bg-white text-[#4A26AB] shadow-md' : 'bg-white/5 text-purple-100 hover:bg-white/20' }} transition-all">
                        English
                    </a>
                </div>
                
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 p-3 hover:bg-white/10 rounded-xl text-rose-200 hover:text-rose-100 transition-colors text-sm font-bold border border-rose-300/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span>{{ __('Logout') }}</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 bg-[#f4f7fc] overflow-y-auto relative">
            <!-- Decorative Background blob -->
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-purple-200/40 rounded-full blur-[100px] -z-10 pointer-events-none -translate-y-1/2 translate-x-1/3"></div>
            
            <!-- Header -->
            <header class="h-20 bg-white/80 backdrop-blur-xl border-b border-gray-100/50 flex items-center px-6 md:px-10 sticky top-0 z-50 shadow-sm transition-all duration-300">
                <!-- Left: Title & Mobile Toggle -->
                <div class="flex-1 flex items-center gap-4">
                    <button class="md:hidden p-2 text-gray-500 hover:text-[#4A26AB] transition-colors rounded-xl hover:bg-purple-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <h2 class="text-lg md:text-xl font-black text-gray-800 tracking-tight hidden sm:block">
                        <span class="bg-clip-text text-transparent bg-gradient-to-r from-gray-900 via-gray-700 to-gray-500">
                            {{ isset($title) ? __($title) : __('Dashboard') }}
                        </span>
                    </h2>
                </div>
                
                <!-- Center: Centered Logo -->
                <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 flex items-center justify-center pointer-events-none sm:pointer-events-auto">
                    <div class="flex items-center gap-2 group">
                        <img src="{{ asset('Clinova Logo.png') }}" alt="Clinova" class="h-10 md:h-12 w-auto object-contain drop-shadow-md group-hover:scale-105 transition-transform duration-500">
                    </div>
                </div>

                <!-- Right: Status / User Actions -->
                <div class="flex-1 flex items-center justify-end gap-4">
                    <div class="hidden lg:flex items-center gap-2 px-4 py-2 bg-white/50 backdrop-blur-md rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all group">
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)] animate-pulse"></div>
                        <span class="text-emerald-700 text-[10px] font-black uppercase tracking-[0.15em]">{{ __('Online') }}</span>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="p-6 md:p-10 z-0">
                <div class="max-w-7xl mx-auto drop-shadow-sm">
                    {{ $slot }}
                </div>
            </div>
        </main>
    </div>

    @livewireScripts

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
</body>
</html>
