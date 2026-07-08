<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dive extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'dive_date',
        'location',
        'dive_type',
        'purpose',
        'start_time',
        'end_time',
        'duration_minutes',
        'max_depth',
        'water_temperature',
        'visibility',
        'weather',
        'equipment',
        'start_pressure',
        'end_pressure',
        'supervisor_name',
        'notes',
        'created_by_student_id',
        'approved_by_staff_id',
    ];

    protected $casts = [
        'dive_date' => 'date',
        'duration_minutes' => 'integer',
        'max_depth' => 'decimal:2',
        'water_temperature' => 'decimal:1',
        'visibility' => 'decimal:1',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function creatorStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'created_by_student_id');
    }

    public function approverStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'approved_by_staff_id');
    }
}
