<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 font-sans" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="max-w-5xl mx-auto w-full">
        
        <div class="text-center mb-10">
            @if($doctor->profile_image)
                <img class="mx-auto h-24 w-24 rounded-full border-4 border-white shadow-lg object-cover" src="{{ asset('storage/' . $doctor->profile_image) }}" alt="Dr. {{ $doctor->name }}">
            @else
                <div class="mx-auto h-24 w-24 rounded-full border-4 border-white shadow-lg bg-indigo-100 flex items-center justify-center text-indigo-500 font-bold text-3xl">
                    {{ substr($doctor->name, 0, 1) }}
                </div>
            @endif
            <h2 class="mt-4 text-3xl font-extrabold text-gray-900">
                {{ __('Dr.') }} {{ $doctor->name }}
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                {{ $doctor->specialty->name ?? __('Specialist') }}
            </p>
        </div>

        @if($bookingSuccess)
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center animate-pulse">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <svg class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ __('Booking Confirmed!') }}</h3>
                <p class="text-gray-600 mb-6">{{ __('Your appointment has been successfully scheduled. We look forward to seeing you.') }}</p>
                <div class="bg-gray-50 rounded-xl p-6 inline-block text-center mb-6 border border-gray-200">
                    <p class="text-sm text-gray-500 uppercase tracking-widest font-bold mb-2">{{ __('Your Queue Number') }}</p>
                    <div class="flex items-center justify-center gap-3">
                        <span class="text-5xl font-black text-indigo-600">#{{ $queueNumber }}</span>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-500">{{ __('Date') }}: <span class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}</span></p>
                    </div>
                </div>
                <div>
                    <button wire:click="$set('bookingSuccess', false)" class="text-indigo-600 hover:text-indigo-900 font-medium">
                        {{ __('Book another appointment') }}
                    </button>
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                @if($errorMessage)
                    <div class="bg-rose-50 border-l-4 border-rose-400 p-4 m-6 rounded-r-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-rose-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3 rtl:mr-3 rtl:ml-0">
                                <p class="text-sm text-rose-700 font-medium">{{ $errorMessage }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="p-6 md:p-10">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                        <!-- Left Column: Date & Time -->
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-4" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                                <span class="inline-block bg-indigo-100 text-indigo-700 rounded-full w-6 h-6 text-center leading-6 text-sm mr-2 rtl:ml-2 rtl:mr-0">1</span>
                                {{ __('Select Date & Time') }}
                            </h3>
                            
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Select Date') }}</label>
                                <input type="date" wire:model.live="selectedDate" min="{{ now()->format('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4">
                                <h4 class="font-bold text-indigo-800 text-sm mb-2 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ __('Queue System') }}
                                </h4>
                                <p class="text-xs text-indigo-700">
                                    {{ __('This clinic operates on a first-come, first-served queue system. You will receive your exact queue number upon completing your booking.') }}
                                </p>
                            </div>
                        </div>

                        <!-- Right Column: Patient Info -->
                        <div class="border-t lg:border-t-0 lg:border-l lg:rtl:border-r lg:rtl:border-l-0 border-gray-200 pt-8 lg:pt-0 lg:pl-10 lg:rtl:pr-10">
                            <h3 class="text-lg font-bold text-gray-900 mb-4" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                                <span class="inline-block bg-indigo-100 text-indigo-700 rounded-full w-6 h-6 text-center leading-6 text-sm mr-2 rtl:ml-2 rtl:mr-0">2</span>
                                {{ __('Your Details') }}
                            </h3>
                            
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('Full Name') }}</label>
                                        <input type="text" wire:model="patientName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="{{ __('e.g. Ahmed Ali') }}">
                                        @error('patientName') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('Age') }} ({{ __('Years') }})</label>
                                        <input type="number" wire:model="patientAgeYears" min="0" max="150" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g. 30">
                                        @error('patientAgeYears') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('Phone Number') }}</label>
                                        <input type="tel" wire:model="patientPhone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="{{ __('e.g. 01012345678') }}" dir="ltr">
                                        @error('patientPhone') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('Address') }}</label>
                                        <input type="text" wire:model="patientAddress" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="{{ __('e.g. Cairo, Nasr City') }}">
                                        @error('patientAddress') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Booking Type') }}</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <label class="flex items-center justify-center px-3 py-2 border rounded-md cursor-pointer transition-colors {{ $type === 'checkup' ? 'bg-indigo-50 border-indigo-500 text-indigo-700 font-bold' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                                            <input type="radio" wire:model.live="type" value="checkup" class="sr-only">
                                            <span class="text-sm">{{ __('New Consultation') }}</span>
                                        </label>
                                        <label class="flex items-center justify-center px-3 py-2 border rounded-md cursor-pointer transition-colors {{ $type === 'follow_up' ? 'bg-indigo-50 border-indigo-500 text-indigo-700 font-bold' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                                            <input type="radio" wire:model.live="type" value="follow_up" class="sr-only">
                                            <span class="text-sm">{{ __('Follow-up') }}</span>
                                        </label>
                                    </div>
                                    @error('type') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mt-8">
                                <button type="button" wire:click="confirmBooking" wire:loading.attr="disabled" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition-colors">
                                    <span wire:loading.remove wire:target="confirmBooking">{{ __('Confirm Appointment') }}</span>
                                    <span wire:loading wire:target="confirmBooking">
                                        <svg class="animate-spin -ml-1 mr-3 rtl:mr-0 rtl:ml-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ __('Processing...') }}
                                    </span>
                                </button>
                                <p class="mt-3 text-center text-xs text-gray-500">
                                    {{ __('By booking, you agree to our clinic terms and conditions.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
        <div class="mt-8 text-center text-sm text-gray-500">
            <p>Powered by <span class="font-bold text-indigo-600">Clinova ERP</span></p>
        </div>
    </div>
</div>
