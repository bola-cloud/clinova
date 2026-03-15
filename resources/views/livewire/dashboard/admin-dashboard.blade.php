<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\User;

new class extends Component
{
    use WithPagination;

    public $search = '';

    public function with()
    {
        return [
            'doctors' => User::where('role', 'doctor')
                ->when($this->search, fn($q) => $q->where('name', 'like', '%'.$this->search.'%')->orWhere('email', 'like', '%'.$this->search.'%'))
                ->paginate(10)
        ];
    }

    public function toggleSubscription($doctorId)
    {
        $doctor = User::findOrFail($doctorId);
        $active = !$doctor->subscription_active;
        $doctor->update([
            'subscription_active' => $active,
            'subscription_expires_at' => $active ? now()->addMonth() : null
        ]);
    }
};
?>

<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <h3 class="font-bold text-lg">{{ __('Manage Doctors & Subscriptions') }}</h3>
            <div class="w-full md:w-64">
                <input wire:model.live="search" type="text" placeholder="{{ __('Search') }}..." 
                       class="w-full px-4 py-2 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 text-sm">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-right" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                <thead class="bg-gray-50 text-gray-500 text-sm">
                    <tr>
                        <th class="px-6 py-4 font-medium">{{ __('Doctor') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Email') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Subscription Status') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Expiry Date') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @if(count($doctors) > 0)
                        @foreach($doctors as $doctor)
                        <tr>
                            <td class="px-6 py-4 font-medium">{{ $doctor->name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $doctor->email }}</td>
                            <td class="px-6 py-4">
                                @if($doctor->subscription_active)
                                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs rounded-full">نشط</span>
                                @else
                                    <span class="px-3 py-1 bg-red-100 text-red-700 text-xs rounded-full">ملغي</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $doctor->subscription_expires_at ? $doctor->subscription_expires_at->format('Y-m-d') : '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <button wire:click="toggleSubscription({{ $doctor->id }})" 
                                        class="px-4 py-1 rounded-lg text-sm font-bold {{ $doctor->subscription_active ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }}">
                                    {{ $doctor->subscription_active ? __('Deactivate') : __('Activate') }}
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400">{{ __('No doctors found.') }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        @if($doctors->hasPages())
        <div class="p-6 border-t border-gray-100">
            {{ $doctors->links() }}
        </div>
        @endif
    </div>
</div>