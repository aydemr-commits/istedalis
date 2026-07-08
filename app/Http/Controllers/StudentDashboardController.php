<?php

namespace App\Http\Controllers;

use App\Models\Dive;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function dashboard(Request $request): View
    {
        $student = $this->student($request);
        $dives = $student->dives()->latest('dive_date')->get();

        return view('student.dashboard', [
            'student' => $student,
            'dives' => $dives,
            'stats' => $this->stats($student),
        ]);
    }

    public function create(Request $request): View
    {
        return view('student.dives.form', [
            'student' => $this->student($request),
            'dive' => new Dive(),
            'action' => route('student.dives.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $student = $this->student($request);
        $data = $this->validatedDive($request);
        $data['student_id'] = $student->id;
        $data['created_by_student_id'] = $student->id;
        Dive::create($data);

        return redirect()->route('student.dashboard')->with('status', 'Dalis kaydi olusturuldu.');
    }

    public function edit(Request $request, Dive $dive): View
    {
        $student = $this->student($request);
        abort_unless($dive->student_id === $student->id, 403);

        return view('student.dives.form', [
            'student' => $student,
            'dive' => $dive,
            'action' => route('student.dives.update', $dive),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, Dive $dive): RedirectResponse
    {
        $student = $this->student($request);
        abort_unless($dive->student_id === $student->id, 403);

        $dive->update($this->validatedDive($request));

        return redirect()->route('student.dashboard')->with('status', 'Dalis kaydi guncellendi.');
    }

    private function student(Request $request): Student
    {
        return Student::findOrFail($request->session()->get('student_id'));
    }

    private function stats(Student $student): array
    {
        $query = $student->dives();

        return [
            'count' => (clone $query)->count(),
            'duration' => (int) (clone $query)->sum('duration_minutes'),
            'max_depth' => (clone $query)->max('max_depth') ?? 0,
            'last_date' => optional((clone $query)->latest('dive_date')->first())->dive_date,
        ];
    }

    private function validatedDive(Request $request): array
    {
        return $request->validate([
            'dive_date' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'dive_type' => ['required', 'string', 'max:255'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'max_depth' => ['nullable', 'numeric', 'min:0'],
            'water_temperature' => ['nullable', 'numeric'],
            'visibility' => ['nullable', 'numeric', 'min:0'],
            'weather' => ['nullable', 'string', 'max:255'],
            'equipment' => ['nullable', 'string', 'max:255'],
            'start_pressure' => ['nullable', 'integer', 'min:0'],
            'end_pressure' => ['nullable', 'integer', 'min:0'],
            'supervisor_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
