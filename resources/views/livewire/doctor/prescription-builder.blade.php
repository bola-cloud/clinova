<div class="space-y-8" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
    x-data="prescriptionBuilder(@js($elements))"
    x-init="
        $watch('elements', value => {
            $wire.saveLayout(value);
        }, { deep: true });
    "
>
    <!-- Header -->
    <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/5 rounded-full -translate-y-32 translate-x-32 blur-3xl"></div>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative z-10">
            <div>
                <h2 class="text-3xl font-black text-gray-900 mb-2">{{ __('Prescription Settings & Builder') }}</h2>
                <p class="text-gray-500 font-bold">{{ __('Upload your prescription design and configure where data should be printed.') }}</p>
            </div>
            
            <div class="flex gap-3">
                <label class="cursor-pointer px-6 py-3 bg-indigo-600 text-white rounded-xl font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                    <input type="file" wire:model.live="backgroundImage" class="hidden" accept="image/*">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    <span>{{ __('Upload A5 Background') }}</span>
                </label>
                
                @if($currentBackgroundImagePath)
                <button wire:click="removeBackground" wire:confirm="{{ __('Are you sure?') }}" class="px-6 py-3 bg-rose-50 text-rose-600 rounded-xl font-bold hover:bg-rose-100 transition-colors">
                    {{ __('Remove') }}
                </button>
                @endif
            </div>
        </div>

        @if (session()->has('message'))
            <div class="mt-4 p-4 bg-emerald-50 text-emerald-700 text-sm font-bold rounded-2xl border border-emerald-100 flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('message') }}
            </div>
        @endif
        @error('backgroundImage') <span class="text-rose-500 text-xs font-bold block mt-2">{{ $message }}</span> @enderror
        <div wire:loading wire:target="backgroundImage" class="mt-2 text-indigo-600 text-xs font-bold animate-pulse">{{ __('Uploading...') }}</div>
    </div>

    <!-- Builder Area -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <!-- Controls Sidebar -->
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <h3 class="font-black text-gray-900 mb-4">{{ __('Printable Fields') }}</h3>
                <p class="text-xs text-gray-500 mb-4">{{ __('Toggle visibility and adjust font sizes for each field on your prescription.') }}</p>
                
                <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2 custom-scrollbar">
                    <template x-for="(field, key) in elements" :key="key">
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-gray-800" x-text="field.label"></span>
                                <button type="button" @click="field.visible = !field.visible" 
                                        :class="field.visible ? 'bg-emerald-500' : 'bg-gray-300'"
                                        class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none">
                                    <span :class="field.visible ? (document.dir === 'rtl' ? '-translate-x-4' : 'translate-x-4') : 'translate-x-0'"
                                          class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                                </button>
                            </div>
                            
                            <div x-show="field.visible" x-collapse class="space-y-2 pt-2 border-t border-gray-200">
                                <div>
                                    <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">{{ __('Font Size') }} (px)</label>
                                    <input type="number" x-model.number="field.fontSize" class="w-full bg-white border-gray-200 rounded-lg py-1 px-2 text-xs">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">{{ __('Width') }} (%)</label>
                                    <input type="number" x-model.number="field.width" min="10" max="100" class="w-full bg-white border-gray-200 rounded-lg py-1 px-2 text-xs">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            
            <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100">
                <h4 class="text-sm font-black text-indigo-900 mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ __('How to use') }}
                </h4>
                <ul class="text-xs text-indigo-800 space-y-2 list-disc list-inside">
                    <li>{{ __('Drag and drop the blue boxes on the paper to set their position.') }}</li>
                    <li>{{ __('Use the sidebar to change font size or hide elements.') }}</li>
                    <li>{{ __('Width limits how far text can go before wrapping to a new line.') }}</li>
                    <li>{{ __('The layout saves automatically.') }}</li>
                </ul>
            </div>
        </div>

        <!-- Interactive Canvas -->
        <div class="lg:col-span-3 flex justify-center bg-gray-100 p-8 rounded-3xl border-2 border-dashed border-gray-200 overflow-x-auto relative">
            
            <!-- Loading indicator for Livewire -->
            <div wire:loading class="absolute inset-0 bg-white/50 backdrop-blur-sm z-50 rounded-3xl flex items-center justify-center">
                <span class="px-4 py-2 bg-white rounded-xl shadow-lg font-bold text-indigo-600 flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    {{ __('Saving layout...') }}
                </span>
            </div>

            <!-- A5 Paper Representation -->
            <!-- Standard A5 Aspect Ratio is 1 : 1.414. We'll use a fixed width container that scales on smaller screens. -->
            <div id="prescription-paper" class="relative bg-white shadow-2xl border border-gray-200 select-none" 
                 style="width: 500px; height: 707px; max-width: 100%; aspect-ratio: 1 / 1.414; background-color: white; {{ $currentBackgroundImagePath ? 'background-image: url('.Storage::url($currentBackgroundImagePath).'); background-size: cover; background-position: center;' : '' }}">
                
                @if(!$currentBackgroundImagePath)
                    <div class="absolute inset-0 flex items-center justify-center text-gray-300 font-bold text-2xl rotate-45 pointer-events-none">
                        {{ __('A5 Paper (No Background)') }}
                    </div>
                @endif

                <template x-for="(field, key) in elements" :key="key">
                    <div x-show="field.visible"
                         class="absolute border-2 border-indigo-500 bg-indigo-500/20 rounded cursor-move group hover:bg-indigo-500/40 hover:shadow-lg transition-colors overflow-hidden"
                         :style="`
                            top: ${field.y}%; 
                            left: ${document.dir === 'rtl' ? 'auto' : field.x + '%'}; 
                            right: ${document.dir === 'rtl' ? field.x + '%' : 'auto'}; 
                            width: ${field.width}%;
                         `"
                         @mousedown="startDrag($event, key)"
                         @touchstart="startDrag($event, key)"
                    >
                        <!-- Resize Handle (Right/Left depending on RTL) -->
                        <div class="absolute top-0 bottom-0 w-2 cursor-col-resize hover:bg-indigo-600 transition-colors"
                             :class="document.dir === 'rtl' ? 'left-0' : 'right-0'"
                             @mousedown.stop="startResize($event, key)"
                             @touchstart.stop="startResize($event, key)"
                             title="{{ __('Drag to resize width') }}"
                        ></div>

                        <div class="p-1 min-h-[30px] flex items-start text-indigo-900 font-bold break-words pointer-events-none"
                             :style="`font-size: ${field.fontSize}px; line-height: 1.2;`"
                        >
                            <span x-text="`[${field.label}]`"></span>
                        </div>
                        
                        <!-- Coordinate Tooltip -->
                        <div class="absolute -top-6 left-0 bg-gray-900 text-white text-[10px] px-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">
                            X: <span x-text="Math.round(field.x)"></span>%, Y: <span x-text="Math.round(field.y)"></span>%
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
    function prescriptionBuilder(initialElements) {
        return {
            elements: initialElements,
            draggingField: null,
            resizingField: null,
            startX: 0,
            startY: 0,
            startLeft: 0,
            startTop: 0,
            startWidth: 0,
            paperWidth: 0,
            paperHeight: 0,
            isRtl: document.dir === 'rtl',

            init() {
                this.boundDrag = this.drag.bind(this);
                this.boundStopDrag = this.stopDrag.bind(this);
                this.boundResize = this.resize.bind(this);
                this.boundStopResize = this.stopResize.bind(this);
            },

            startDrag(e, key) {
                if (e.type === 'touchstart') {
                    e.preventDefault(); // Prevent scrolling
                }
                this.draggingField = key;
                this.paperWidth = document.getElementById('prescription-paper').offsetWidth;
                this.paperHeight = document.getElementById('prescription-paper').offsetHeight;
                
                const clientX = e.type === 'touchstart' ? e.touches[0].clientX : e.clientX;
                const clientY = e.type === 'touchstart' ? e.touches[0].clientY : e.clientY;

                this.startX = clientX;
                this.startY = clientY;
                this.startLeft = this.elements[key].x;
                this.startTop = this.elements[key].y;

                document.addEventListener('mousemove', this.boundDrag);
                document.addEventListener('touchmove', this.boundDrag, { passive: false });
                document.addEventListener('mouseup', this.boundStopDrag);
                document.addEventListener('touchend', this.boundStopDrag);
            },

            drag(e) {
                if (!this.draggingField) return;
                if (e.type === 'touchmove') e.preventDefault();

                const clientX = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX;
                const clientY = e.type === 'touchmove' ? e.touches[0].clientY : e.clientY;

                const dx = clientX - this.startX;
                const dy = clientY - this.startY;

                const dxPercent = (dx / this.paperWidth) * 100;
                const dyPercent = (dy / this.paperHeight) * 100;

                // If RTL, dragging right (positive dx) means less distance from right (smaller x%)
                let newX = this.isRtl ? this.startLeft - dxPercent : this.startLeft + dxPercent;
                let newY = this.startTop + dyPercent;

                // Boundaries
                if (newX < 0) newX = 0;
                if (newY < 0) newY = 0;
                if (newX + this.elements[this.draggingField].width > 100) newX = 100 - this.elements[this.draggingField].width;
                if (newY > 95) newY = 95;

                this.elements[this.draggingField].x = newX;
                this.elements[this.draggingField].y = newY;
            },

            stopDrag() {
                this.draggingField = null;
                document.removeEventListener('mousemove', this.boundDrag);
                document.removeEventListener('touchmove', this.boundDrag);
                document.removeEventListener('mouseup', this.boundStopDrag);
                document.removeEventListener('touchend', this.boundStopDrag);
            },

            startResize(e, key) {
                if (e.type === 'touchstart') e.preventDefault();
                this.resizingField = key;
                this.paperWidth = document.getElementById('prescription-paper').offsetWidth;
                
                const clientX = e.type === 'touchstart' ? e.touches[0].clientX : e.clientX;
                this.startX = clientX;
                this.startWidth = this.elements[key].width;

                document.addEventListener('mousemove', this.boundResize);
                document.addEventListener('touchmove', this.boundResize, { passive: false });
                document.addEventListener('mouseup', this.boundStopResize);
                document.addEventListener('touchend', this.boundStopResize);
            },

            resize(e) {
                if (!this.resizingField) return;
                if (e.type === 'touchmove') e.preventDefault();

                const clientX = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX;
                const dx = clientX - this.startX;
                const dxPercent = (dx / this.paperWidth) * 100;

                // RTL logic: moving mouse left (negative dx) increases width
                let newWidth = this.isRtl ? this.startWidth - dxPercent : this.startWidth + dxPercent;

                if (newWidth < 10) newWidth = 10;
                
                // Prevent overflowing the container horizontally
                if (this.elements[this.resizingField].x + newWidth > 100) {
                    newWidth = 100 - this.elements[this.resizingField].x;
                }

                this.elements[this.resizingField].width = newWidth;
            },

            stopResize() {
                this.resizingField = null;
                document.removeEventListener('mousemove', this.boundResize);
                document.removeEventListener('touchmove', this.boundResize);
                document.removeEventListener('mouseup', this.boundStopResize);
                document.removeEventListener('touchend', this.boundStopResize);
            }
        }
    }
</script>
