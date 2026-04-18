<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SystemRevenue extends Component
{
    public $year;
    public $month;
    public $selectedPlan = 'all';

    public function mount()
    {
        $this->year = date('Y');
        $this->month = date('m');
    }

    public function getRevenueData()
    {
        $query = Subscription::query()
            ->whereYear('start_date', $this->year);

        if ($this->month !== 'all') {
            $query->whereMonth('start_date', $this->month);
        }

        if ($this->selectedPlan !== 'all') {
            $query->where('plan_name', $this->selectedPlan);
        }

        $subscriptions = $query->with('doctor')->orderBy('start_date', 'desc')->get();

        $stats = [
            'total_revenue' => $subscriptions->where('is_paid', true)->sum('amount'),
            'pending_revenue' => $subscriptions->where('is_paid', false)->sum('amount'),
            'total_count' => $subscriptions->count(),
            'paid_count' => $subscriptions->where('is_paid', true)->count(),
        ];

        // Monthly breakdown for chart
        $monthlyData = Subscription::whereYear('start_date', $this->year)
            ->where('is_paid', true)
            ->select(
                DB::raw('MONTH(start_date) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartData[] = $monthlyData[$i] ?? 0;
        }

        return [
            'subscriptions' => $subscriptions,
            'stats' => $stats,
            'chartData' => $chartData,
        ];
    }

    public function render()
    {
        return view('livewire.admin.system-revenue', $this->getRevenueData())
            ->layout('layouts.clinic');
    }
}
