<?php

namespace App\Services;

use App\Models\ConsultancyApplication as Application;
use App\Models\ConsultancyEngagementPolicy;
use App\Models\User;
use App\Notifications\ConsultancyUpdate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ConsultancyEngagement
{
    public static function canAssign(User $user): bool
    {
        return $user->can('assignEngagement', Application::class);
    }

    public const PERCENTAGE_RULES = ['required', 'numeric', 'between:0,100', 'decimal:0,2'];

    public const MAX_TERM_YEARS = 4294967295;

    public function assign(Application $record, User $assigner, string $contract, ?string $percentage, mixed $termYears): void
    {
        abort_unless(self::canAssign($assigner), 403);
        $data = validator(['contract' => $contract, 'percentage' => $percentage, 'term_years' => $termYears], [
            'contract' => ['required', Rule::in(['long_term'])],
            'percentage' => self::PERCENTAGE_RULES,
            'term_years' => ['required', 'integer', 'min:1', 'max:'.self::MAX_TERM_YEARS],
        ])->validate();
        $termYears = (int) $data['term_years'];

        DB::transaction(function () use ($record, $assigner, $contract, $percentage, $termYears): void {
            $application = Application::query()->lockForUpdate()->findOrFail($record->id);
            if ($application->status !== Application::APPROVED || $application->contract_type !== null || $application->signed_at) {
                throw ValidationException::withMessages(['contract' => __('consultancy_engagement.only_pending')]);
            }

            $policy = ConsultancyEngagementPolicy::where('contract_type', $contract)->sole();
            $terms = $policy->only(['version', ...ConsultancyEngagementPolicy::CONTENT_FIELDS]);
            $rate = number_format((float) $percentage, 2, '.', '').'%';
            foreach ($terms['sections'] ?? [] as $index => $section) {
                $isTermSection = preg_match('/^\s*(?:12|১২)(?=\D|$)|term,\s*renewal,?\s*and\s*termination/iu', $section['title'] ?? '')
                    || preg_match('/^\s*(?:12|১২)(?=\D|$)/u', $section['title_bn'] ?? '');

                foreach (['description', 'description_bn'] as $field) {
                    $description = $section[$field] ?? null;
                    if (filled($description)) {
                        $description = preg_replace(
                            '/:percentage\b(?:\h*%)?|(?:_{2,}|\.{3,}|…|\[\h*\])\h*%/u',
                            $rate,
                            $description,
                        );
                        if ($isTermSection) {
                            $description = preg_replace_callback(
                                '/(?:\(\h*_{2,}\h*\)|_{2,}|:term_years\b)\h+(years?\b|বছর)/iu',
                                fn (array $match) => $termYears.' '.(mb_strtolower($match[1]) === 'বছর' ? $match[1] : str('year')->plural($termYears)),
                                $description,
                            );
                        }
                        $terms['sections'][$index][$field] = str_replace(':term_years', (string) $termYears, $description);
                    }
                }
            }
            $application->update([
                'contract_type' => $contract,
                'engagement_policy' => $terms,
                'engagement_percentage' => $percentage,
                'engagement_term_years' => $termYears,
                'engagement_assigned_at' => now(),
                'engagement_assigned_by' => $assigner->id,
            ]);

            Notification::route('mail', $application->email)->notify(new ConsultancyUpdate(
                __('consultancy_engagement.notification_subject', locale: 'en').' / '.__('consultancy_engagement.notification_subject', locale: 'bn'),
                __('consultancy_engagement.notification_body', ['contract' => Application::contractLabel($contract, 'en')], 'en')
                    ."\n\n".__('consultancy_engagement.notification_body', ['contract' => Application::contractLabel($contract, 'bn')], 'bn'),
                route('expert.dashboard'),
                __('consultancy_engagement.notification_link', locale: 'en').' / '.__('consultancy_engagement.notification_link', locale: 'bn'),
            ));
        });
    }
}
