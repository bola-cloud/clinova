<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Smart Clinic Management') }} - {{ config('app.name', 'Clinova') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .text-gradient {
            background: linear-gradient(135deg, #4f46e5 0%, #9333ea 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .bg-gradient-brand {
            background: linear-gradient(135deg, #4f46e5 0%, #9333ea 100%);
        }
        .hero-mask {
            mask-image: linear-gradient(to bottom, black 80%, transparent 100%);
        }
    </style>
</head>
<body class="antialiased bg-slate-50 text-slate-900 overflow-x-hidden">
    <!-- Navbar -->
    <nav class="fixed top-0 w-full z-50 glass">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Clinova Logo" class="h-10 w-10 object-contain">
                    <span class="text-2xl font-bold tracking-tight text-gradient">Clinova</span>
                </div>
                
                <div class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600">
                    <a href="#features" class="hover:text-indigo-600 transition-colors uppercase tracking-wider">{{ __('Features') }}</a>
                    <a href="#about" class="hover:text-indigo-600 transition-colors uppercase tracking-wider">{{ __('About') }}</a>
                    <a href="#contact" class="hover:text-indigo-600 transition-colors uppercase tracking-wider">{{ __('Contact') }}</a>
                </div>

                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="bg-gradient-brand text-white px-6 py-2.5 rounded-full font-semibold shadow-lg shadow-indigo-200 hover:scale-105 transition-transform">
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-slate-600 font-semibold hover:text-indigo-600 transition-colors">{{ __('Log in') }}</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="bg-gradient-brand text-white px-6 py-2.5 rounded-full font-semibold shadow-lg shadow-indigo-200 hover:scale-105 transition-transform">
                                {{ __('Get Started') }}
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="absolute top-0 right-0 w-1/2 h-full opacity-10 blur-3xl bg-indigo-500 rounded-full translate-x-1/2 -translate-y-1/2"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="lg:flex items-center gap-16">
                <div class="lg:w-1/2 space-y-8 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-50 text-indigo-600 text-sm font-bold border border-indigo-100 uppercase tracking-widest">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                        </span>
                        {{ __('Introducing Clinova 2.0') }}
                    </div>
                    
                    <h1 class="text-5xl lg:text-7xl font-extrabold tracking-tight text-slate-900 leading-[1.1]">
                        {{ __('Manage Your Clinic') }} <br>
                        <span class="text-gradient">{{ __('With Intelligence') }}.</span>
                    </h1>
                    
                    <p class="text-xl text-slate-600 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                        {{ __('The all-in-one platform for modern medical practices. Streamline appointments, patient records, and financial analytics with elegance and ease.') }}
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="{{ route('register') }}" class="bg-gradient-brand text-white px-10 py-4 rounded-xl font-bold text-lg shadow-xl shadow-indigo-200 hover:shadow-2xl hover:-translate-y-1 transition-all">
                            {{ __('Start Free Trial') }}
                        </a>
                        <a href="#features" class="bg-white border-2 border-slate-100 text-slate-700 px-10 py-4 rounded-xl font-bold text-lg hover:bg-slate-50 transition-all">
                            {{ __('View Features') }}
                        </a>
                    </div>
                    
                    <div class="pt-8 flex items-center justify-center lg:justify-start gap-8 opacity-60">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-slate-900">5k+</div>
                            <div class="text-sm font-medium uppercase tracking-tighter">{{ __('Doctors') }}</div>
                        </div>
                        <div class="border-l border-slate-200 h-8"></div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-slate-900">100k+</div>
                            <div class="text-sm font-medium uppercase tracking-tighter">{{ __('Patients') }}</div>
                        </div>
                        <div class="border-l border-slate-200 h-8"></div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-slate-900">99.9%</div>
                            <div class="text-sm font-medium uppercase tracking-tighter">{{ __('Uptime') }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="lg:w-1/2 mt-16 lg:mt-0 relative group">
                    <div class="absolute -inset-4 bg-gradient-brand rounded-3xl opacity-20 blur-2xl group-hover:opacity-30 transition-opacity"></div>
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-white/50">
                        <img src="{{ asset('images/hero.png') }}" alt="Clinova Dashboard Preview" class="w-full h-auto transform group-hover:scale-105 transition-transform duration-700 ease-out">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-20 space-y-4">
                <h2 class="text-indigo-600 font-bold uppercase tracking-widest text-sm text-gradient">{{ __('Core Modules') }}</h2>
                <h3 class="text-4xl font-bold text-slate-900 leading-tight">
                    {{ __('Everything you need to run a') }} <br> 
                    <span class="text-gradient">{{ __('successful practice') }}.</span>
                </h3>
                <p class="text-slate-600 text-lg">{{ __('Designed by medical professionals for efficiency and clinical excellence.') }}</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="p-8 rounded-3xl bg-slate-50 hover:bg-white hover:shadow-xl hover:-translate-y-2 transition-all duration-300 border border-slate-100">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-100 flex items-center justify-center text-indigo-600 mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <h4 class="text-xl font-extrabold mb-3">{{ __('Smart Appointments') }}</h4>
                    <p class="text-slate-600 line-clamp-3 leading-relaxed">{{ __('Automated booking, queue management, and real-time availability tracking to eliminate waiting rooms.') }}</p>
                </div>
                
                <!-- Feature 2 -->
                <div class="p-8 rounded-3xl bg-slate-50 hover:bg-white hover:shadow-xl hover:-translate-y-2 transition-all duration-300 border border-slate-100">
                    <div class="w-14 h-14 rounded-2xl bg-purple-100 flex items-center justify-center text-purple-600 mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h4 class="text-xl font-extrabold mb-3">{{ __('Electronic Records') }}</h4>
                    <p class="text-slate-600 line-clamp-3 leading-relaxed">{{ __('Secure, digital patient histories with file uploads for labs and X-rays. Accessible anywhere, anytime.') }}</p>
                </div>
                
                <!-- Feature 3 -->
                <div class="p-8 rounded-3xl bg-slate-50 hover:bg-white hover:shadow-xl hover:-translate-y-2 transition-all duration-300 border border-slate-100">
                    <div class="w-14 h-14 rounded-2xl bg-pink-100 flex items-center justify-center text-pink-600 mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <h4 class="text-xl font-extrabold mb-3">{{ __('Finance Insights') }}</h4>
                    <p class="text-slate-600 line-clamp-3 leading-relaxed">{{ __('Comprehensive revenue dashboards, billing management, and financial growth analytics built-in.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 py-20 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-12 mb-16 border-b border-white/10 pb-16">
                <div class="col-span-1 space-y-6">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" alt="Clinova Logo" class="h-8 w-8 object-contain">
                        <span class="text-2xl font-bold tracking-tight text-white">Clinova</span>
                    </div>
                    <p class="text-slate-400 leading-relaxed">{{ __('Transforming medical practice management with modern technology.') }}</p>
                </div>
                <div>
                    <h5 class="font-bold mb-6 italic text-gradient uppercase tracking-widest text-sm">{{ __('Product') }}</h5>
                    <ul class="space-y-4 text-slate-400">
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Features') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Pricing') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Roadmap') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="font-bold mb-6 italic text-gradient uppercase tracking-widest text-sm">{{ __('Company') }}</h5>
                    <ul class="space-y-4 text-slate-400">
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('About') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Blog') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Careers') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="font-bold mb-6 italic text-gradient uppercase tracking-widest text-sm">{{ __('Legal') }}</h5>
                    <ul class="space-y-4 text-slate-400">
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Privacy') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Terms') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('GDPR') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="flex flex-col md:flex-row justify-between items-center gap-6 text-slate-500 text-sm">
                <p>© {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</p>
                <div class="flex gap-6">
                    <a href="#" class="hover:text-white">Twitter</a>
                    <a href="#" class="hover:text-white">LinkedIn</a>
                    <a href="#" class="hover:text-white">Dribbble</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
