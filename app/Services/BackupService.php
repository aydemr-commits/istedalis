<?php

namespace App\Services;

use App\Models\Dive;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BackupService
{
    public function payload(): array
    {
        return [
            'application' => 'istedalis',
            'exported_at' => now()->toIso8601String(),
            'students' => Student::with(['dives' => fn ($query) => $query->orderBy('dive_date')])
                ->orderBy('student_no')
                ->get()
                ->map(fn (Student $student) => $this->studentPayload($student))
                ->values(),
            'staff' => Staff::orderBy('staff_no')
                ->get()
                ->map(fn (Staff $staff) => [
                    'id' => $staff->id,
                    'staff_no' => $staff->staff_no,
                    'name' => $staff->name,
                    'surname' => $staff->surname,
                    'full_name' => $staff->full_name,
                    'role_name' => $staff->role_name,
                    'created_at' => optional($staff->created_at)->toIso8601String(),
                    'updated_at' => optional($staff->updated_at)->toIso8601String(),
                ])
                ->values(),
            'dives' => Dive::with(['student', 'approverStaff'])
                ->orderBy('dive_date')
                ->orderBy('id')
                ->get()
                ->map(fn (Dive $dive) => $this->divePayload($dive))
                ->values(),
        ];
    }

    public function createJsonFile(string $type = 'manual'): array
    {
        $this->ensureBackupDirectory();

        $type = Str::slug($type) ?: 'manual';
        $filename = 'istedalis-'.$type.'-'.now()->format('Ymd-His').'.json';
        $relativePath = $this->backupPath().'/'.$filename;
        $json = json_encode($this->payload(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        Storage::disk($this->disk())->put($relativePath, $json);

        return [
            'filename' => $filename,
            'path' => $relativePath,
            'size' => Storage::disk($this->disk())->size($relativePath),
            'checksum' => hash('sha256', $json),
            'created_at' => now()->toIso8601String(),
        ];
    }

    public function deleteExpiredBackups(): int
    {
        $this->ensureBackupDirectory();

        $retentionDays = max(1, (int) config('backup.retention_days', 14));
        $deleteBefore = now()->subDays($retentionDays)->getTimestamp();
        $deleted = 0;

        foreach (Storage::disk($this->disk())->files($this->backupPath()) as $path) {
            if (! str_ends_with($path, '.json')) {
                continue;
            }

            if (Storage::disk($this->disk())->lastModified($path) < $deleteBefore) {
                Storage::disk($this->disk())->delete($path);
                $deleted++;
            }
        }

        return $deleted;
    }

    public function listJsonBackups(): array
    {
        $this->ensureBackupDirectory();

        $backups = [];

        foreach (Storage::disk($this->disk())->files($this->backupPath()) as $path) {
            if (! str_ends_with($path, '.json')) {
                continue;
            }

            $backups[] = [
                'filename' => basename($path),
                'size' => Storage::disk($this->disk())->size($path),
                'created_at' => Carbon::createFromTimestamp(Storage::disk($this->disk())->lastModified($path)),
            ];
        }

        usort($backups, fn (array $a, array $b) => $b['created_at']->timestamp <=> $a['created_at']->timestamp);

        return $backups;
    }

    public function absolutePathForDownload(string $filename): string
    {
        if (! preg_match('/\Aistedalis-[a-z0-9-]+-\d{8}-\d{6}\.json\z/i', $filename)) {
            throw new NotFoundHttpException();
        }

        $relativePath = $this->backupPath().'/'.$filename;

        if (! Storage::disk($this->disk())->exists($relativePath)) {
            throw new NotFoundHttpException();
        }

        return storage_path('app/private/'.$relativePath);
    }

    private function studentPayload(Student $student): array
    {
        return [
            'id' => $student->id,
            'student_no' => $student->student_no,
            'name' => $student->name,
            'surname' => $student->surname,
            'full_name' => $student->full_name,
            'program' => $student->program,
            'class_name' => $student->class_name,
            'phone' => $student->phone,
            'email' => $student->email,
            'created_at' => optional($student->created_at)->toIso8601String(),
            'updated_at' => optional($student->updated_at)->toIso8601String(),
            'dives' => $student->dives->map(fn (Dive $dive) => $this->divePayload($dive))->values(),
        ];
    }

    private function divePayload(Dive $dive): array
    {
        return [
            'id' => $dive->id,
            'student_id' => $dive->student_id,
            'student_no' => $dive->student?->student_no,
            'student_full_name' => $dive->student?->full_name,
            'dive_date' => optional($dive->dive_date)->format('Y-m-d'),
            'location' => $dive->location,
            'dive_type' => $dive->dive_type,
            'purpose' => $dive->purpose,
            'start_time' => $dive->start_time,
            'end_time' => $dive->end_time,
            'duration_minutes' => $dive->duration_minutes,
            'max_depth' => $dive->max_depth,
            'water_temperature' => $dive->water_temperature,
            'visibility' => $dive->visibility,
            'weather' => $dive->weather,
            'equipment' => $dive->equipment,
            'start_pressure' => $dive->start_pressure,
            'end_pressure' => $dive->end_pressure,
            'supervisor_name' => $dive->supervisor_name,
            'notes' => $dive->notes,
            'created_by_student_id' => $dive->created_by_student_id,
            'approved_by_staff_id' => $dive->approved_by_staff_id,
            'approved_by_staff_no' => $dive->approverStaff?->staff_no,
            'approved_by_staff_name' => $dive->approverStaff?->full_name,
            'created_at' => optional($dive->created_at)->toIso8601String(),
            'updated_at' => optional($dive->updated_at)->toIso8601String(),
        ];
    }

    private function disk(): string
    {
        return config('backup.disk', 'local');
    }

    private function backupPath(): string
    {
        return trim(config('backup.path', 'backups'), '/');
    }

    private function ensureBackupDirectory(): void
    {
        Storage::disk($this->disk())->makeDirectory($this->backupPath());
    }
}
