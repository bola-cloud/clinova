<div class="max-w-4xl mx-auto space-y-8" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="bg-purple-600 text-white p-8 rounded-[2rem] shadow-xl shadow-purple-200 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold mb-1 italic">{{ __('Record Medical Visit') }}</h2>
            <p class="opacity-80">{{ __('Patient') }}: <span class="font-bold underline">{{ $appointment->patient->name }}</span></p>
        </div>
        <div class="text-left">
            <p class="text-sm opacity-60">{{ __('Booking Date') }}</p>
            <p class="font-bold">{{ $appointment->scheduled_at->format('Y-m-d H:i') }}</p>
        </div>
    </div>

    <form wire:submit="saveVisit" class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="md:col-span-2 bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block font-bold text-gray-700">{{ __('Main Complaint') }} (Complaint)</label>
                    <div class="relative">
                        <textarea wire:model.live="complaint" rows="3" class="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 transition-all"></textarea>
                        @if(!empty($complaint) && count($complaintSuggestions) > 0)
                        <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 max-h-40 overflow-y-auto">
                            @foreach($complaintSuggestions as $suggestion)
                                <button type="button" wire:click="selectSuggestionFor('complaint', '{{ addslashes($suggestion) }}')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    {{ $suggestion }}
                                </button>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @error('complaint') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-2">
                    <label class="block font-bold text-gray-700">{{ __('Diagnosis') }} (Diagnosis)</label>
                    <div class="relative">
                        <textarea wire:model.live="diagnosis" rows="3" class="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 transition-all"></textarea>
                        @if(!empty($diagnosis) && count($diagnosisSuggestions) > 0)
                        <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 max-h-40 overflow-y-auto">
                            @foreach($diagnosisSuggestions as $suggestion)
                                <button type="button" wire:click="selectSuggestionFor('diagnosis', '{{ addslashes($suggestion) }}')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    {{ $suggestion }}
                                </button>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @error('diagnosis') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="space-y-4">
                <label class="block text-sm font-bold text-gray-700">{{ __('Chronic Illnesses') }}</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 bg-gray-50 p-4 rounded-xl border border-gray-100">
                    @foreach(['Diabetes melitus', 'Hypertension', 'Ischemic heart disease', 'Asthma', 'COPD', 'Thyroid disorders', 'Chronic kidney disease', 'Chronic liver disease', 'Osteoporosis', 'Dyslipidemia', 'Anemia'] as $illness)
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" wire:model="chronicIllnesses" value="{{ $illness }}" class="w-4 h-4 text-purple-600 bg-white border-gray-300 rounded focus:ring-purple-500">
                        <span class="text-sm text-gray-700 group-hover:text-purple-700 transition-colors">{{ __($illness) }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="space-y-2">
                <label class="block font-bold text-gray-700">{{ __('Investigations & Tests') }}</label>
                <div class="relative">
                    <textarea wire:model.live="history" rows="2" class="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 transition-all text-sm"></textarea>
                    @if(!empty($history) && count($investigationSuggestions) > 0)
                    <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 max-h-40 overflow-y-auto">
                        @foreach($investigationSuggestions as $suggestion)
                            <button type="button" wire:click="selectSuggestionFor('history', '{{ addslashes($suggestion) }}')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ $suggestion }}
                            </button>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <div class="space-y-2">
                <label class="block font-bold text-gray-700">{{ __('Treatment Plan & Prescription') }} (Treatment Plan)</label>
                <div class="relative">
                    <textarea wire:model.live="treatment_text" rows="5" placeholder="{{ __('Write medications and dosages here...') }}" class="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 transition-all font-mono leading-relaxed"></textarea>
                    @if(!empty($treatment_text) && count($treatmentSuggestions) > 0)
                    <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 max-h-40 overflow-y-auto">
                        @foreach($treatmentSuggestions as $suggestion)
                            <button type="button" wire:click="selectSuggestionFor('treatment_text', '{{ addslashes($suggestion) }}')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ $suggestion }}
                            </button>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            @if(count($specialtyFields) > 0)
            <div class="pt-6 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-6">
                <h4 class="md:col-span-2 text-sm font-black text-purple-600 uppercase tracking-widest">{{ auth()->user()->specialty->name }} - {{ __('Extra Details') }}</h4>
                @foreach($specialtyFields as $field)
                <div class="space-y-2">
                    <label class="block font-bold text-gray-700">{{ $field->label }} <span class="text-rose-500">*</span></label>
                    @if($field->type === 'text')
                        <input type="text" wire:model="dynamicAnswers.{{ $field->id }}" class="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 transition-all">
                    @elseif($field->type === 'select')
                        <select wire:model="dynamicAnswers.{{ $field->id }}" class="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 transition-all">
                            <option value="">{{ __('Select...') }}</option>
                            @foreach($field->options as $opt)
                                <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                    @endif
                    @error('dynamicAnswers.' . $field->id) <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                </div>
                @endforeach
            </div>
            @endif

            <div class="space-y-3">
                <label class="block font-bold text-gray-700">{{ __('Attach Prescription Image or File') }}</label>
                <div class="relative">
                    <input type="file" wire:model="treatment_file" class="hidden" id="treatment_file">
                    <label for="treatment_file" class="flex items-center justify-center gap-3 w-full py-10 border-2 border-dashed border-gray-200 rounded-2xl cursor-pointer hover:bg-gray-50 hover:border-purple-300 transition-all group">
                        <div class="text-center">
                            <svg class="w-10 h-10 text-gray-300 mx-auto mb-2 group-hover:text-purple-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="text-sm text-gray-500 group-hover:text-purple-600">{{ __('Click to upload file or drag here') }}</span>
                        </div>
                    </label>
                    <div wire:loading wire:target="treatment_file" class="mt-2 text-xs text-purple-600 font-bold">{{ __('Uploading') }}...</div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-50 flex gap-4">
                <button type="submit" class="flex-1 py-4 bg-purple-600 text-white rounded-2xl font-bold text-lg shadow-lg shadow-purple-200 hover:-translate-y-1 transition-all">{{ __('Save Visit & End Appointment') }}</button>
                <a href="{{ route('doctor.dashboard') }}" class="px-8 py-4 bg-gray-100 text-gray-600 rounded-2xl font-bold text-lg hover:bg-gray-200 transition-all">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>
</div>
