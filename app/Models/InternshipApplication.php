<?php

namespace App\Models;

use App\Support\Vocabulary;
use Illuminate\Database\Eloquent\Model;

/** An application for a RICH internship, from the public /internship form. */
class InternshipApplication extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'duration_months' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    /** How an application moves through the office. */
    public const STATUSES = [
        'new' => 'New',
        'shortlisted' => 'Shortlisted',
        'interview' => 'Interview',
        'accepted' => 'Accepted',
        'declined' => 'Declined',
    ];

    /** INT-00012: the reference the applicant was given. */
    public function getReferenceAttribute(): string
    {
        return 'INT-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function getTrackLabelAttribute(): ?string
    {
        return Vocabulary::label('internship_tracks', $this->track);
    }

    public function getModeLabelAttribute(): ?string
    {
        return Vocabulary::label('internship_modes', $this->mode);
    }

    public function getYearLabelAttribute(): ?string
    {
        return Vocabulary::label('internship_years', $this->year_level);
    }
}
