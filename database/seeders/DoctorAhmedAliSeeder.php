<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\Appointment;
use Carbon\Carbon;

class DoctorAhmedAliSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctorId = 5; // Dr. Ahmed Ali
        $today = Carbon::today();

        $patients = [
            ['name' => 'محمد محمود علي', 'phone' => '01012345678'],
            ['name' => 'سارة أحمد حسن', 'phone' => '01123456789'],
            ['name' => 'إبراهيم كمال محمد', 'phone' => '01234567890'],
            ['name' => 'منى السيد يوسف', 'phone' => '01545678901'],
            ['name' => 'خالد عبد الرحمن', 'phone' => '01098765432'],
        ];

        foreach ($patients as $index => $data) {
            $patient = Patient::create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'doctor_id' => $doctorId,
                'age_years' => rand(20, 60),
                'address' => 'القاهرة، مصر',
            ]);

            // Create appointment for today
            Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctorId,
                'scheduled_at' => $today->copy()->setHour(rand(9, 21))->setMinute(0),
                'status' => 'pending',
                'type' => $index % 2 == 0 ? 'checkup' : 'follow_up',
                'queue_order' => $index + 3, // Assuming there are already some appointments
            ]);
        }
    }
}
