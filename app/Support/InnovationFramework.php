<?php

namespace App\Support;

use App\Models\Innovation;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

/** The Innovation Wing proposal, in the language of the page. */
class InnovationFramework
{
    /**
     * The document, with the admin's own innovations in place of the two lists
     * that are maintained there.
     *
     * Everything else on the page is the proposal as written and stays in
     * config. Only the current and proposed innovations are editable, because
     * only those change: a new prototype, a project moving from proposed to
     * current, a photograph arriving. A phase with no rows keeps the document's
     * version, so the page is never empty — before seeding, or if someone
     * deactivates every record.
     */
    public static function all(): array
    {
        $doc = LocalizedDocument::load('innovation_framework');

        foreach (static::records() as $phase => $records) {
            $doc = static::apply($doc, $phase, $records);
        }

        return $doc;
    }

    /**
     * @return array<string, Collection<int, Innovation>>
     */
    protected static function records(): array
    {
        try {
            return Innovation::query()->active()->ordered()->get()->groupBy('phase')->all();
        } catch (Throwable) {
            // The table is not there yet (a half-migrated deploy). The document
            // is a complete page on its own, so show that rather than fail.
            return [];
        }
    }

    /**
     * @param  Collection<int, Innovation>  $records
     */
    protected static function apply(array $doc, string $phase, Collection $records): array
    {
        if ($records->isEmpty()) {
            return $doc;
        }

        $doc[$phase] = $records->map(fn (Innovation $record) => $phase === Innovation::CURRENT
            ? static::current($record)
            : static::proposed($record))->all();

        $doc[$phase.'_covers'] = $records->map(fn (Innovation $record) => $record->image)->all();

        return $doc;
    }

    /** The current innovations are read positionally: [name, lead, highlights]. */
    protected static function current(Innovation $record): array
    {
        return [$record->name, (string) $record->lead, (string) $record->highlights];
    }

    /** The proposed ones carry the document's full write-up. */
    protected static function proposed(Innovation $record): array
    {
        // The optional three are dropped rather than blanked: the page tests
        // them with @isset, and an empty string would print an empty line.
        $optional = array_filter([
            'no' => $record->number,
            'native' => $record->otherName(),
            'subtitle' => $record->subtitle,
        ], fn ($value) => filled($value));

        return [
            ...$optional,
            'name' => $record->name,
            'tagline' => (string) $record->tagline,
            'concept' => (string) $record->concept,
            'how' => (string) $record->how,
            'why' => (string) $record->why,
            'departments' => (string) $record->departments,
            'sdg' => (string) $record->sdg,
        ];
    }
}
