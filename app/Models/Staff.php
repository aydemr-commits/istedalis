<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';

    protected $fillable = [
        'staff_no',
        'password',
        'name',
        'surname',
        'email',
        'role_name',
        'approval_status',
        'approved_at',
        'approved_by_staff_id',
    ];

    protected $hidden = ['password'];

    public function approvedDives(): HasMany
    {
        return $this->hasMany(Dive::class, 'approved_by_staff_id');
    }

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = str_starts_with($value, '$2y$') ? $value : Hash::make($value);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->name.' '.$this->surname);
    }

    public function isAdmin(): bool
    {
        return $this->role_name === 'admin';
    }

    public function isApproved(): bool
    {
        return $this->isAdmin() || $this->approval_status === 'approved';
    }
}
