<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A researcher's academic record. One row per user.
 *
 * Name, email and password stay on `users`; nothing here repeats them. The
 * completion figure is worked out from the groups below rather than from every
 * column, because a profile is useful once the important parts are filled in,
 * not only when every optional field has been answered.
 */
class ResearcherProfile extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date_of_birth' => 'date',
        'research_interests' => 'array',
        'research_fields' => 'array',
        'expertise' => 'array',
        'languages' => 'array',
    ];

    /**
     * The sections the dashboard checks off, and what counts as done.
     *
     * @return array<string, array<int, string>>
     */
    public const SECTIONS = [
        'basic' => ['gender', 'country', 'profession', 'designation', 'organization'],
        'contact' => ['phone', 'city', 'address'],
        'academic' => ['department', 'highest_degree', 'research_experience'],
        'interests' => ['research_interests', 'research_fields'],
        'expertise' => ['expertise', 'biography'],
        'links' => ['orcid', 'google_scholar', 'researchgate', 'linkedin'],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** A section counts as done once every field in it has something in it. */
    public function hasSection(string $section): bool
    {
        foreach (self::SECTIONS[$section] ?? [] as $field) {
            if (blank($this->{$field})) {
                return false;
            }
        }

        return true;
    }

    /** How far through the profile is, as a whole number of per cent. */
    public function completion(): int
    {
        $sections = array_keys(self::SECTIONS);

        if ($sections === []) {
            return 100;
        }

        $done = count(array_filter($sections, fn (string $s) => $this->hasSection($s)));

        return (int) round($done / count($sections) * 100);
    }

    /** @return array<string, bool> section => done */
    public function checklist(): array
    {
        return collect(array_keys(self::SECTIONS))
            ->mapWithKeys(fn (string $s) => [$s => $this->hasSection($s)])
            ->all();
    }
}
