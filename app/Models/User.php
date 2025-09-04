<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * Role constants
     */
    const ROLE_ADMIN = 'admin';
    const ROLE_FIXED_DATA_ENTRY = 'fixed_data_entry';
    const ROLE_VARIABLE_DATA_ENTRY = 'variable_data_entry';
    const ROLE_APPROVER = 'approver';

    /**
     * Check if user has admin role
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is fixed data entry user
     */
    public function isFixedDataEntry(): bool
    {
        return $this->role === self::ROLE_FIXED_DATA_ENTRY;
    }

    /**
     * Check if user is variable data entry user
     */
    public function isVariableDataEntry(): bool
    {
        return $this->role === self::ROLE_VARIABLE_DATA_ENTRY;
    }

    /**
     * Check if user is approver
     */
    public function isApprover(): bool
    {
        return $this->role === self::ROLE_APPROVER;
    }

    /**
     * Check if user can add fixed data (shlokas)
     */
    public function canAddFixedData(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_FIXED_DATA_ENTRY]);
    }

    /**
     * Check if user can add variable data (Q&A pairs)
     */
    public function canAddVariableData(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN, 
            self::ROLE_VARIABLE_DATA_ENTRY,
            self::ROLE_FIXED_DATA_ENTRY
        ]);
    }

    /**
     * Check if user can approve data
     */
    public function canApprove(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_APPROVER]);
    }

    /**
     * Get shlokas created by this user
     */
    public function shlokas()
    {
        return $this->hasMany(Shloka::class, 'created_by');
    }

    /**
     * Get Q&A pairs created by this user
     */
    public function qaPairs()
    {
        return $this->hasMany(QAPair::class, 'created_by');
    }

    /**
     * Get approved shlokas by this user
     */
    public function approvedShlokas()
    {
        return $this->hasMany(Shloka::class, 'approved_by');
    }

    /**
     * Get approved Q&A pairs by this user
     */
    public function approvedQAPairs()
    {
        return $this->hasMany(QAPair::class, 'approved_by');
    }

    /**
     * Get all available roles
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_FIXED_DATA_ENTRY => 'Fixed Data Entry User',
            self::ROLE_VARIABLE_DATA_ENTRY => 'Variable Data Entry User',
            self::ROLE_APPROVER => 'Approver',
        ];
    }

    /**
     * Get role display name
     */
    public function getRoleDisplayName(): string
    {
        $roles = self::getRoles();
        return $roles[$this->role] ?? $this->role;
    }
}