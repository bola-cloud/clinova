<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Visit;
use App\Models\PatientFile;

#[Layout('layouts.clinic')]
class SystemTrash extends Component
{
    use WithPagination;

    public $currentTab = 'patients';
    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCurrentTab()
    {
        $this->resetPage();
    }

    public function restoreRecord($type, $id)
    {
        $model = $this->getModelInstance($type, $id);
        if ($model) {
            $model->restore();
            session()->flash('success', __('Record restored successfully.'));
        }
    }

    public function forceDeleteRecord($type, $id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $model = $this->getModelInstance($type, $id);
        if ($model) {
            $model->forceDelete();
            session()->flash('success', __('Record permanently deleted.'));
        }
    }

    public function downloadBackup()
    {
        return redirect()->route('admin.backup');
    }

    private function getModelInstance($type, $id)
    {
        return match ($type) {
            'patient' => Patient::onlyTrashed()->find($id),
            'appointment' => Appointment::onlyTrashed()->find($id),
            'visit' => Visit::onlyTrashed()->find($id),
            'file' => PatientFile::onlyTrashed()->find($id),
            'doctor' => \App\Models\User::onlyTrashed()->where('role', 'doctor')->find($id),
            default => null,
        };
    }

    public function render()
    {
        $records = [];
        
        $queryScope = function($query) {
            if (auth()->user()->isDoctor()) {
                if (method_exists($query->getModel(), 'doctor_id')) {
                     // Patient model has doctor_id directly
                     $query->where('doctor_id', auth()->id());
                } else {
                     // Models like Appointment, Visit, PatientFile usually relate to patient
                     $query->whereHas('patient', function($q) {
                         $q->where('doctor_id', auth()->id());
                     });
                }
            }
        };

        $patientScope = function($query) {
            if (auth()->user()->isDoctor()) {
                $query->where('doctor_id', auth()->id());
            }
        };

        if ($this->currentTab === 'patients') {
            $records = Patient::onlyTrashed()
                ->where($patientScope)
                ->when($this->search, fn($q) => $q->where('name', 'like', '%'.$this->search.'%'))
                ->latest('deleted_at')
                ->paginate(15);
        } elseif ($this->currentTab === 'appointments') {
            $records = Appointment::onlyTrashed()->with(['patient' => fn($q) => $q->withTrashed()])
                ->whereHas('patient', $patientScope)
                ->when($this->search, fn($q) => $q->whereHas('patient', fn($pq) => $pq->where('name', 'like', '%'.$this->search.'%')))
                ->latest('deleted_at')
                ->paginate(15);
        } elseif ($this->currentTab === 'visits') {
            $records = Visit::onlyTrashed()->with(['patient' => fn($q) => $q->withTrashed()])
                ->whereHas('patient', $patientScope)
                ->when($this->search, fn($q) => $q->whereHas('patient', fn($pq) => $pq->where('name', 'like', '%'.$this->search.'%')))
                ->latest('deleted_at')
                ->paginate(15);
        } elseif ($this->currentTab === 'files') {
            $records = PatientFile::onlyTrashed()->with(['patient' => fn($q) => $q->withTrashed()])
                ->whereHas('patient', $patientScope)
                ->when($this->search, fn($q) => $q->where('file_name', 'like', '%'.$this->search.'%'))
                ->latest('deleted_at')
                ->paginate(15);
        } elseif ($this->currentTab === 'doctors' && auth()->user()->isAdmin()) {
            $records = \App\Models\User::onlyTrashed()->where('role', 'doctor')
                ->when($this->search, fn($q) => $q->where('name', 'like', '%'.$this->search.'%'))
                ->latest('deleted_at')
                ->paginate(15);
        }

        return view('livewire.admin.system-trash', [
            'records' => $records,
            'stats' => [
                'patients' => Patient::onlyTrashed()->where($patientScope)->count(),
                'appointments' => Appointment::onlyTrashed()->whereHas('patient', $patientScope)->count(),
                'visits' => Visit::onlyTrashed()->whereHas('patient', $patientScope)->count(),
                'files' => PatientFile::onlyTrashed()->whereHas('patient', $patientScope)->count(),
                'doctors' => auth()->user()->isAdmin() ? \App\Models\User::onlyTrashed()->where('role', 'doctor')->count() : 0,
            ]
        ])->layout('layouts.clinic', ['title' => __('System Trash')]);
    }
}
