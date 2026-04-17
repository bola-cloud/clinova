<?php

use Livewire\Volt\Component;
use App\Models\Specialty;
use App\Models\SpecialtyField;

new class extends Component
{
    public $specialties;
    public $selectedSpecialty = null;
    
    // Specialty Form
    public $specialtyName = '';
    public $showSpecialtyModal = false;
    public $editingSpecialtyId = null;

    // Field Form
    public $fieldLabel = '';
    public $fieldType = 'text';
    public $fieldOptions = [];
    public $newOption = '';
    public $showFieldModal = false;
    public $editingFieldId = null;

    public function mount()
    {
        $this->loadSpecialties();
    }

    public function loadSpecialties()
    {
        $this->specialties = Specialty::with('fields')->get();
    }

    public function openSpecialtyModal($id = null)
    {
        $this->reset(['specialtyName', 'editingSpecialtyId']);
        if ($id) {
            $specialty = Specialty::find($id);
            $this->editingSpecialtyId = $id;
            $this->specialtyName = $specialty->name;
        }
        $this->showSpecialtyModal = true;
    }

    public function saveSpecialty()
    {
        $this->validate(['specialtyName' => 'required|min:2']);

        if ($this->editingSpecialtyId) {
            Specialty::find($this->editingSpecialtyId)->update(['name' => $this->specialtyName]);
        } else {
            Specialty::create(['name' => $this->specialtyName]);
        }

        $this->loadSpecialties();
        $this->showSpecialtyModal = false;
    }

    public function deleteSpecialty($id)
    {
        Specialty::find($id)->delete();
        $this->loadSpecialties();
        if ($this->selectedSpecialty && $this->selectedSpecialty->id == $id) {
            $this->selectedSpecialty = null;
        }
    }

    public function selectSpecialty($id)
    {
        $this->selectedSpecialty = Specialty::with('fields')->find($id);
    }

    public function openFieldModal($id = null)
    {
        $this->reset(['fieldLabel', 'fieldType', 'fieldOptions', 'newOption', 'editingFieldId']);
        if ($id) {
            $field = SpecialtyField::find($id);
            $this->editingFieldId = $id;
            $this->fieldLabel = $field->label;
            $this->fieldType = $field->type;
            $this->fieldOptions = $field->options ?? [];
        }
        $this->showFieldModal = true;
    }

    public function addOption()
    {
        if (trim($this->newOption)) {
            $this->fieldOptions[] = trim($this->newOption);
            $this->newOption = '';
        }
    }

    public function removeOption($index)
    {
        unset($this->fieldOptions[$index]);
        $this->fieldOptions = array_values($this->fieldOptions);
    }

    public function saveField()
    {
        $this->validate([
            'fieldLabel' => 'required|min:2',
            'fieldType' => 'required|in:text,select,multi_select',
        ]);

        if (in_array($this->fieldType, ['select', 'multi_select']) && empty($this->fieldOptions)) {
            $this->addError('newOption', __('At least one option is required.'));
            return;
        }

        $data = [
            'specialty_id' => $this->selectedSpecialty->id,
            'label' => $this->fieldLabel,
            'type' => $this->fieldType,
            'options' => in_array($this->fieldType, ['select', 'multi_select']) ? $this->fieldOptions : null,
        ];

        if ($this->editingFieldId) {
            SpecialtyField::find($this->editingFieldId)->update($data);
        } else {
            SpecialtyField::create($data);
        }

        $this->selectSpecialty($this->selectedSpecialty->id);
        $this->loadSpecialties();
        $this->showFieldModal = false;
    }

    public function deleteField($id)
    {
        SpecialtyField::find($id)->delete();
        $this->selectSpecialty($this->selectedSpecialty->id);
        $this->loadSpecialties();
    }
};
?>

<div class="space-y-6">
    <div class="flex items-center justify-between bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">{{ __('Specialties Management') }}</h2>
            <p class="text-gray-500 font-medium mt-1">{{ __('Define medical specialties and their custom visit fields.') }}</p>
        </div>
        <button wire:click="openSpecialtyModal()" class="px-8 py-4 bg-slate-900 text-white rounded-2xl font-black text-sm shadow-xl hover:bg-black hover:-translate-y-1 transition-all flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            {{ __('Add New Specialty') }}
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Specialties List -->
        <div class="lg:col-span-1 space-y-4">
            @foreach($specialties as $specialty)
            <div wire:click="selectSpecialty({{ $specialty->id }})" 
                 class="group p-6 rounded-[2rem] border transition-all cursor-pointer {{ ($selectedSpecialty && $selectedSpecialty->id == $specialty->id) ? 'bg-purple-600 border-purple-600 shadow-xl shadow-purple-100' : 'bg-white border-gray-100 hover:border-purple-200' }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-lg {{ ($selectedSpecialty && $selectedSpecialty->id == $specialty->id) ? 'bg-white/20 text-white' : 'bg-purple-50 text-purple-600' }}">
                            {{ mb_substr($specialty->name, 0, 1) }}
                        </div>
                        <div>
                            <h4 class="font-black {{ ($selectedSpecialty && $selectedSpecialty->id == $specialty->id) ? 'text-white' : 'text-slate-900' }}">{{ $specialty->name }}</h4>
                            <p class="text-xs font-bold {{ ($selectedSpecialty && $selectedSpecialty->id == $specialty->id) ? 'text-purple-100' : 'text-gray-400' }} uppercase tracking-widest">{{ $specialty->fields->count() }} {{ __('Fields') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button wire:click.stop="openSpecialtyModal({{ $specialty->id }})" class="p-2 rounded-xl {{ ($selectedSpecialty && $selectedSpecialty->id == $specialty->id) ? 'bg-white/20 text-white hover:bg-white/30' : 'bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-600' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </button>
                        <button wire:click.stop="deleteSpecialty({{ $specialty->id }})" wire:confirm="{{ __('Are you sure you want to delete this specialty and all its fields?') }}" class="p-2 rounded-xl {{ ($selectedSpecialty && $selectedSpecialty->id == $specialty->id) ? 'bg-white/20 text-white hover:bg-rose-500' : 'bg-slate-50 text-slate-400 hover:bg-rose-50 hover:text-rose-600' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Field List -->
        <div class="lg:col-span-2">
            @if($selectedSpecialty)
            <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden animate-slide-in">
                <div class="p-8 border-b border-gray-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ $selectedSpecialty->name }} - {{ __('Custom Fields') }}</h3>
                        <p class="text-gray-500 font-medium text-sm">{{ __('These fields will appear in visit forms for doctors with this specialty.') }}</p>
                    </div>
                    <button wire:click="openFieldModal()" class="px-6 py-3 bg-purple-600 text-white rounded-2xl font-black text-xs shadow-lg shadow-purple-100 hover:bg-purple-700 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        {{ __('Add Field') }}
                    </button>
                </div>
                <div class="p-8">
                    @if($selectedSpecialty->fields->count() > 0)
                    <div class="space-y-4">
                        @foreach($selectedSpecialty->fields as $field)
                        <div class="flex items-center justify-between p-6 bg-slate-50 rounded-[2rem] border border-slate-100 group">
                            <div class="flex items-center gap-6">
                                <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 group-hover:text-purple-600 transition-colors">
                                    @if($field->type === 'text')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                                    @endif
                                </div>
                                <div>
                                    <h5 class="font-bold text-slate-900">{{ $field->label }}</h5>
                                    <div class="flex items-center gap-3">
                                        <span class="text-[10px] uppercase font-black tracking-widest text-slate-400">
                                            @if($field->type === 'text')
                                                {{ __('Text Field') }}
                                            @elseif($field->type === 'select')
                                                {{ __('Single Selection') }}
                                            @else
                                                {{ __('Multiple Selection') }}
                                            @endif
                                        </span>
                                        @if(in_array($field->type, ['select', 'multi_select']))
                                        <span class="text-[10px] text-purple-600 font-bold bg-purple-50 px-2 py-0.5 rounded-lg">{{ count($field->options) }} {{ __('Options') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button wire:click="openFieldModal({{ $field->id }})" class="p-3 bg-white rounded-xl border border-slate-200 text-slate-400 hover:text-slate-600 hover:border-slate-300 transition-all shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                <button wire:click="deleteField({{ $field->id }})" wire:confirm="{{ __('Delete this field?') }}" class="p-3 bg-white rounded-xl border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-100 hover:bg-rose-50 transition-all shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-300">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h4 class="font-bold text-slate-900 mb-1">{{ __('No dynamic fields yet') }}</h4>
                        <p class="text-sm text-gray-500">{{ __('Add custom fields to collect specific data for this specialty.') }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @else
            <div class="h-full min-h-[400px] flex flex-col items-center justify-center bg-white rounded-[2.5rem] border border-gray-100 border-dashed text-center p-12">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-6 text-slate-300">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <h3 class="text-xl font-black text-slate-900 mb-2">{{ __('Select any specialty') }}</h3>
                <p class="text-gray-500 max-w-xs">{{ __('Choose a specialty from the left to manage its custom metadata fields.') }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Specialty Modal -->
    @if($showSpecialtyModal)
    <div class="fixed inset-0 z-[60] overflow-y-auto flex justify-center items-start p-4 sm:p-6">
        <div wire:click="$set('showSpecialtyModal', false)" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
        <div class="bg-white rounded-[2.5rem] w-full max-w-lg shadow-2xl relative mx-auto my-8 animate-zoom-in">
            <div class="p-10">
                <h3 class="text-2xl font-black text-slate-900 mb-8">{{ $editingSpecialtyId ? __('Edit Specialty') : __('New Specialty') }}</h3>
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('Specialty Name') }}</label>
                        <input type="text" wire:model="specialtyName" class="w-full bg-slate-50 border-gray-100 rounded-2xl py-4 px-6 text-sm focus:ring-4 focus:ring-purple-500/10 transition-all" placeholder="{{ __('e.g., Cardiology, Pediatrics...') }}">
                        @error('specialtyName') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
                    </div>
                    <div class="pt-6 flex gap-4">
                        <button type="button" wire:click="$set('showSpecialtyModal', false)" class="flex-1 py-4 text-slate-500 font-bold hover:text-slate-700 transition-colors">{{ __('Cancel') }}</button>
                        <button wire:click="saveSpecialty" class="flex-[2] py-4 bg-slate-900 text-white rounded-2xl font-black shadow-xl hover:bg-black transition-all">
                            {{ __('Save Specialty') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Field Modal -->
    @if($showFieldModal)
    <div class="fixed inset-0 z-[60] overflow-y-auto flex justify-center items-start p-4 sm:p-6">
        <div wire:click="$set('showFieldModal', false)" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
        <div class="bg-white rounded-[2.5rem] w-full max-w-xl shadow-2xl relative mx-auto my-8 animate-zoom-in">
            <div class="p-10">
                <h3 class="text-2xl font-black text-slate-900 mb-8">{{ $editingFieldId ? __('Edit Field') : __('Add New Field') }}</h3>
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('Field Label') }}</label>
                        <input type="text" wire:model="fieldLabel" class="w-full bg-slate-50 border-gray-100 rounded-2xl py-4 px-6 text-sm focus:ring-4 focus:ring-purple-500/10 transition-all" placeholder="{{ __('e.g., Blood Pressure, Symptoms...') }}">
                        @error('fieldLabel') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('Field Type') }}</label>
                        <div class="grid grid-cols-3 gap-4">
                            <button wire:click="$set('fieldType', 'text')" 
                                    class="p-4 rounded-2xl border-2 transition-all flex flex-col items-center gap-2 {{ $fieldType === 'text' ? 'bg-purple-50 border-purple-600 text-purple-700' : 'bg-slate-50 border-transparent text-slate-400 grayscale' }}">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                <span class="text-[10px] font-black">{{ __('Text Field') }}</span>
                            </button>
                            <button wire:click="$set('fieldType', 'select')" 
                                    class="p-4 rounded-2xl border-2 transition-all flex flex-col items-center gap-2 {{ $fieldType === 'select' ? 'bg-purple-50 border-purple-600 text-purple-700' : 'bg-slate-50 border-transparent text-slate-400 grayscale' }}">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                                <span class="text-[10px] font-black">{{ __('Single Choice') }}</span>
                            </button>
                            <button wire:click="$set('fieldType', 'multi_select')" 
                                    class="p-4 rounded-2xl border-2 transition-all flex flex-col items-center gap-2 {{ $fieldType === 'multi_select' ? 'bg-purple-50 border-purple-600 text-purple-700' : 'bg-slate-50 border-transparent text-slate-400 grayscale' }}">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                <span class="text-[10px] font-black">{{ __('Multi-Select') }}</span>
                            </button>
                        </div>
                    </div>

                    @if(in_array($fieldType, ['select', 'multi_select']))
                    <div class="space-y-4 pt-4 border-t border-dashed border-gray-100 animate-slide-in">
                        <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('Options') }}</label>
                        <div class="flex gap-2">
                            <input type="text" wire:model="newOption" wire:keydown.enter="addOption" class="flex-1 bg-slate-50 border-gray-100 rounded-2xl py-4 px-6 text-sm focus:ring-4 focus:ring-purple-500/10 transition-all" placeholder="{{ __('Add option...') }}">
                            <button wire:click="addOption" class="px-6 bg-purple-600 text-white rounded-2xl font-black text-sm hover:bg-purple-700 transition-all">
                                {{ __('Add') }}
                            </button>
                        </div>
                        @error('newOption') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
                        
                        <div class="flex flex-wrap gap-2">
                            @foreach($fieldOptions as $index => $opt)
                            <div class="bg-purple-50 border border-purple-100 px-4 py-2 rounded-xl text-sm font-bold text-purple-700 flex items-center gap-3 group">
                                {{ $opt }}
                                <button wire:click="removeOption({{ $index }})" class="text-purple-300 hover:text-rose-500 transition-colors">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="pt-6 flex gap-4">
                        <button type="button" wire:click="$set('showFieldModal', false)" class="flex-1 py-4 text-slate-500 font-bold hover:text-slate-700 transition-colors">{{ __('Cancel') }}</button>
                        <button wire:click="saveField" class="flex-[2] py-4 bg-purple-600 text-white rounded-2xl font-black shadow-xl shadow-purple-100 hover:bg-purple-700 transition-all">
                            {{ __('Save Field') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
