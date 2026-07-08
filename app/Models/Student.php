<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_no',
        'password',
        'name',
        'surname',
        'program',
        'class_name',
        'phone',
        'email',
        'approval_status',
        'approved_at',
        'approved_by_staff_id',
    ];

    protected $hidden = ['password'];

    public function dives(): HasMany
    {
        return $this->hasMany(Dive::class);
    }

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = str_starts_with($value, '$2y$') ? $value : Hash::make($value);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->name.' '.$this->surname);
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }
}
