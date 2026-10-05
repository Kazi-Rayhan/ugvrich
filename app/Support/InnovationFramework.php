<?php

namespace App\Support;

use App\Models\Innovation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
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

        $doc['proposed'] = static::slugged($doc['proposed'] ?? []);
        $doc['current_slugs'] = static::currentSlugs($doc['current'] ?? []);

        return $doc;
    }

    /**
     * The page address of each innovation record, keyed by its id.
     *
     * The document lists each phase in the same order as the active records
     * (that is how apply() builds it), so a record's place in its phase is its
     * place in the document, and the address is read from there. That keeps an
     * area's links pointing at exactly the pages the Innovation page links to.
     *
     * @return array<int, string>
     */
    public static function recordSlugs(?array $doc = null): array
    {
        $doc ??= static::all();
        $slugs = [];

        foreach (static::records() as $phase => $records) {
            foreach ($records->values() as $i => $record) {
                $slug = $phase === Innovation::CURRENT
                    ? ($doc['current_slugs'][$i] ?? null)
                    : ($doc['proposed'][$i]['slug'] ?? null);

                if ($slug) {
                    $slugs[$record->id] = $slug;
                }
            }
        }

        return $slugs;
    }

    /**
     * The innovation at an address, in either phase, or null.
     *
     * The two phases are shaped differently — a current innovation is a lead
     * line and a highlights paragraph, a proposed one is the full write-up — so
     * what comes back says which it is, and the page renders accordingly.
     *
     * @return array{phase: string, item: array, index: int}|null
     */
    public static function find(string $slug, ?array $doc = null): ?array
    {
        $doc ??= static::all();

        foreach ($doc['proposed'] as $i => $item) {
            if (($item['slug'] ?? null) === $slug) {
                return ['phase' => Innovation::PROPOSED, 'item' => $item, 'index' => $i];
            }
        }

        foreach ($doc['current_slugs'] as $i => $current) {
            if ($current !== $slug) {
                continue;
            }

            [$name, $lead, $highlights] = $doc['current'][$i];

            return [
                'phase' => Innovation::CURRENT,
                'index' => $i,
                'item' => [
                    'slug' => $slug,
                    'name' => $name,
                    'lead' => $lead,
                    'highlights' => $highlights,
                ],
            ];
        }

        return null;
    }

    /** One proposed innovation, or null when the address names nothing. */
    public static function proposal(string $slug): ?array
    {
        $found = static::find($slug);

        return $found && $found['phase'] === Innovation::PROPOSED ? $found['item'] : null;
    }

    /**
     * The current innovations are read positionally, so their addresses travel
     * beside them rather than inside them.
     *
     * @return array<int, string>
     */
    protected static function currentSlugs(array $current): array
    {
        $english = config('innovation_framework.current', []);

        return array_map(
            fn (int $i) => Str::slug($english[$i][0] ?? $current[$i][0]),
            array_keys($current),
        );
    }

    /**
     * Each proposed innovation gets an address of its own.
     *
     * Built from the English name in every language, so /innovation/solar-scooty
     * and /en/innovation/solar-scooty are the same page and a link shared from
     * one reads the same in the other. Slugging the Bangla name would give a
     * different address per language for the same innovation.
     */
    protected static function slugged(array $proposed): array
    {
        $english = config('innovation_framework.proposed', []);

        foreach ($proposed as $i => $item) {
            $proposed[$i]['slug'] = $item['slug']
                ?? Str::slug($english[$i]['name'] ?? $item['name']);
        }

        return $proposed;
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
            'slug' => Str::slug($record->getRawOriginal('name')),
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
