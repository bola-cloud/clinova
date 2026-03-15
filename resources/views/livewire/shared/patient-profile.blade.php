<div class="space-y-8" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex items-center gap-6">
                <div class="w-20 h-20 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center text-3xl font-bold">
                    {{ $patient->name[0] }}
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
            <h3 class="text-xl font-bold text-gray-900 border-r-4 border-purple-600 pr-4">{{ __('Clinical Visit History') }}</h3>
            
            @forelse($patient->visits as $visit)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 bg-gray-50 flex items-center justify-between border-b border-gray-100">
                    <div>
                        <span class="text-sm text-gray-500">{{ __('Visit Date') }}</span>
                        <p class="font-bold">{{ $visit->created_at->format('Y-m-d') }}</p>
                    </div>
                    <div class="text-left">
                        <span class="text-sm text-gray-500">{{ __('Treating Doctor') }}</span>
                        <p class="font-bold">{{ __('Dr.') }} {{ $visit->doctor->name }}</p>
                    </div>
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
                        <a href="{{ Storage::disk('public')->url($visit->treatment_file_path) }}" target="_blank" class="inline-flex items-center gap-2 text-purple-600 font-bold hover:underline">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            {{ __('View Attached Treatment File') }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="bg-white p-12 text-center rounded-2xl border-2 border-dashed border-gray-100">
                <p class="text-gray-400">{{ __('No previous visits recorded for this patient.') }}</p>
            </div>
            @endforelse
        </div>

        <div class="space-y-8">
            <!-- Family History -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm relative">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg">{{ __('Family Medical History') }}</h3>
                    @if(!$isEditingHistory)
                    <button wire:click="$set('isEditingHistory', true)" class="text-purple-600 hover:bg-purple-50 p-2 rounded-lg transition-colors" title="{{ __('Edit') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </button>
                    @endif
                </div>

                @if($isEditingHistory)
                <form wire:submit="saveFamilyHistory" class="space-y-3">
                    <textarea wire:model="familyHistoryEdit" rows="4" class="w-full px-4 py-3 bg-gray-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500" placeholder="{{ __('Record any relevant hereditary diseases or family medical context...') }}"></textarea>
                    <div class="flex items-center gap-2 justify-end">
                        <button type="button" wire:click="$set('isEditingHistory', false)" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">{{ __('Cancel') }}</button>
                        <button type="submit" class="px-4 py-2 text-sm font-bold bg-purple-600 text-white rounded-xl shadow-lg shadow-purple-200 hover:bg-purple-700 transition-colors">{{ __('Save History') }}</button>
                    </div>
                </form>
                @else
                <p class="text-gray-600 text-sm leading-relaxed whitespace-pre-line">
                    {{ $patient->family_history ?: __('No data recorded.') }}
                </p>
                @endif
            </div>

            <!-- Medical Files & X-Rays -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <h3 class="font-bold text-lg mb-4">{{ __('Medical Files & X-Rays') }}</h3>
                
                @if (session()->has('message'))
                    <div class="mb-4 p-3 bg-green-50 text-green-700 text-sm rounded-xl border border-green-100">
                        {{ session('message') }}
                    </div>
                @endif
                
                <div class="space-y-4 mb-6 max-h-[300px] overflow-y-auto custom-scrollbar pr-2">
                    @forelse($patient->files as $file)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="p-2 {{ $file->file_type === 'xray' ? 'bg-blue-100 text-blue-600' : ($file->file_type === 'lab' ? 'bg-rose-100 text-rose-600' : 'bg-purple-100 text-purple-600') }} rounded-lg shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-sm font-bold text-gray-800 truncate" title="{{ $file->file_name }}">{{  Str::limit($file->file_name, 20) }}</span>
                                <span class="text-[10px] text-gray-500 uppercase font-bold">{{ __($file->file_type) }} • {{ $file->created_at->format('Y-m-d') }}</span>
                            </div>
                        </div>
                        <a href="{{ Storage::disk('public')->url($file->file_path) }}" target="_blank" class="text-purple-600 text-xs font-bold shrink-0 hover:bg-purple-50 p-2 rounded-lg transition-colors">
                            {{ __('View') }}
                        </a>
                    </div>
                    @empty
                    <p class="text-sm text-gray-400 text-center py-4">{{ __('No files uploaded yet.') }}</p>
                    @endforelse
                </div>
                
                <!-- Upload Form -->
                <form wire:submit="uploadFile" class="pt-4 border-t border-dashed border-gray-200 space-y-3 relative">
                    <div class="flex items-center gap-2">
                        <select wire:model="fileType" class="w-1/3 px-3 py-2 text-sm bg-gray-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500">
                            <option value="lab">{{ __('Lab Result') }}</option>
                            <option value="xray">{{ __('X-Ray') }}</option>
                            <option value="other">{{ __('Other Document') }}</option>
                        </select>
                        <input type="file" wire:model="newFile" class="block w-2/3 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 transition-all cursor-pointer">
                    </div>
                    
                    <div wire:loading wire:target="newFile" class="text-xs text-purple-600 font-bold loading-dots">{{ __('Uploading file...') }}</div>
                    @error('newFile') <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror
                    
                    <button type="submit" wire:loading.attr="disabled" class="w-full py-2.5 bg-purple-600 text-white rounded-xl text-sm font-bold shadow-md shadow-purple-200 hover:bg-purple-700 transition-colors disabled:opacity-50">
                        {{ __('Upload File') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Record New Visit Modal -->
    @if($showVisitModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div wire:click="closeVisitModal" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="bg-white rounded-[2.5rem] w-full max-w-2xl shadow-2xl relative overflow-hidden animate-zoom-in">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full -translate-y-16 translate-x-16 blur-2xl"></div>
            
            <form wire:submit="saveVisit" class="relative">
                <div class="p-8 md:p-10">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-2xl font-black text-gray-900 flex items-center gap-3">
                            <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center shadow-inner">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                            </div>
                            {{ __('Record Clinical Visit') }}
                        </h3>
                        <button type="button" wire:click="closeVisitModal" class="p-3 hover:bg-gray-100 rounded-full transition-colors text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('Patient Complaint') }} <span class="text-rose-500">*</span></label>
                            <textarea wire:model="complaint" rows="3" placeholder="{{ __('What is the patient suffering from?') }}" 
                                      class="w-full bg-slate-50 border-gray-200 rounded-2xl py-4 px-5 text-sm focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"></textarea>
                            @error('complaint') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('Preliminary Diagnosis') }} <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="diagnosis" placeholder="{{ __('Enter diagnosis...') }}"
                                   class="w-full bg-slate-50 border-gray-200 rounded-2xl py-4 px-5 text-sm focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all">
                            @error('diagnosis') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('Investigations & Tests') }}</label>
                            <textarea wire:model="investigation" rows="4" placeholder="{{ __('Requested labs, x-rays, etc.') }}"
                                      class="w-full bg-slate-50 border-gray-200 rounded-2xl py-4 px-5 text-sm focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"></textarea>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('Treatment Plan') }}</label>
                            <textarea wire:model="treatmentText" rows="4" placeholder="{{ __('Prescribed medications and instructions.') }}"
                                      class="w-full bg-slate-50 border-gray-200 rounded-2xl py-4 px-5 text-sm focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"></textarea>
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('Attach Treatment File (Optional)') }}</label>
                            <div class="flex items-center gap-4 p-4 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl hover:border-emerald-300 transition-colors cursor-pointer group relative">
                                <input type="file" wire:model="treatmentFile" class="absolute inset-0 opacity-0 cursor-pointer">
                                <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-emerald-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700">{{ __('Click to upload prescription or report') }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase">{{ __('PDF, JPG, PNG up to 10MB') }}</p>
                                </div>
                            </div>
                            @if($treatmentFile)
                                <div class="mt-2 text-xs text-emerald-600 font-black flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    {{ $treatmentFile->getClientOriginalName() }}
                                </div>
                            @endif
                            @error('treatmentFile') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-10 flex gap-4">
                        <button type="button" wire:click="closeVisitModal" class="flex-1 py-4 px-6 bg-slate-100 hover:bg-slate-200 text-slate-700 font-black rounded-2xl transition-all uppercase tracking-widest text-xs">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="flex-[2] py-4 px-6 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black rounded-2xl shadow-xl shadow-emerald-100 transition-all uppercase tracking-widest text-xs hover:-translate-y-1">
                            {{ __('Save Visit Record') }}
                        </button>
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
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
            
            <div class="p-8 text-right">
                <div class="flex items-center justify-between mb-8 flex-row-reverse">
                    <h3 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        {{ __('Book Appointment') }}
                    </h3>
                    <button wire:click="closeBookingModal" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="space-y-6">
                    <div class="p-4 bg-indigo-50 rounded-2xl border border-indigo-100 flex items-center justify-center">
                        <p class="font-bold text-indigo-900">{{ __('Booking for:') }} {{ $patient->name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest block text-right">{{ __('Date') }}</label>
                            <input type="date" wire:model="bookingDate" class="w-full bg-slate-50 border-gray-200 rounded-2xl py-3 px-4 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest block text-right">{{ __('Visit Type') }}</label>
                            <select wire:model="bookingType" class="w-full bg-slate-50 border-gray-200 rounded-2xl py-3 px-4 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                                <option value="checkup">{{ __('Checkup') }}</option>
                                <option value="follow_up">{{ __('Follow-up') }}</option>
                            </select>
                        </div>
                    </div>

                    @if(auth()->user()->role !== 'doctor')
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-500 uppercase tracking-widest block text-right">{{ __('Doctor') }}</label>
                        <select wire:model="bookingDoctorId" class="w-full bg-slate-50 border-gray-200 rounded-2xl py-3 px-4 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">
                            <option value="">{{ __('Choose a doctor...') }}</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}">{{ __('Dr.') }} {{ $doctor->name }}</option>
                            @endforeach
                        </select>
                        @error('bookingDoctorId') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
                    </div>
                    @endif

                    <div class="pt-4 flex gap-4">
                        <button type="button" wire:click="closeBookingModal" class="flex-1 py-4 px-6 bg-slate-100 hover:bg-slate-200 text-slate-700 font-black rounded-2xl transition-all uppercase tracking-widest text-xs">
                            {{ __('Cancel') }}
                        </button>
                        <button type="button" wire:click="confirmBooking" class="flex-[2] py-4 px-6 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-black rounded-2xl shadow-xl shadow-indigo-100 transition-all uppercase tracking-widest text-xs hover:-translate-y-1">
                            {{ __('Confirm Booking') }}
                        </button>
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
        <div class="bg-white rounded-[2.5rem] w-full max-w-lg shadow-2xl relative overflow-hidden animate-zoom-in">
            <div class="absolute top-0 right-0 w-32 h-32 bg-purple-500/10 rounded-full -translate-y-16 translate-x-16 blur-2xl"></div>
            
            <form wire:submit="savePatientData" class="relative">
                <div class="p-8 md:p-10">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-2xl font-black text-gray-900 flex items-center gap-3">
                            <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center shadow-inner">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </div>
                            {{ __('Edit Patient Data') }}
                        </h3>
                        <button type="button" wire:click="closeEditModal" class="p-3 hover:bg-gray-100 rounded-full transition-colors text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="space-y-5">
                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('Full Name') }} <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="editName" class="w-full bg-slate-50 border-gray-200 rounded-2xl py-4 px-5 text-sm focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 transition-all">
                            @error('editName') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('Phone Number') }} <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="editPhone" class="w-full bg-slate-50 border-gray-200 rounded-2xl py-4 px-5 text-sm focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 transition-all">
                                @error('editPhone') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('Age') }}</label>
                                <input type="number" min="0" wire:model="editAge" class="w-full bg-slate-50 border-gray-200 rounded-2xl py-4 px-5 text-sm focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 transition-all">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('Address / Details') }}</label>
                            <input type="text" wire:model="editAddress" class="w-full bg-slate-50 border-gray-200 rounded-2xl py-4 px-5 text-sm focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 transition-all">
                        </div>
                    </div>

                    <div class="mt-10 flex gap-4">
                        <button type="button" wire:click="closeEditModal" class="flex-1 py-4 px-6 bg-slate-100 hover:bg-slate-200 text-slate-700 font-black rounded-2xl transition-all uppercase tracking-widest text-xs">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="flex-[2] py-4 px-6 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-black rounded-2xl shadow-xl shadow-purple-100 transition-all uppercase tracking-widest text-xs hover:-translate-y-1">
                            {{ __('Update Records') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
