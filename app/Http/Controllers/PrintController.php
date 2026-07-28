<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\PrescriptionSetting;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PrintController extends Controller
{
    public function prescription(Visit $visit)
    {
        $doctor = $visit->doctor;
        $patient = $visit->patient;
        
        $setting = PrescriptionSetting::where('doctor_id', $doctor->id)->first();
        
        if (!$setting || !$setting->background_image_path) {
            if (auth()->user()->isDoctor()) {
                return redirect()->route('doctor.prescription')->with('error', __('Please configure your prescription layout before printing.'));
            }
            abort(404, __('The doctor has not configured a prescription layout yet.'));
        }

        // Get the background URL
        $backgroundUrl = Storage::url($setting->background_image_path);
        
        // Ensure elements exist
        $elements = $setting->elements ?? [];

        return view('print.prescription', [
            'visit' => $visit,
            'patient' => $patient,
            'doctor' => $doctor,
            'backgroundUrl' => $backgroundUrl,
            'elements' => $elements,
        ]);
    }
}
