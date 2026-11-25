<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsultancyApplication extends Model
{
    use SoftDeletes;

    public const WING = 'wing_review';
    public const DIRECTOR = 'director_review';
    public const APPROVED = 'approved';
    public const DECLINED = 'declined';

    public const AFFILIATIONS = ['internal', 'external'];

    public const REVIEW_STAGES = [
        self::WING => 'Consultancy Wing',
        self::DIRECTOR => 'Director',
    ];

    public const CONTRACTS = [
        'long_term' => 'Long-term contract',
        'freelancer' => 'Freelancer (short-term contract)',
    ];

    public function canWorkOnOrders(): bool
    {
        return $this->signed_at !== null || $this->orderAssignments()->exists();
    }

    public function scopeAssignableToOrders(Builder $query): Builder
    {
        return $query->where('status', self::APPROVED)
            ->where(fn (Builder $query) => $query->whereNull('contract_type')->orWhereNotNull('signed_at'));
    }

    protected static function booted(): void
    {
        static::deleted(function (self $application): void {
            $application->expert()->update(['is_active' => false]);
        });
    }

    public static function contractLabel(string $contract, ?string $locale = null): string
    {
        return __('consultancy_engagement.contracts.'.$contract, locale: $locale);
    }

    public static function contractOptions(): array
    {
        return array_map(self::contractLabel(...), array_combine(array_keys(self::CONTRACTS), array_keys(self::CONTRACTS)));
    }

    public static function localizedTerm(array $content, string $key): ?string
    {
        $english = $content[$key] ?? null;
        $bangla = $content[$key.'_bn'] ?? null;

        return app()->isLocale('bn') ? ($bangla ?: $english) : ($english ?: $bangla);
    }

    public function engagementReviewItems(): array
    {
        $policy = $this->engagement_policy ?? [];
        $items = [];

        if ($this->engagement_percentage !== null) {
            $items['percentage'] = [
                'title' => __('consultancy_engagement.percentage'),
                'description' => $this->engagement_percentage.'%',
            ];
        }

        foreach (array_values($policy['sections'] ?? []) as $index => $section) {
            $items['section_'.$index] = [
                'title' => self::localizedTerm($section, 'title'),
                'description' => self::localizedTerm($section, 'description'),
            ];
        }

        foreach (['privacy_notice' => 'privacy', 'participation_policy' => 'participation'] as $key => $label) {
            if ($description = self::localizedTerm($policy, $key)) {
                $items[$label] = ['title' => __('consultancy_engagement.'.$label), 'description' => $description];
            }
        }

        return $items;
    }

    public const STATUSES = [
        self::WING => 'Pending Consultancy Wing review',
        self::DIRECTOR => 'Pending Director review',
        self::APPROVED => 'Approved',
        self::DECLINED => 'Declined',
    ];

    protected $guarded = [];

    protected $hidden = ['wallet_destinations'];

    protected $casts = [
        'education' => 'array',
        'occupations' => 'array',
        'experience' => 'array',
        'skills' => 'array',
        'decisions' => 'array',
        'engagement_policy' => 'array',
        'engagement_assigned_at' => 'datetime',
        'engagement_percentage' => 'decimal:2',
        'engagement_term_years' => 'integer',
        'signed_at' => 'datetime',
        'wallet_destinations' => 'encrypted:array',
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }

    public function expertiseServices()
    {
        return $this->belongsToMany(Service::class, 'consultancy_application_expertise')->orderBy('services.name');
    }

    public function portfolios()
    {
        return $this->hasMany(ExpertPortfolio::class);
    }

    public function walletEntries()
    {
        return $this->hasMany(ExpertWalletEntry::class);
    }

    public function orderAssignments()
    {
        return $this->hasMany(OrderExpertAssignment::class);
    }

    public function engagementAssigner()
    {
        return $this->belongsTo(User::class, 'engagement_assigned_by');
    }

    public function canReview(User $user): bool
    {
        return match ($this->status) {
            self::WING => $user->consultancyReviewStages()->where('key', self::WING)->exists(),
            self::DIRECTOR => (int) data_get($this->decisions, '0.user_id') !== $user->id
                && $user->consultancyReviewStages()->where('key', self::DIRECTOR)->exists(),
            default => false,
        };
    }
}
