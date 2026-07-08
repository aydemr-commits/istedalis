<?php

namespace App\Http\Controllers;

use App\Models\Dive;
use App\Models\Staff;
use App\Models\Student;
use App\Services\BackupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function index(BackupService $backups): View
    {
        return view('staff.backup', [
            'backups' => $backups->listJsonBackups(),
            'retentionDays' => config('backup.retention_days', 14),
        ]);
    }

    public function json(BackupService $backups): JsonResponse
    {
        $filename = 'istedalis-yedek-'.now()->format('Ymd-His').'.json';

        return response()->json($backups->payload(), 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ->withHeaders($this->downloadHeaders('application/json', $filename));
    }

    public function create(Request $request, BackupService $backups): RedirectResponse
    {
        $backup = $backups->createJsonFile('manual');
        $deleted = $backups->deleteExpiredBackups();

        return back()->with('status', "Yedek olusturuldu: {$backup['filename']}. Temizlenen eski yedek: {$deleted}.");
    }

    public function runAutomatic(Request $request, BackupService $backups): JsonResponse
    {
        $rateLimitKey = 'automatic-backup|'.$request->ip();

        abort_if(RateLimiter::tooManyAttempts($rateLimitKey, 12), 429);
        RateLimiter::hit($rateLimitKey, 60);

        $expectedToken = (string) config('backup.automatic_token');
        $providedToken = (string) $request->header('X-Backup-Token');

        abort_if($expectedToken === '' || ! hash_equals($expectedToken, $providedToken), 403);

        $backup = $backups->createJsonFile('auto');
        $deleted = $backups->deleteExpiredBackups();

        return response()->json([
            'ok' => true,
            'backup' => $backup,
            'deleted_expired_backups' => $deleted,
        ])->withHeaders(['Cache-Control' => 'no-store']);
    }

    public function downloadStored(string $filename, BackupService $backups): BinaryFileResponse
    {
        return response()->download($backups->absolutePathForDownload($filename), $filename, [
            'Cache-Control' => 'no-store',
        ]);
    }

    public function allCsv(): Response
    {
        $rows = [['type', 'id', 'identifier', 'name', 'date', 'location', 'duration_minutes', 'max_depth']];

        foreach (Student::all() as $student) {
            $rows[] = ['student', $student->id, $student->student_no, $student->full_name, '', '', '', ''];
        }

        foreach (Staff::all() as $staff) {
            $rows[] = ['staff', $staff->id, $staff->staff_no, $staff->full_name, '', '', '', ''];
        }

        foreach (Dive::with('student')->get() as $dive) {
            $rows[] = ['dive', $dive->id, $dive->student?->student_no, $dive->student?->full_name, $dive->dive_date?->format('Y-m-d'), $dive->location, $dive->duration_minutes, $dive->max_depth];
        }

        return $this->csv('tum-veriler.csv', $rows);
    }

    public function studentsCsv(): Response
    {
        $rows = [['student_no', 'name', 'surname', 'program', 'class_name', 'phone', 'email']];

        foreach (Student::orderBy('student_no')->get() as $student) {
            $rows[] = [$student->student_no, $student->name, $student->surname, $student->program, $student->class_name, $student->phone, $student->email];
        }

        return $this->csv('ogrenci-listesi.csv', $rows);
    }

    public function divesCsv(): Response
    {
        $rows = [['student_no', 'name', 'dive_date', 'location', 'dive_type', 'purpose', 'duration_minutes', 'max_depth', 'supervisor_name', 'notes']];

        foreach (Dive::with('student')->orderBy('dive_date')->get() as $dive) {
            $rows[] = [
                $dive->student?->student_no,
                $dive->student?->full_name,
                $dive->dive_date?->format('Y-m-d'),
                $dive->location,
                $dive->dive_type,
                $dive->purpose,
                $dive->duration_minutes,
                $dive->max_depth,
                $dive->supervisor_name,
                $dive->notes,
            ];
        }

        return $this->csv('dalis-kayitlari.csv', $rows);
    }

    private function csv(string $filename, array $rows): Response
    {
        $stream = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        return response($content, 200, $this->downloadHeaders('text/csv; charset=UTF-8', $filename));
    }

    private function downloadHeaders(string $contentType, string $filename): array
    {
        return [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store',
        ];
    }
}
