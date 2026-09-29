<?php

namespace App\Support;

use App\Models\ConsultancyRequest;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * When the RICH office takes consultancy meetings.
 *
 * The opening hours, the length of a slot and the closed days are kept in the
 * site settings, so the office can change them without a deploy. One place
 * decides it, so the form, the validation and the admin panel cannot drift
 * apart. A slot someone has already been given is not offered again.
 */
class MeetingSlots
{
    /** Used when a setting has never been saved. */
    public const DEFAULT_OPENS = '09:00';

    public const DEFAULT_CLOSES = '20:00';

    public const DEFAULT_MINUTES = 30;

    /** The office breaks for lunch between these. */
    public const DEFAULT_BREAK_STARTS = '13:00';

    public const DEFAULT_BREAK_ENDS = '15:00';

    /** Thursday and Friday, as Carbon and JavaScript both count them (0 = Sunday). */
    public const DEFAULT_CLOSED_DAYS = [CarbonInterface::THURSDAY, CarbonInterface::FRIDAY];

    /** A request in one of these states has given its slot back. */
    public const RELEASED_STATUSES = ['declined', 'closed'];

    public static function opens(): string
    {
        return static::time('booking_opens', self::DEFAULT_OPENS);
    }

    public static function closes(): string
    {
        return static::time('booking_closes', self::DEFAULT_CLOSES);
    }

    /** A stored time, only if it reads as one. */
    protected static function time(string $key, string $fallback): string
    {
        $value = (string) app(Site::class)->get($key, $fallback);

        return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value) ? $value : $fallback;
    }

    /** The lunch break, as ['13:00', '15:00'], or null when the office keeps none. */
    public static function breakWindow(): ?array
    {
        $starts = static::time('booking_break_starts', '');
        $ends = static::time('booking_break_ends', '');

        return ($starts && $ends && $starts < $ends) ? [$starts, $ends] : null;
    }

    public static function minutes(): int
    {
        $minutes = (int) app(Site::class)->get('booking_slot_minutes', self::DEFAULT_MINUTES);

        return $minutes > 0 ? $minutes : self::DEFAULT_MINUTES;
    }

    /** @return array<int, int> */
    public static function closedDays(): array
    {
        $days = app(Site::class)->list('booking_closed_days');

        return $days === [] ? self::DEFAULT_CLOSED_DAYS : array_map('intval', $days);
    }

    /**
     * Every slot in a day, as `09:00-09:30` => `09:00 – 09:30`.
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        $slots = [];

        $start = CarbonImmutable::createFromFormat('H:i', static::opens());
        $closes = CarbonImmutable::createFromFormat('H:i', static::closes());
        $minutes = static::minutes();
        $break = static::breakWindow();

        // A closing time at or before opening would loop forever.
        if (! $start->lessThan($closes)) {
            return [];
        }

        while ($start->lessThan($closes)) {
            $end = $start->addMinutes($minutes);

            if ($end->greaterThan($closes)) {
                break;
            }

            // A slot that runs into the lunch break is not offered.
            $overlapsBreak = $break
                && $start->format('H:i') < $break[1]
                && $end->format('H:i') > $break[0];

            if (! $overlapsBreak) {
                $value = $start->format('H:i').'-'.$end->format('H:i');
                $slots[$value] = static::label($value);
            }

            $start = $end;
        }

        return $slots;
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_keys(static::all());
    }

    /** `09:00-09:30` read in the digits of the language being read. */
    public static function label(?string $slot): ?string
    {
        return blank($slot) ? null : Numerals::localize(str_replace('-', ' – ', $slot));
    }

    /** The office is shut on the days it has named; every other day is open. */
    public static function isOpenOn(CarbonInterface $date): bool
    {
        return ! in_array($date->dayOfWeek, static::closedDays(), true);
    }

    /**
     * Slots already given away, as `2026-10-06` => ['14:00-14:30', …].
     *
     * Only dates from today onwards: a slot in the past cannot be booked
     * anyway, and the map is handed to the browser.
     *
     * @return array<string, array<int, string>>
     */
    public static function taken(?int $exceptRequest = null): array
    {
        return ConsultancyRequest::query()
            ->whereNotNull('preferred_date')
            ->whereNotNull('preferred_slot')
            ->whereDate('preferred_date', '>=', CarbonImmutable::today())
            ->whereNotIn('status', self::RELEASED_STATUSES)
            ->when($exceptRequest, fn ($query) => $query->whereKeyNot($exceptRequest))
            ->get(['preferred_date', 'preferred_slot'])
            ->groupBy(fn ($request) => $request->preferred_date->toDateString())
            ->map(fn ($requests) => $requests->pluck('preferred_slot')->unique()->values()->all())
            ->all();
    }

    /** Is this slot still free on this date? */
    public static function isFree(string $date, string $slot, ?int $exceptRequest = null): bool
    {
        return ! in_array($slot, static::taken($exceptRequest)[$date] ?? [], true);
    }
}
