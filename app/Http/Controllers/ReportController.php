<?php

namespace App\Http\Controllers;

use App\Models\Dive;
use App\Models\Staff;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function studentReport(Student $student): Response
    {
        $dives = $student->dives()->orderBy('dive_date')->get();

        return $this->download($student, $dives, null);
    }

    public function selected(Request $request): Response
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'dive_ids' => ['required', 'array', 'min:1'],
            'dive_ids.*' => ['integer', 'exists:dives,id'],
        ]);

        $student = Student::findOrFail($data['student_id']);
        $dives = Dive::where('student_id', $student->id)
            ->whereIn('id', $data['dive_ids'])
            ->orderBy('dive_date')
            ->get();

        abort_if($dives->isEmpty(), 422, 'Secili dalis bulunamadi.');

        $staff = Staff::find($request->session()->get('staff_id'));

        return $this->download($student, $dives, $staff);
    }

    private function download(Student $student, $dives, ?Staff $staff): Response
    {
        $firstDive = $dives->first();
        $lastDive = $dives->last();

        $pdf = Pdf::loadView('pdf.dive-report', [
            'student' => $student,
            'dives' => $dives,
            'staff' => $staff,
            'createdAt' => now(),
            'dateRange' => $firstDive && $lastDive
                ? $firstDive->dive_date?->format('d.m.Y').' - '.$lastDive->dive_date?->format('d.m.Y')
                : '-',
        ])->setPaper('a4');

        return $pdf->download('dalis-raporu-'.$student->student_no.'.pdf');
    }
}
