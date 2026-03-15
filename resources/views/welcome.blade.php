<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clinova - {{ __('Smart Clinic Management') }}</title>
    <meta name="description" content="{{ __('The all-in-one platform for modern medical practices. Streamline appointments, patient records, and financial analytics with elegance and ease.') }}">
    <link rel="icon" type="image/png" href="{{ asset('Clinova Logo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { box-sizing: border-box; }
        :root {
            --brand-purple: #7C3AED;
            --brand-indigo: #4338CA;
            --brand-light: #EDE9FE;
            --brand-dark: #1E1B4B;
        }
        body {
            font-family: {{ app()->getLocale() === 'ar' ? "'Cairo'" : "'Outfit'" }}, sans-serif;
            background: #f9fafb;
            color: #111827;
            overflow-x: hidden;
        }
        .gradient-text {
            background: linear-gradient(135deg, #7C3AED 0%, #4338CA 50%, #6D28D9 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #7C3AED 0%, #4338CA 100%);
        }
        .gradient-bg-hover:hover {
            background: linear-gradient(135deg, #6D28D9 0%, #3730A3 100%);
        }
        .glass-nav {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(124, 58, 237, 0.08);
        }
        .hero-gradient {
            background: linear-gradient(135deg, #faf5ff 0%, #ede9fe 30%, #e0e7ff 60%, #f0f9ff 100%);
        }
        .card-hover {
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(124, 58, 237, 0.18);
        }
        .stat-num {
            font-size: 2.25rem;
            font-weight: 900;
            background: linear-gradient(135deg, #7C3AED, #4338CA);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .feature-icon {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        .step-number {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            font-weight: 900;
            flex-shrink: 0;
        }
        .testimonial-card {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
            border: 1px solid #f3f4f6;
            transition: box-shadow 0.3s;
        }
        .testimonial-card:hover {
            box-shadow: 0 12px 40px rgba(124,58,237,0.12);
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }
        .animate-float {
            animation: float 4s ease-in-out infinite;
        }
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fade-in-up 0.7s ease-out forwards;
        }
        .animate-delay-100 { animation-delay: 0.1s; opacity: 0; }
        .animate-delay-200 { animation-delay: 0.2s; opacity: 0; }
        .animate-delay-300 { animation-delay: 0.3s; opacity: 0; }
        .section-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #ede9fe, #ddd6fe);
            color: #7C3AED;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 6px 16px;
            border-radius: 999px;
            border: 1px solid #c4b5fd;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 1.25rem;
        }

        /* RTL/LTR fixes */
        [dir="rtl"] .rtl\:rotate-180 { transform: rotate(180deg); }

        html { scroll-behavior: smooth; }
    </style>
</head>
<body>

    <!-- ===================== NAVBAR ===================== -->
    <nav id="navbar" class="fixed top-0 w-full z-50 glass-nav transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-[72px] items-center gap-6">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-3 shrink-0">
                    <img src="{{ asset('Clinova Logo.png') }}" alt="Clinova" class="h-10 w-auto object-contain">
                </a>

                <!-- Nav Links -->
                <div class="hidden md:flex items-center gap-8 text-sm font-semibold text-gray-600">
                    <a href="#features" class="hover:text-purple-700 transition-colors">{{ __('Features') }}</a>
                    <a href="#how-it-works" class="hover:text-purple-700 transition-colors">{{ __('How It Works') }}</a>
                    <a href="#testimonials" class="hover:text-purple-700 transition-colors">{{ __('Testimonials') }}</a>
                    <a href="#contact" class="hover:text-purple-700 transition-colors">{{ __('Contact') }}</a>
                </div>

                <!-- CTA Buttons -->
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="gradient-bg text-white px-6 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-purple-200 hover:shadow-purple-300 hover:-translate-y-0.5 transition-all">
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 font-semibold text-sm hover:text-purple-700 transition-colors px-3 py-2">
                            {{ __('Log in') }}
                        </a>
                        @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="gradient-bg text-white px-6 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-purple-200 hover:shadow-purple-300 hover:-translate-y-0.5 transition-all">
                            {{ __('Start Free Trial') }}
                        </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- ===================== HERO ===================== -->
    <section class="hero-gradient relative pt-28 pb-20 lg:pt-36 lg:pb-28 overflow-hidden">
        <!-- Background decorations -->
        <div class="absolute top-20 start-10 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float"></div>
        <div class="absolute top-40 end-10 w-96 h-96 bg-indigo-300 rounded-full mix-blend-multiply filter blur-3xl opacity-15 animate-float" style="animation-delay:2s"></div>
        <div class="absolute bottom-10 start-1/3 w-64 h-64 bg-violet-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float" style="animation-delay:1s"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="lg:grid lg:grid-cols-2 lg:gap-16 items-center">
                <!-- Hero Text -->
                <div class="text-center lg:text-start space-y-7 animate-fade-in-up">
                    <div class="section-badge">
                        <span class="w-2 h-2 rounded-full bg-purple-600 animate-pulse"></span>
                        {{ __('Clinova — Smart Clinic Platform') }}
                    </div>

                    <h1 class="text-4xl lg:text-6xl font-black leading-[1.1] text-gray-900 tracking-tight">
                        {{ __('Manage Your Clinic') }}<br>
                        <span class="gradient-text">{{ __('Smarter. Faster. Better.') }}</span>
                    </h1>

                    <p class="text-lg lg:text-xl text-gray-500 leading-relaxed max-w-xl mx-auto lg:mx-0">
                        {{ __('The all-in-one platform for modern medical practices. Streamline appointments, patient records, and financial analytics with elegance and ease.') }}
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start pt-2">
                        <a href="{{ route('register') }}" class="gradient-bg gradient-bg-hover text-white px-10 py-4 rounded-2xl font-bold text-lg shadow-xl shadow-purple-200 hover:-translate-y-1 transition-all text-center">
                            {{ __('Start Free Trial') }}
                        </a>
                        <a href="#how-it-works" class="bg-white border-2 border-gray-100 text-gray-700 px-10 py-4 rounded-2xl font-bold text-lg hover:border-purple-200 hover:bg-purple-50 transition-all text-center flex items-center justify-center gap-2">
                            {{ __('See How It Works') }}
                            <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    <!-- Stats -->
                    <div class="flex items-center justify-center lg:justify-start gap-8 pt-6 border-t border-purple-100/60">
                        <div class="text-center">
                            <div class="stat-num">500+</div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">{{ __('Doctors') }}</div>
                        </div>
                        <div class="w-px h-10 bg-gray-200"></div>
                        <div class="text-center">
                            <div class="stat-num">50k+</div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">{{ __('Patients') }}</div>
                        </div>
                        <div class="w-px h-10 bg-gray-200"></div>
                        <div class="text-center">
                            <div class="stat-num">99.9%</div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">{{ __('Uptime') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Hero Image -->
                <div class="mt-14 lg:mt-0 relative animate-fade-in-up animate-delay-200">
                    <div class="absolute -inset-6 bg-gradient-to-tr from-purple-200 to-indigo-200 rounded-[40px] opacity-40 blur-2xl"></div>
                    <div class="relative rounded-[32px] overflow-hidden shadow-2xl border-4 border-white/80">
                        <img src="{{ asset('images/hero.png') }}" alt="{{ __('Clinova Dashboard') }}" class="w-full h-auto object-cover">
                    </div>
                    <!-- Floating badge -->
                    <div class="absolute -bottom-4 -start-4 bg-white rounded-2xl px-5 py-3 shadow-xl border border-gray-100 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl gradient-bg flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium">{{ __('Appointments Today') }}</p>
                            <p class="text-lg font-black text-gray-900">24 {{ __('Booked') }}</p>
                        </div>
                    </div>
                    <div class="absolute -top-4 -end-4 bg-white rounded-2xl px-5 py-3 shadow-xl border border-gray-100 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium">{{ __('Monthly Revenue') }}</p>
                            <p class="text-lg font-black text-gray-900">+18%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== TRUSTED BY LOGOS / MARQUEE ===================== -->
    <section class="bg-white py-10 border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-8">{{ __('Trusted by leading clinics and medical centers') }}</p>
            <div class="flex items-center justify-center gap-10 flex-wrap">
                @foreach(['🏥', '🦷', '👁️', '🫀', '🧠', '🦴'] as $icon)
                <div class="flex items-center gap-2 text-gray-300 hover:text-purple-400 transition-colors cursor-default">
                    <span class="text-3xl">{{ $icon }}</span>
                    <span class="font-bold text-sm text-gray-300">{{ __('Medical Center') }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ===================== FEATURES ===================== -->
    <section id="features" class="py-28 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20 space-y-4">
                <div class="section-badge">{{ __('Core Modules') }}</div>
                <h2 class="text-4xl lg:text-5xl font-black text-gray-900 leading-tight">
                    {{ __('Everything you need to run') }}<br>
                    <span class="gradient-text">{{ __('a successful practice') }}</span>
                </h2>
                <p class="text-gray-500 text-lg max-w-2xl mx-auto leading-relaxed">
                    {{ __('Designed by medical professionals for efficiency and clinical excellence.') }}
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white rounded-3xl p-8 card-hover border border-gray-100">
                    <div class="feature-icon bg-indigo-50 text-indigo-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-xl font-extrabold mb-3 text-gray-900">{{ __('Smart Appointments') }}</h3>
                    <p class="text-gray-500 leading-relaxed">{{ __('Automated booking, queue management, and real-time availability tracking to eliminate waiting rooms.') }}</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white rounded-3xl p-8 card-hover border border-gray-100">
                    <div class="feature-icon bg-purple-50 text-purple-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-xl font-extrabold mb-3 text-gray-900">{{ __('Electronic Records') }}</h3>
                    <p class="text-gray-500 leading-relaxed">{{ __('Secure, digital patient histories with file uploads for labs and X-rays. Accessible anywhere, anytime.') }}</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white rounded-3xl p-8 card-hover border border-gray-100">
                    <div class="feature-icon bg-emerald-50 text-emerald-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="text-xl font-extrabold mb-3 text-gray-900">{{ __('Finance Insights') }}</h3>
                    <p class="text-gray-500 leading-relaxed">{{ __('Comprehensive revenue dashboards, billing management, and financial growth analytics built-in.') }}</p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white rounded-3xl p-8 card-hover border border-gray-100">
                    <div class="feature-icon bg-pink-50 text-pink-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-extrabold mb-3 text-gray-900">{{ __('Multi-Role Access') }}</h3>
                    <p class="text-gray-500 leading-relaxed">{{ __('Doctor, Secretary, and Admin roles — each with a tailored dashboard, permissions, and workflow.') }}</p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-white rounded-3xl p-8 card-hover border border-gray-100">
                    <div class="feature-icon bg-amber-50 text-amber-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </div>
                    <h3 class="text-xl font-extrabold mb-3 text-gray-900">{{ __('Patient Profiles') }}</h3>
                    <p class="text-gray-500 leading-relaxed">{{ __('Complete patient history, visit records, medical files, tags, and rapid booking — all in one place.') }}</p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-gradient-to-br from-purple-600 to-indigo-700 rounded-3xl p-8 card-hover text-white">
                    <div class="feature-icon bg-white/20 text-white">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h3 class="text-xl font-extrabold mb-3">{{ __('Secure & Reliable') }}</h3>
                    <p class="text-purple-100 leading-relaxed">{{ __('Enterprise-grade security with encrypted data storage, role-based access, and 99.9% uptime guarantee.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== FEATURE SHOWCASE ===================== -->
    <section class="py-28 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-2 gap-16 items-center">
                <!-- Image side -->
                <div class="relative mb-14 lg:mb-0">
                    <div class="absolute -inset-4 bg-gradient-to-tr from-purple-100 to-indigo-100 rounded-[40px] opacity-60 blur-xl"></div>
                    <div class="relative rounded-[32px] overflow-hidden shadow-2xl border-4 border-white">
                        <img src="{{ asset('images/features.png') }}" alt="{{ __('Feature Overview') }}" class="w-full h-auto">
                    </div>
                </div>
                <!-- Text side -->
                <div class="space-y-8">
                    <div class="section-badge">{{ __('Why Clinova?') }}</div>
                    <h2 class="text-4xl lg:text-5xl font-black text-gray-900 leading-tight">
                        {{ __('Your Vision,') }}<br>
                        <span class="gradient-text">{{ __('Our Platform.') }}</span>
                    </h2>
                    <p class="text-gray-500 text-lg leading-relaxed">
                        {{ __('Clinova was built from the ground up by a team of developers working closely with real doctors. Every feature is designed to minimize admin work and maximize time with patients.') }}
                    </p>

                    <div class="space-y-5">
                        @foreach([
                            [__('Instant Setup'), __('Get your clinic online in under 10 minutes. No IT team needed.')],
                            [__('Arabic & English'), __('Full bilingual support with proper RTL layout for Arabic users.')],
                            [__('Multi-Doctor Support'), __('Manage multiple doctors, schedules, and independent income reports.')]
                        ] as $item)
                        <div class="flex items-start gap-4 p-4 rounded-2xl bg-gray-50 hover:bg-purple-50 transition-colors border border-transparent hover:border-purple-100">
                            <div class="w-9 h-9 rounded-xl gradient-bg flex items-center justify-center shrink-0 shadow-md shadow-purple-200">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-gray-900">{{ $item[0] }}</h4>
                                <p class="text-gray-500 text-sm mt-0.5">{{ $item[1] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== HOW IT WORKS ===================== -->
    <section id="how-it-works" class="py-28 bg-gray-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20 space-y-4">
                <div class="section-badge">{{ __('Simple Process') }}</div>
                <h2 class="text-4xl lg:text-5xl font-black text-gray-900 leading-tight">
                    {{ __('How to get started') }}<br>
                    <span class="gradient-text">{{ __('with Clinova?') }}</span>
                </h2>
            </div>

            <div class="space-y-8">
                @php
                $steps = [
                    ['01', __('Create Your Account'), __('Register in seconds. No credit card required. Set up your clinic profile with your name, specialty, and fees.'), 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'],
                    ['02', __('Customize Your Settings'), __('Configure your consultation fees, add doctors and secretary accounts, and personalize the system to your workflow.'), 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                    ['03', __('Add Patients & Book Appointments'), __('Start adding patient records and booking appointments. The system handles the waitlist, visit records, and billing automatically.'), 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                    ['04', __('Track & Grow'), __('Monitor your clinic performance, revenue trends, and patient statistics from a beautiful analytics dashboard.'), 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ];
                @endphp
                @foreach($steps as $index => $step)
                <div class="flex items-start gap-6 bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-gray-100 hover:border-purple-200 hover:shadow-md transition-all group">
                    <div class="step-number gradient-bg text-white shadow-lg shadow-purple-200 group-hover:scale-110 transition-transform">
                        {{ $step[0] }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <svg class="w-5 h-5 text-purple-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step[3] }}"/></svg>
                            <h3 class="text-lg font-extrabold text-gray-900">{{ $step[1] }}</h3>
                        </div>
                        <p class="text-gray-500 leading-relaxed">{{ $step[2] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ===================== TESTIMONIALS ===================== -->
    <section id="testimonials" class="py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20 space-y-4">
                <div class="section-badge">{{ __('Testimonials') }}</div>
                <h2 class="text-4xl lg:text-5xl font-black text-gray-900 leading-tight">
                    {{ __('What doctors say') }}<br>
                    <span class="gradient-text">{{ __('about Clinova') }}</span>
                </h2>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                @foreach([
                    ['Dr. Ahmed Hassan', __('General Practitioner'), '⭐⭐⭐⭐⭐', __('Clinova completely transformed how I run my clinic. The waitlist management is flawless, and patients love the experience.')],
                    ['Dr. Sara Mohammed', __('Pediatrician'), '⭐⭐⭐⭐⭐', __('The Arabic interface is perfect. My team adapted to it instantly. The financial reports save me hours every month.')],
                    ['Dr. Khaled Ibrahim', __('Orthopedic Surgeon'), '⭐⭐⭐⭐⭐', __('I manage 3 doctors in my clinic now. Clinova handles all the complexity—separate income, separate waitlists—effortlessly.')],
                ] as $t)
                <div class="testimonial-card">
                    <div class="text-2xl mb-4">{{ $t[2] }}</div>
                    <p class="text-gray-600 leading-relaxed mb-6 italic">"{{ $t[3] }}"</p>
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-full gradient-bg flex items-center justify-center text-white font-bold text-lg shadow">
                            {{ mb_substr($t[0], 4, 1) }}
                        </div>
                        <div>
                            <p class="font-extrabold text-gray-900">{{ $t[0] }}</p>
                            <p class="text-sm text-gray-400">{{ $t[1] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ===================== CTA BANNER ===================== -->
    <section id="contact" class="py-20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="gradient-bg rounded-[40px] p-12 md:p-20 text-center text-white relative overflow-hidden shadow-2xl shadow-purple-300">
                <div class="absolute top-0 start-0 w-72 h-72 bg-white/10 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 end-0 w-72 h-72 bg-white/10 rounded-full blur-3xl translate-x-1/2 translate-y-1/2"></div>
                <div class="relative z-10 space-y-6">
                    <div class="inline-block bg-white/20 text-white text-xs font-bold px-4 py-2 rounded-full border border-white/30 uppercase tracking-widest">
                        {{ __('Limited Time Offer') }}
                    </div>
                    <h2 class="text-4xl lg:text-5xl font-black leading-tight">
                        {{ __('Ready to transform') }}<br>{{ __('your clinic?') }}
                    </h2>
                    <p class="text-purple-100 text-lg max-w-2xl mx-auto">
                        {{ __('Join hundreds of doctors who already use Clinova to deliver better care, faster.') }}
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center pt-4">
                        <a href="{{ route('register') }}" class="bg-white text-purple-700 px-10 py-4 rounded-2xl font-bold text-lg hover:bg-purple-50 hover:-translate-y-1 transition-all shadow-xl">
                            {{ __('Start Free Trial') }}
                        </a>
                        <a href="{{ route('login') }}" class="border-2 border-white/40 text-white px-10 py-4 rounded-2xl font-bold text-lg hover:bg-white/10 transition-all">
                            {{ __('Sign In') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== FOOTER ===================== -->
    <footer class="bg-[#0f0a2e] py-20 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-12 mb-16 border-b border-white/10 pb-16">
                <div class="col-span-2 md:col-span-1 space-y-6">
                    <a href="/" class="flex items-center gap-3">
                        <img src="{{ asset('Clinova Logo.png') }}" alt="Clinova" class="h-10 w-auto object-contain">
                    </a>
                    <p class="text-gray-400 leading-relaxed text-sm">{{ __('Transforming medical practice management with modern technology.') }}</p>
                    <div class="flex gap-3">
                        @foreach(['#', '#', '#'] as $link)
                        <a href="{{ $link }}" class="w-9 h-9 rounded-xl bg-white/5 hover:bg-purple-600 flex items-center justify-center text-gray-400 hover:text-white transition-all border border-white/10">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878V14.89h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>
                        </a>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h5 class="font-bold mb-6 text-purple-400 uppercase tracking-widest text-xs">{{ __('Product') }}</h5>
                    <ul class="space-y-3 text-gray-400 text-sm">
                        <li><a href="#features" class="hover:text-white transition-colors">{{ __('Features') }}</a></li>
                        <li><a href="#how-it-works" class="hover:text-white transition-colors">{{ __('How It Works') }}</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">{{ __('Log in') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h5 class="font-bold mb-6 text-purple-400 uppercase tracking-widest text-xs">{{ __('Company') }}</h5>
                    <ul class="space-y-3 text-gray-400 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('About') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Blog') }}</a></li>
                        <li><a href="#contact" class="hover:text-white transition-colors">{{ __('Contact') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h5 class="font-bold mb-6 text-purple-400 uppercase tracking-widest text-xs">{{ __('Legal') }}</h5>
                    <ul class="space-y-3 text-gray-400 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Privacy') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Terms') }}</a></li>
                    </ul>
                </div>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-gray-500 text-sm">
                <p>© {{ date('Y') }} Clinova. {{ __('All rights reserved.') }}</p>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse inline-block"></span>
                    <span class="text-emerald-400 font-semibold text-xs">{{ __('All systems operational') }}</span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll effect
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('shadow-lg');
            } else {
                navbar.classList.remove('shadow-lg');
            }
        });

        // Intersection Observer for animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                }
            });
        }, { threshold: 0.15 });

        document.querySelectorAll('.animate-fade-in-up').forEach(el => {
            el.style.animationPlayState = 'paused';
            observer.observe(el);
        });
    </script>
</body>
</html>
