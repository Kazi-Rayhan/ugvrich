<?php

namespace App\Models;

use App\Support\Vocabulary;
use Illuminate\Database\Eloquent\Model;

/** A student membership application. */
class StudentMembership extends Model
{
    protected $guarded = [];

    protected $casts = [
        'semester' => 'integer',
    ];

    /** Icon for each internship track, matching the keys in config/rich.php. */
    public const TRACK_ICONS = [
        'electrical' => 'bolt',
        'ict' => 'cpu',
        'infrastructure' => 'building',
        'mechanical' => 'wrench',
        'business' => 'chart',
        'language' => 'chat',
    ];

    /** MEM-00012: the reference the applicant was given. */
    public function getReferenceAttribute(): string
    {
        return 'MEM-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function getDepartmentNameAttribute(): ?string
    {
        return Vocabulary::label('departments', $this->department);
    }

    public function getTrackLabelAttribute(): ?string
    {
        return Vocabulary::label('internship_tracks', $this->track);
    }

    public function getSemesterLabelAttribute(): string
    {
        return __('site.membership.semester_option', ['n' => $this->semester]);
    }
}
