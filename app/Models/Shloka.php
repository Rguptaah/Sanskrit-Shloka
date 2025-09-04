<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shloka extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'shloka_id',
        'sanskrit_shloka',
        'unicode',
        'transliteration',
        'translations',
        'source_text_name',
        'source_section',
        'source_chapter',
        'source_verse',
        'keywords',
        'category',
        'commentaries',
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
        'translations' => 'array',
        'keywords' => 'array',
        'commentaries' => 'array',
        'approved' => 'boolean',
        'approved_at' => 'datetime',
        'source_chapter' => 'integer',
        'source_verse' => 'integer',
    ];

    /**
     * Get the user who created this shloka
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this shloka
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get Q&A pairs for this shloka
     */
    public function qaPairs(): HasMany
    {
        return $this->hasMany(QAPair::class);
    }

    /**
     * Get approved Q&A pairs for this shloka
     */
    public function approvedQAPairs(): HasMany
    {
        return $this->hasMany(QAPair::class)->where('approved', true);
    }

    /**
     * Scope for approved shlokas
     */
    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    /**
     * Scope for pending approval shlokas
     */
    public function scopePending($query)
    {
        return $query->where('approved', false);
    }

    /**
     * Get the full source reference
     */
    public function getFullSourceAttribute(): string
    {
        return "{$this->source_text_name} {$this->source_section} {$this->source_chapter}.{$this->source_verse}";
    }

    /**
     * Get Hindi translation
     */
    public function getHindiTranslationAttribute(): ?string
    {
        return $this->translations['hindi'] ?? null;
    }

    /**
     * Get English translation
     */
    public function getEnglishTranslationAttribute(): ?string
    {
        return $this->translations['english'] ?? null;
    }

    /**
     * Check if shloka is approved
     */
    public function isApproved(): bool
    {
        return $this->approved === true;
    }

    /**
     * Approve the shloka
     */
    public function approve(User $user): bool
    {
        $this->approved = true;
        $this->approved_by = $user->id;
        $this->approved_at = now();
        return $this->save();
    }

    /**
     * Reject the shloka (unapprove)
     */
    public function reject(): bool
    {
        $this->approved = false;
        $this->approved_by = null;
        $this->approved_at = now();
        return $this->save();
    }

    /**
     * Get shloka data in export format
     */
    public function toExportArray(): array
    {
        return [
            'id' => $this->shloka_id,
            'sanskrit_shloka' => $this->sanskrit_shloka,
            'unicode' => $this->unicode,
            'transliteration' => $this->transliteration,
            'translations' => $this->translations,
            'metadata' => [
                'source' => [
                    'text_name' => $this->source_text_name,
                    'section' => $this->source_section,
                    'chapter' => $this->source_chapter,
                    'verse' => $this->source_verse,
                ],
                'keywords' => $this->keywords,
                'category' => $this->category,
                'commentaries' => $this->commentaries,
            ],
            'qa_pairs' => $this->approvedQAPairs->map(function ($qaPair) {
                return [
                    'question' => $qaPair->question,
                    'answer' => $qaPair->answer,
                    'keywords' => $qaPair->keywords,
                ];
            })->toArray(),
            'context' => $this->approvedQAPairs->pluck('context')->filter()->first(),
        ];
    }
}