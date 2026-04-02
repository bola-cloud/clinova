<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Subscription;
use App\Models\Setting;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class DoctorSubscriptionManager extends Component
{
    use WithPagination;

    public User $doctor;
    
    // New subscription form
    public $plan = 'monthly';
    public $price = 0;
    public $is_paid = false;
    public $start_date;
    public $trial_days = 14;

    protected $rules = [
        'plan' => 'required|in:trial,monthly,yearly',
        'price' => 'required|numeric|min:0',
        'is_paid' => 'boolean',
        'start_date' => 'required|date',
        'trial_days' => 'required_if:plan,trial|numeric|min:1',
    ];

    public function mount(User $doctor)
    {
        $this->doctor = $doctor;
        $this->start_date = now()->format('Y-m-d');
        $this->trial_days = Setting::get('trial_duration_days', 14);
        
        // Suggest price based on previous or defaults
        if ($this->doctor->subscription_price > 0) {
            $this->price = $this->doctor->subscription_price;
        }
    }

    public function saveSubscription()
    {
        $this->validate();

        $startDate = Carbon::parse($this->start_date);
        $expiresAt = $startDate->copy();

        if ($this->plan === 'monthly') {
            $expiresAt = $startDate->copy()->addMonth();
        } elseif ($this->plan === 'yearly') {
            $expiresAt = $startDate->copy()->addYear();
        } elseif ($this->plan === 'trial') {
            $expiresAt = $startDate->copy()->addDays((int) $this->trial_days);
        }

        // 1. Update Doctor Current Status
        $this->doctor->update([
            'subscription_plan' => $this->plan,
            'subscription_price' => $this->price,
            'is_paid' => $this->is_paid,
            'subscription_start_at' => $startDate,
            'subscription_expires_at' => $expiresAt,
            'subscription_active' => true,
        ]);

        // 2. Create History Record
        Subscription::create([
            'user_id' => $this->doctor->id,
            'plan_name' => $this->plan,
            'amount' => $this->price,
            'is_paid' => $this->is_paid,
            'start_date' => $startDate,
            'end_date' => $expiresAt,
            'paid_at' => $this->is_paid ? now() : null,
        ]);

        session()->flash('success', __('Subscription created successfully.'));
        $this->reset(['is_paid']); // Reset paid status for next entry
    }

    public function togglePaidStatus($subscriptionId)
    {
        $sub = Subscription::findOrFail($subscriptionId);
        $newStatus = !$sub->is_paid;
        
        $sub->update([
            'is_paid' => $newStatus,
            'paid_at' => $newStatus ? now() : null
        ]);

        // If this was the doctor's current/latest subscription, update their status too
        if ($this->doctor->subscription_expires_at && $sub->end_date->format('Y-m-d') === $this->doctor->subscription_expires_at->format('Y-m-d')) {
            $this->doctor->update(['is_paid' => $newStatus]);
        }

        session()->flash('success', __('Payment status updated.'));
    }

    public function deleteSubscription($subscriptionId)
    {
        $sub = Subscription::findOrFail($subscriptionId);
        $sub->delete();
        
        session()->flash('success', __('Subscription record deleted.'));
    }

    public function render()
    {
        $history = Subscription::where('user_id', $this->doctor->id)
            ->orderBy('start_date', 'desc')
            ->paginate(12);

        return view('livewire.admin.doctor-subscription-manager', [
            'history' => $history
        ])->layout('layouts.clinic');
    }
}
