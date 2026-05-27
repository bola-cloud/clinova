<?php

use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use Livewire\Livewire;
use App\Livewire\Admin\IncomeStatistics;

test('it calculates custom fee revenue correctly and displays it', function () {
    // 1. Create a doctor with custom fees
    $doctor = User::factory()->create([
        'role' => 'doctor',
        'consultation_fee' => 100.00,
        'followup_fee' => 50.00,
        'custom_fees' => [
            [
                'id' => 'custom_incubator',
                'name' => 'Incubator Fee',
                'fee' => 150.00,
            ],
            [
                'id' => 'custom_unused',
                'name' => 'Unused Fee',
                'fee' => 200.00,
            ],
        ],
    ]);

    // 2. Create a patient
    $patient = Patient::factory()->create([
        'doctor_id' => $doctor->id,
    ]);

    // 3. Create completed appointments
    // One checkup (100.00)
    Appointment::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'type' => 'checkup',
        'status' => 'seen',
        'scheduled_at' => now(),
    ]);

    // Two Incubator visits (150.00 each = 300.00)
    Appointment::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'type' => 'custom_incubator',
        'status' => 'seen',
        'scheduled_at' => now(),
    ]);

    Appointment::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'type' => 'custom_incubator',
        'status' => 'seen',
        'scheduled_at' => now(),
    ]);

    // 4. Assert correct calculations in Livewire component
    Livewire::actingAs($doctor)
        ->test(IncomeStatistics::class)
        ->assertViewHas('totalIncome', 400.00)
        ->assertViewHas('checkupIncome', 100.00)
        ->assertViewHas('followupIncome', 0.00)
        ->assertViewHas('customIncomes', function ($customIncomes) {
            return isset($customIncomes['custom_incubator']) 
                && $customIncomes['custom_incubator']['name'] === 'Incubator Fee'
                && $customIncomes['custom_incubator']['income'] == 300.00
                && isset($customIncomes['custom_unused'])
                && $customIncomes['custom_unused']['name'] === 'Unused Fee'
                && $customIncomes['custom_unused']['income'] == 0.00;
        })
        ->assertSee('Incubator Fee')
        ->assertSee('300.00')
        ->assertSee('Unused Fee')
        ->assertSee('0.00');
});
