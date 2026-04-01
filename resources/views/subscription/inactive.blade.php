<x-clinic-layout title="{{ __('Subscription Inactive') }}">
    <div class="min-h-[70vh] flex items-center justify-center p-6">
        <div class="max-w-2xl w-full bg-white rounded-[3rem] shadow-2xl shadow-slate-200/50 border border-gray-100 overflow-hidden animate-fade-in">
            <div class="p-12 text-center">
                <!-- Icon -->
                <div class="w-24 h-24 bg-rose-50 text-rose-600 rounded-[2rem] flex items-center justify-center mx-auto mb-8 shadow-inner">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>

                <h1 class="text-4xl font-black text-slate-900 tracking-tight mb-4">
                    {{ __('Subscription Expired') }}
                </h1>
                
                <p class="text-gray-500 text-lg font-medium leading-relaxed mb-10 max-w-md mx-auto">
                    {{ __('Your clinic\'s subscription has ended. Access to the management system is currently restricted.') }}
                </p>

                <!-- Status Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-slate-50 border border-gray-100 rounded-2xl mb-12">
                    <div class="w-2 h-2 bg-rose-500 rounded-full animate-pulse"></div>
                    <span class="text-xs font-black text-slate-600 uppercase tracking-widest">{{ __('Action Required') }}</span>
                </div>

                <div class="space-y-4">
                    <p class="text-sm font-bold text-slate-900">
                        {{ __('Please contact the system administrator to renew your plan.') }}
                    </p>
                    
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-6">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="px-8 py-4 bg-slate-100 text-slate-600 rounded-2xl font-black text-sm hover:bg-slate-200 transition-all uppercase tracking-widest">
                                {{ __('Logout') }}
                            </button>
                        </form>
                        
                        <a href="mailto:admin@clinova.com" class="px-8 py-4 bg-slate-900 text-white rounded-2xl font-black text-sm hover:bg-black transition-all shadow-xl shadow-slate-200 uppercase tracking-widest">
                            {{ __('Contact Support') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Footer Clinic Branding -->
            <div class="bg-slate-50 p-6 text-center border-t border-gray-50">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">
                    {{ config('app.name') }} &copy; {{ date('Y') }}
                </p>
            </div>
        </div>
    </div>
</x-clinic-layout>
