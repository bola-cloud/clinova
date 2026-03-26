<div class="space-y-8 overflow-x-hidden" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" x-data="{ isEditingPH: false, isEditingFH: false }">
    <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex items-center gap-6">
                <div class="w-20 h-20 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center text-3xl font-bold">
                    {{ mb_substr($patient->name, 0, 1, 'UTF-8') }}
                </div>
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-1">{{ $patient->name }}</h2>
                    <p class="text-gray-500 font-medium">{{ $patient->phone }} | {{ $patient->age }} {{ __('Years') }} | {{ $patient->address }}</p>
                </div>
            </div>
            <div class="flex gap-3">
                <button wire:click="openEditModal" class="px-6 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 font-bold transition-colors">{{ __('Edit Data') }}</button>
                <button wire:click="openBooking" class="px-6 py-2 bg-purple-600 text-white rounded-xl font-bold hover:shadow-lg hover:shadow-purple-200 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    {{ __('Book Appointment') }}
                </button>
                <button wire:click="openVisitModal" class="px-6 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-bold hover:shadow-lg hover:shadow-emerald-200 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                    {{ __('Record New Visit') }}
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
            <h3 class="text-xl font-bold text-gray-900 border-s-4 border-purple-600 ps-4">{{ __('Clinical Visit History') }}</h3>
            
            @if (session()->has('visit_message'))
                <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 text-sm rounded-2xl border border-emerald-100 flex items-center gap-3 animate-slide-in">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="font-bold">{{ session('visit_message') }}</span>
                </div>
            @endif

            @forelse($primaryVisits as $visit)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 bg-gray-50 flex items-center justify-between border-b border-gray-100">
                    <div class="flex items-center gap-6">
                        <div>
                            <span class="text-sm text-gray-500">{{ __('Visit Date') }}</span>
                            <div class="flex items-center gap-2">
                                <p class="font-bold">{{ $visit->created_at->format('Y-m-d') }}</p>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $visit->type === 'checkup' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ __($visit->type === 'checkup' ? 'Checkup' : 'Follow-up') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @if($visit->type === 'checkup' && auth()->user()->isDoctor())
                    <button wire:click="startFollowUp({{ $visit->id }})" class="px-4 py-1.5 bg-white border border-emerald-200 text-emerald-600 rounded-lg text-xs font-bold hover:bg-emerald-50 transition-colors flex items-center gap-1.5 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        {{ __('Add Follow-up') }}
                    </button>
                    @endif
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h4 class="text-purple-900 font-bold text-sm mb-2">{{ __('Complaint') }}</h4>
                        <p class="text-gray-700 leading-relaxed">{{ $visit->complaint }}</p>
                    </div>
                    <div>
                        <h4 class="text-purple-900 font-bold text-sm mb-2">{{ __('Diagnosis') }}</h4>
                        <p class="text-gray-700 leading-relaxed">{{ $visit->diagnosis }}</p>
                    </div>
                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h4 class="text-purple-900 font-bold text-sm mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                {{ __('Investigation & Tests') }}
                            </h4>
                            <p class="text-gray-700 leading-relaxed whitespace-pre-line bg-slate-50 p-4 rounded-xl border border-slate-100">{{ $visit->history ?: __('No data.') }}</p>
                        </div>
                        <div>
                            <h4 class="text-purple-900 font-bold text-sm mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                {{ __('Treatment & Instructions') }}
                            </h4>
                            <p class="text-gray-700 leading-relaxed whitespace-pre-line bg-slate-50 p-4 rounded-xl border border-slate-100">{{ $visit->treatment_text ?: __('No data.') }}</p>
                        </div>
                    </div>
                    @if($visit->treatment_file_path)
                    <div class="md:col-span-2">
                        <a href="{{ asset('storage/' . $visit->treatment_file_path) }}" target="_blank" class="inline-flex items-center gap-2 text-purple-600 font-bold hover:underline">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            {{ __('View Attached Treatment File') }}
                        </a>
                    </div>
                    @endif
                </div>
            @if($visit->followUps->count() > 0)
            <div class="ms-8 mt-2 space-y-4 mb-8 border-s-4 border-emerald-100 ps-6">
                @foreach($visit->followUps as $followUp)
                <div class="bg-emerald-50/20 rounded-2xl border border-emerald-100/50 overflow-hidden relative shadow-sm">
                    <div class="p-4 bg-emerald-50/50 flex items-center justify-between border-b border-emerald-100/20">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-emerald-800">{{ __('Follow-up Visit') }}</p>
                                <p class="text-[10px] text-emerald-600 font-bold uppercase">{{ $followUp->created_at->format('Y-m-d') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="text-xs">
                            <h5 class="text-emerald-900 font-bold mb-1">{{ __('Complaint') }}</h5>
                            <p class="text-gray-600">{{ $followUp->complaint }}</p>
                        </div>
                        <div class="text-xs">
                            <h5 class="text-emerald-900 font-bold mb-1">{{ __('Diagnosis') }}</h5>
                            <p class="text-gray-600">{{ $followUp->diagnosis }}</p>
                        </div>
                        <div class="md:col-span-2 text-xs">
                            <h5 class="text-emerald-900 font-bold mb-1">{{ __('Treatment & Instructions') }}</h5>
                            <p class="text-gray-600 whitespace-pre-line">{{ $followUp->treatment_text ?: __('No data.') }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @empty
            <div class="bg-white p-12 text-center rounded-2xl border-2 border-dashed border-gray-100">
                <p class="text-gray-400">{{ __('No previous visits recorded for this patient.') }}</p>
            </div>
            @endforelse
        </div>

        <div class="space-y-8">
            <!-- Medical Files & Investigations -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <h3 class="font-bold text-lg mb-4">{{ __('Medical Files & Investigations') }}</h3>
                
                @if (session()->has('file_message'))
                    <div class="mb-4 p-3 bg-green-50 text-green-700 text-sm rounded-xl border border-green-100">
                        {{ session('file_message') }}
                    </div>
                @endif
                
                <div class="space-y-4 mb-6 max-h-[300px] overflow-y-auto custom-scrollbar pr-2">
                    @forelse($patient->files as $file)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="p-2 {{ $file->file_type === 'investigation' ? 'bg-blue-100 text-blue-600' : ($file->file_type === 'lab' ? 'bg-rose-100 text-rose-600' : 'bg-purple-100 text-purple-600') }} rounded-lg shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-sm font-bold text-gray-800 truncate" title="{{ $file->file_name }}">{{  Str::limit($file->file_name, 20) }}</span>
                                <span class="text-[10px] text-gray-500 uppercase font-bold">{{ __($file->file_type === 'investigation' ? 'Investigation' : ($file->file_type === 'lab' ? 'Lab Result' : 'Other')) }} • {{ $file->created_at->format('Y-m-d') }}</span>
                            </div>
                        </div>
                        <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                            {{ __('View') }}
                        </a>
                    </div>
                    @empty
                    <p class="text-sm text-gray-400 text-center py-4">{{ __('No files uploaded yet.') }}</p>
                    @endforelse
                </div>
                
                @if(auth()->user()->isDoctor() || auth()->user()->isSecretary())
                <!-- Upload Form -->
                <form wire:submit.prevent="uploadFile" class="pt-4 border-t border-dashed border-gray-200 space-y-3 relative">
                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        <select wire:model="fileType" class="w-full sm:w-1/3 px-3 py-2 text-sm bg-gray-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500">
                            <option value="lab">{{ __('Lab Result') }}</option>
                            <option value="investigation">{{ __('Investigation') }}</option>
                            <option value="other">{{ __('Other Document') }}</option>
                        </select>
                        <input type="file" wire:model="newFile" class="block w-full sm:w-2/3 text-sm text-gray-500 file:me-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 transition-all cursor-pointer">
                    </div>
                    
                    <div wire:loading wire:target="newFile" class="text-xs text-purple-600 font-bold loading-dots">{{ __('Uploading file...') }}</div>
                    @error('newFile') <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror
                    
                    <button type="submit" wire:loading.attr="disabled" wire:target="uploadFile, newFile" class="w-full py-2.5 bg-purple-600 text-white rounded-xl text-sm font-bold shadow-md shadow-purple-200 hover:bg-purple-700 transition-colors disabled:opacity-50">
                        {{ __('Upload File') }}
                    </button>
                </form>
                @endif
            </div>

            <!-- Family History -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm relative" x-data="{ isEditing: false }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg">{{ __('Family Medical History') }}</h3>
                    @if(auth()->user()->isDoctor())
                    <button @click="isEditing = true" x-show="!isEditing" class="text-purple-600 hover:bg-purple-50 p-2 rounded-lg transition-colors" title="{{ __('Edit') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </button>
                    @endif
                </div>

                <div x-show="isEditing" x-cloak>
                    <form wire:submit.prevent="saveFamilyHistory" class="space-y-3">
                        <textarea wire:model="familyHistoryEdit" rows="4" class="w-full px-4 py-3 bg-gray-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500" placeholder="{{ __('Record hereditary diseases...') }}"></textarea>
                        <div class="flex items-center gap-2 justify-end">
                            <button type="button" @click="isEditing = false" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">{{ __('Cancel') }}</button>
                            <button type="submit" class="px-4 py-2 text-sm font-bold bg-purple-600 text-white rounded-xl shadow-lg shadow-purple-200 hover:bg-purple-700 transition-colors">{{ __('Save') }}</button>
                        </div>
                    </form>
                </div>
                <div x-show="!isEditing">
                    <p class="text-gray-600 text-sm leading-relaxed whitespace-pre-line">
                        {{ $patient->family_history ?: __('No data recorded.') }}
                    </p>
                </div>
            </div>

            <!-- Personal History -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm relative" x-data="{ isEditingPH: false }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg">{{ __('Personal Medical History') }}</h3>
                    @if(auth()->user()->isDoctor())
                    <button @click="isEditingPH = true" x-show="!isEditingPH" class="text-purple-600 hover:bg-purple-50 p-2 rounded-lg transition-colors" title="{{ __('Edit') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </button>
                    @endif
                </div>

                <div x-show="isEditingPH" x-cloak>
                    <form wire:submit.prevent="savePersonalHistory" class="space-y-3">
                        <textarea wire:model="personalHistoryEdit" rows="4" class="w-full px-4 py-3 bg-gray-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500" placeholder="{{ __('Record chronic diseases, allergies, etc...') }}"></textarea>
                        <div class="flex items-center gap-2 justify-end">
                            <button type="button" @click="isEditingPH = false" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">{{ __('Cancel') }}</button>
                            <button type="submit" class="px-4 py-2 text-sm font-bold bg-purple-600 text-white rounded-xl shadow-lg shadow-purple-200 hover:bg-purple-700 transition-colors">{{ __('Save') }}</button>
                        </div>
                    </form>
                </div>
                <div x-show="!isEditingPH">
                    <p class="text-gray-600 text-sm leading-relaxed whitespace-pre-line">
                        {{ $patient->personal_history ?: __('No data recorded.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Record New Visit Modal -->
    @if($showVisitModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div wire:click="closeVisitModal" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="bg-white rounded-[2.5rem] w-full max-w-2xl shadow-2xl relative overflow-hidden animate-zoom-in">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full -translate-y-16 translate-x-16 blur-2xl"></div>
            
            <form wire:submit.prevent="saveVisit" class="relative">
                <div class="p-8 md:p-10">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-2xl font-black text-gray-900 flex items-center gap-3">
                            <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center shadow-inner">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                            </div>
                            {{ $visitType === 'checkup' ? __('Record Clinical Visit') : __('Record Follow-up Visit') }}
                        </h3>
                        <button type="button" wire:click="closeVisitModal" class="p-3 hover:bg-gray-100 rounded-full transition-colors text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('Patient Complaint') }} <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <textarea wire:model.live="complaint" rows="3" placeholder="{{ __('What is the patient suffering from?') }}" 
                                        class="w-full bg-slate-50 border-gray-200 rounded-2xl py-4 px-5 text-sm focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"></textarea>
                                @if(!empty($complaint) && count($complaintSuggestions) > 0)
                                <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 max-h-40 overflow-y-auto">
                                    @foreach($complaintSuggestions as $suggestion)
                                        <button type="button" wire:click="selectComplaint('{{ $suggestion }}')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            {{ $suggestion }}
                                        </button>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @error('complaint') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('Preliminary Diagnosis') }} <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="text" wire:model.live="diagnosis" placeholder="{{ __('Enter diagnosis...') }}"
                                    class="w-full bg-slate-50 border-gray-200 rounded-2xl py-4 px-5 text-sm focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all">
                                @if(!empty($diagnosis) && count($diagnosisSuggestions) > 0)
                                <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 max-h-40 overflow-y-auto">
                                    @foreach($diagnosisSuggestions as $suggestion)
                                        <button type="button" wire:click="selectDiagnosis('{{ $suggestion }}')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            {{ $suggestion }}
                                        </button>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @error('diagnosis') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('Investigations & Tests') }}</label>
                            <textarea wire:model="investigation" rows="4" placeholder="{{ __('Requested labs, etc.') }}"
                                      class="w-full bg-slate-50 border-gray-200 rounded-2xl py-4 px-5 text-sm focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"></textarea>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('Treatment Plan') }}</label>
                            <textarea wire:model="treatmentText" rows="4" placeholder="{{ __('Instructions...') }}"
                                      class="w-full bg-slate-50 border-gray-200 rounded-2xl py-4 px-5 text-sm focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"></textarea>
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('Attach Treatment File') }}</label>
                            <div class="flex items-center gap-4 p-4 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl group relative cursor-pointer">
                                <input type="file" wire:model="treatmentFile" class="absolute inset-0 opacity-0 cursor-pointer">
                                <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-emerald-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700">{{ __('Upload prescription/report') }}</p>
                                    <p class="text-[10px] text-slate-400 uppercase font-black">PDF, JPG, PNG</p>
                                </div>
                            </div>
                            @if($treatmentFile)
                                <p class="text-xs text-emerald-600 font-bold mt-2">{{ $treatmentFile->getClientOriginalName() }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-10 flex gap-4">
                        <button type="button" wire:click="closeVisitModal" class="flex-1 py-4 text-slate-500 font-bold">{{ __('Cancel') }}</button>
                        <button type="submit" class="flex-[2] py-4 bg-emerald-600 text-white rounded-2xl font-bold shadow-xl">{{ __('Save Visit Record') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Booking Modal -->
    @if($showBookingModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div wire:click="closeBookingModal" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="bg-white rounded-[2rem] w-full max-w-lg shadow-2xl relative overflow-hidden animate-zoom-in">
            <div class="p-8">
                <h3 class="text-xl font-bold mb-6">{{ __('Book Appointment for') }} {{ $patient->name }}</h3>
                <div class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase">{{ __('Date') }}</label>
                            <input type="date" wire:model="bookingDate" class="w-full bg-slate-50 border-gray-200 rounded-xl px-4 py-3">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase">{{ __('Time') }}</label>
                            <input wire:model="bookingTime" type="time" class="w-full bg-slate-50 border-gray-200 rounded-xl px-4 py-3">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase">{{ __('Type') }}</label>
                            <select wire:model="bookingType" class="w-full bg-slate-50 border-gray-200 rounded-xl px-4 py-3">
                                <option value="checkup">{{ __('Checkup') }}</option>
                                <option value="follow_up">{{ __('Follow-up') }}</option>
                            </select>
                        </div>
                    </div>
                    @if(auth()->user()->isAdmin())
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase">{{ __('Doctor') }}</label>
                        <select wire:model="bookingDoctorId" class="w-full bg-slate-50 border-gray-200 rounded-xl px-4 py-3">
                            <option value="">{{ __('Select Doctor') }}</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="pt-6 flex gap-3">
                        <button wire:click="closeBookingModal" class="flex-1 py-3 text-gray-500 font-bold">{{ __('Cancel') }}</button>
                        <button wire:click="confirmBooking" class="flex-[2] py-3 bg-indigo-600 text-white rounded-xl font-bold shadow-lg">{{ __('Confirm Booking') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Edit Patient Modal -->
    @if($showEditModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div wire:click="closeEditModal" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="bg-white rounded-[2.5rem] w-full max-w-lg shadow-2xl relative overflow-hidden animate-zoom-in p-8">
            <h3 class="text-xl font-bold mb-6">{{ __('Edit Patient Data') }}</h3>
            <form wire:submit.prevent="savePatientData" class="space-y-5">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase">{{ __('Full Name') }}</label>
                    <input type="text" wire:model="editName" class="w-full bg-slate-50 border-gray-200 rounded-xl px-4 py-3">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase">{{ __('Phone') }}</label>
                        <input type="text" wire:model="editPhone" class="w-full bg-slate-50 border-gray-200 rounded-xl px-4 py-3">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase">{{ __('Age') }}</label>
                        <input type="number" wire:model="editAge" class="w-full bg-slate-50 border-gray-200 rounded-xl px-4 py-3">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase">{{ __('Personal History') }}</label>
                    <textarea wire:model="personalHistoryEdit" rows="3" class="w-full bg-slate-50 border-gray-200 rounded-xl px-4 py-3"></textarea>
                </div>
                <div class="pt-6 flex gap-3">
                    <button type="button" wire:click="closeEditModal" class="flex-1 py-3 text-gray-500 font-bold">{{ __('Cancel') }}</button>
                    <button type="submit" class="flex-[2] py-3 bg-purple-600 text-white rounded-xl font-bold shadow-lg">{{ __('Update Records') }}</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
