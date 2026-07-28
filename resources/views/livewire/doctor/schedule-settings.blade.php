<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('Schedule Settings') }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('Manage your public booking page and weekly working hours.') }}</p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 bg-emerald-50 border-l-4 border-emerald-400 p-4 rounded-r-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3 rtl:mr-3 rtl:ml-0">
                    <p class="text-sm text-emerald-700 font-medium">{{ session('message') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">{{ __('Public Booking Portal') }}</h3>
            
            <label class="flex items-center cursor-pointer">
                <div class="relative">
                    <input type="checkbox" wire:model.live="is_booking_active" class="sr-only">
                    <div class="block {{ $is_booking_active ? 'bg-emerald-500' : 'bg-gray-300' }} w-14 h-8 rounded-full transition-colors duration-300"></div>
                    <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition transform {{ $is_booking_active ? 'translate-x-6' : '' }} {{ app()->getLocale() === 'ar' && $is_booking_active ? '-translate-x-6' : '' }}"></div>
                </div>
                <div class="ml-3 rtl:mr-3 rtl:ml-0 text-sm font-medium text-gray-700">
                    {{ $is_booking_active ? __('Active') : __('Paused') }}
                </div>
            </label>
        </div>
        <div class="p-6">
            <div class="max-w-xl">
                <label class="block text-sm font-bold text-gray-700 mb-1">{{ __('Booking Link Slug') }}</label>
                <div class="flex rounded-md shadow-sm">
                    <span class="inline-flex items-center px-3 rounded-l-md rtl:rounded-l-none rtl:rounded-r-md border border-r-0 rtl:border-r rtl:border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                        {{ url('/book') }}/
                    </span>
                    <input type="text" wire:model="booking_slug" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md rtl:rounded-none rtl:rounded-l-md border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="dr-ahmed-ali">
                </div>
                @error('booking_slug') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                <p class="text-xs text-gray-500 mt-2">{{ __('Share this link with your patients to allow them to book appointments online.') }}</p>
                @if($booking_slug)
                    <a href="{{ url('/book/' . $booking_slug) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium mt-2 inline-block">
                        {{ __('Preview Booking Page') }} &rarr;
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">{{ __('Weekly Schedule') }}</h3>
            <p class="text-sm text-gray-500">{{ __('Configure your working hours and slot duration for each day.') }}</p>
        </div>
        <div class="p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left rtl:text-right text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Day') }}</th>
                            <th scope="col" class="px-6 py-3 text-left rtl:text-right text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Working') }}</th>
                            <th scope="col" class="px-6 py-3 text-left rtl:text-right text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Start Time') }}</th>
                            <th scope="col" class="px-6 py-3 text-left rtl:text-right text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('End Time') }}</th>
                            <th scope="col" class="px-6 py-3 text-left rtl:text-right text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Slot Duration (min)') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($days as $index => $name)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ __($name) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" wire:model="schedules.{{ $index }}.is_working_day" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="time" wire:model="schedules.{{ $index }}.start_time" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" @if(!($schedules[$index]['is_working_day'] ?? false)) disabled @endif>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="time" wire:model="schedules.{{ $index }}.end_time" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" @if(!($schedules[$index]['is_working_day'] ?? false)) disabled @endif>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select wire:model="schedules.{{ $index }}.slot_duration_minutes" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" @if(!($schedules[$index]['is_working_day'] ?? false)) disabled @endif>
                                        <option value="5">{{ __('5 mins') }}</option>
                                        <option value="10">{{ __('10 mins') }}</option>
                                        <option value="15">{{ __('15 mins') }}</option>
                                        <option value="20">{{ __('20 mins') }}</option>
                                        <option value="30">{{ __('30 mins') }}</option>
                                        <option value="45">{{ __('45 mins') }}</option>
                                        <option value="60">{{ __('60 mins') }}</option>
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 text-right rtl:text-left">
            <button type="button" wire:click="saveSettings" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-bold rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                {{ __('Save Settings') }}
            </button>
        </div>
    </div>
</div>
