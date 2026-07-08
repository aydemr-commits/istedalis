<?php

namespace App\Http\Controllers;

use App\Models\Dive;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffDashboardController extends Controller
{
    public function dashboard(Request $request): View
    {
        $divesQuery = $this->filteredDives($request);

        return view('staff.dashboard', [
            'staff' => $this->staff($request),
            'students' => Student::withCount('dives')->withSum('dives', 'duration_minutes')->orderBy('student_no')->get(),
            'dives' => $divesQuery->latest('dive_date')->paginate(20)->withQueryString(),
            'allStudents' => Student::orderBy('student_no')->get(),
            'selectedStudent' => $request->filled('report_student_id') ? Student::with('dives')->find($request->integer('report_student_id')) : null,
            'summary' => [
                'student_count' => Student::count(),
                'dive_count' => Dive::count(),
                'duration' => (int) Dive::sum('duration_minutes'),
                'recent' => Dive::with('student')->latest()->limit(5)->get(),
            ],
        ]);
    }

    private function filteredDives(Request $request): Builder
    {
        return Dive::with('student')
            ->when($request->filled('student_no'), fn (Builder $query) => $query->whereHas('student', fn (Builder $student) => $student->where('student_no', 'like', '%'.$request->student_no.'%')))
            ->when($request->filled('name'), fn (Builder $query) => $query->whereHas('student', function (Builder $student) use ($request) {
                foreach (preg_split('/\s+/', trim($request->name)) as $term) {
                    $student->where(function (Builder $inner) use ($term) {
                        $inner->where('name', 'like', '%'.$term.'%')
                            ->orWhere('surname', 'like', '%'.$term.'%');
                    });
                }
            }))
            ->when($request->filled('date_from'), fn (Builder $query) => $query->whereDate('dive_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn (Builder $query) => $query->whereDate('dive_date', '<=', $request->date_to))
            ->when($request->filled('location'), fn (Builder $query) => $query->where('location', 'like', '%'.$request->location.'%'))
            ->when($request->filled('dive_type'), fn (Builder $query) => $query->where('dive_type', 'like', '%'.$request->dive_type.'%'));
    }

    private function staff(Request $request): Staff
    {
        return Staff::findOrFail($request->session()->get('staff_id'));
    }
}
