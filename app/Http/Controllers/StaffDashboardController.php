<?php

namespace App\Http\Controllers;

use App\Models\Dive;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffDashboardController extends Controller
{
    public function dashboard(Request $request): View
    {
        $divesQuery = $this->filteredDives($request);

        return view('staff.dashboard', [
            'staff' => $staff = $this->staff($request),
            'students' => Student::where('approval_status', 'approved')->withCount('dives')->withSum('dives', 'duration_minutes')->orderBy('student_no')->get(),
            'dives' => $divesQuery->latest('dive_date')->paginate(20)->withQueryString(),
            'allStudents' => Student::where('approval_status', 'approved')->orderBy('student_no')->get(),
            'selectedStudent' => $request->filled('report_student_id') ? Student::where('approval_status', 'approved')->with('dives')->find($request->integer('report_student_id')) : null,
            'pendingStudents' => $staff->isAdmin() ? Student::where('approval_status', 'pending')->orderBy('created_at')->get() : collect(),
            'pendingStaff' => $staff->isAdmin() ? Staff::where('approval_status', 'pending')->where('role_name', 'staff')->orderBy('created_at')->get() : collect(),
            'summary' => [
                'student_count' => Student::where('approval_status', 'approved')->count(),
                'dive_count' => Dive::count(),
                'duration' => (int) Dive::sum('duration_minutes'),
                'recent' => Dive::with('student')->latest()->limit(5)->get(),
            ],
        ]);
    }

    public function approveStudent(Request $request, Student $student): RedirectResponse
    {
        $admin = $this->staff($request);
        abort_unless($admin->isAdmin(), 403);

        $student->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by_staff_id' => $admin->id,
        ]);

        return back()->with('status', 'Ogrenci kaydi onaylandi.');
    }

    public function destroyStudent(Request $request, Student $student): RedirectResponse
    {
        abort_unless($this->staff($request)->isAdmin(), 403);

        $student->delete();

        return back()->with('status', 'Ogrenci sistemden cikarildi.');
    }

    public function approveStaff(Request $request, Staff $staffMember): RedirectResponse
    {
        $admin = $this->staff($request);
        abort_unless($admin->isAdmin(), 403);

        $staffMember->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by_staff_id' => $admin->id,
        ]);

        return back()->with('status', 'Dalis amiri kaydi onaylandi.');
    }

    private function filteredDives(Request $request): Builder
    {
        return Dive::with('student')
            ->whereHas('student', fn (Builder $student) => $student->where('approval_status', 'approved'))
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
