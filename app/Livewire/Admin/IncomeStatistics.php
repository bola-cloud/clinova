<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.clinic')]
class IncomeStatistics extends Component
{
    public $dateFrom;
    public $dateTo;
    public $doctorId = null;

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
    }

    public function render()
    {
        $query = Appointment::with('doctor')
            ->where('status', 'seen')
            ->whereDate('scheduled_at', '>=', $this->dateFrom)
            ->whereDate('scheduled_at', '<=', $this->dateTo);

        if (auth()->user()->role === 'doctor') {
            $query->where('doctor_id', auth()->id());
        } elseif ($this->doctorId) {
            $query->where('doctor_id', $this->doctorId);
        }

        $appointments = $query->get();

        $totalIncome = 0;
        $checkupIncome = 0;
        $followupIncome = 0;
        $appointmentsCount = $appointments->count();

        foreach ($appointments as $appointment) {
            $doctor = $appointment->doctor;
            if ($appointment->type === 'checkup') {
                $fee = $doctor->consultation_fee ?? 0;
                $checkupIncome += $fee;
                $totalIncome += $fee;
            } elseif ($appointment->type === 'follow_up') {
                $fee = $doctor->followup_fee ?? 0;
                $followupIncome += $fee;
                $totalIncome += $fee;
            }
        }

        // Daily Breakdown Chart Data
        $dailyData = $appointments->groupBy(function($date) {
            return Carbon::parse($date->scheduled_at)->format('Y-m-d');
        })->map(function ($dayAppointments) {
            $dayIncome = 0;
            foreach ($dayAppointments as $app) {
                if ($app->type === 'checkup') {
                    $dayIncome += $app->doctor->consultation_fee ?? 0;
                } elseif ($app->type === 'follow_up') {
                    $dayIncome += $app->doctor->followup_fee ?? 0;
                }
            }
            return $dayIncome;
        });

        // Fill missing days with 0
        $chartData = [];
        $chartLabels = [];
        
        if ($this->dateFrom && $this->dateTo) {
            $start = Carbon::parse($this->dateFrom);
            $end = Carbon::parse($this->dateTo);
            
            // Limit to 31 days to avoid massive arrays if user selects a huge range
            if ($start->diffInDays($end) <= 60) {
                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                    $dateString = $date->format('Y-m-d');
                    $chartLabels[] = $date->format('M d');
                    $chartData[] = $dailyData->get($dateString, 0);
                }
            }
        }

        return view('livewire.admin.income-statistics', [
            'totalIncome' => $totalIncome,
            'checkupIncome' => $checkupIncome,
            'followupIncome' => $followupIncome,
            'appointmentsCount' => $appointmentsCount,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'doctors' => \App\Models\User::where('role', 'doctor')->get()
        ]);
    }
}
