<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QAPair extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'shloka_id',
        'question',
        'answer',
        'keywords',
        'context',
        'created_by',
        'approved',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'keywords' => 'array',
        'approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the shloka this Q&A pair belongs to
     */
    public function shloka(): BelongsTo
    {
        return $this->belongsTo(Shloka::class);
    }

    /**
     * Get the user who created this Q&A pair
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this Q&A pair
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope for approved Q&A pairs
     */
    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    /**
     * Scope for pending approval Q&A pairs
     */
    public function scopePending($query)
    {
        return $query->where('approved', false);
    }

    /**
     * Check if Q&A pair is approved
     */
    public function isApproved(): bool
    {
        return $this->approved === true;
    }

    /**
     * Approve the Q&A pair
     */
    public function approve(User $user): bool
    {
        $this->approved = true;
        $this->approved_by = $user->id;
        $this->approved_at = now();
        return $this->save();
    }

    /**
     * Reject the Q&A pair (unapprove)
     */
    public function reject(): bool
    {
        $this->approved = false;
        $this->approved_by = null;
        $this->approved_at = now();
        return $this->save();
    }

    /**
     * Get Q&A pair data in export format
     */
    public function toExportArray(): array
    {
        return [
            'question' => $this->question,
            'answer' => $this->answer,
            'keywords' => $this->keywords,
        ];
    }
}