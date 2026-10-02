<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Department → research field → research area, built from the framework.
 *
 * Nothing here is a new list. The departments and their fields are the
 * framework's priority areas; the areas under them are the areas named by the
 * research clusters that department belongs to. So the choices a researcher is
 * offered on the proposal form are the ones the Research Wing actually planned
 * for, and they stay in step with the document in both languages.
 */
class ResearchTaxonomy
{
    /**
     * The whole tree, ready to hand to the form.
     *
     * @return array<int, array{department: string, fields: array<int, array{name: string, sdgs: string}>, areas: array<int, string>}>
     */
    public static function tree(): array
    {
        $framework = ResearchFramework::all();

        $areas = collect($framework['priority_areas'] ?? []);
        $clusters = collect($framework['clusters'] ?? []);

        return $areas
            ->groupBy(0)
            ->map(fn (Collection $fields, string $department) => [
                'department' => $department,
                'fields' => $fields
                    ->map(fn (array $field) => [
                        'name' => (string) ($field[1] ?? ''),
                        'sdgs' => (string) ($field[2] ?? ''),
                    ])
                    ->filter(fn (array $field) => $field['name'] !== '')
                    ->values()
                    ->all(),
                'areas' => static::areasFor($clusters, $department),
            ])
            ->values()
            ->all();
    }

    /**
     * The cluster areas open to a department.
     *
     * A cluster names its departments in one string — "CSE + EEE + English" —
     * so membership is read from that. A department in no cluster gets every
     * area rather than none: an empty third step would be a dead end on the
     * form, and the clusters are cross-departmental by design anyway.
     *
     * @param  Collection<int, array<string, mixed>>  $clusters
     * @return array<int, string>
     */
    protected static function areasFor(Collection $clusters, string $department): array
    {
        $mine = $clusters->filter(
            fn (array $cluster) => str_contains(
                mb_strtolower((string) ($cluster['departments'] ?? '')),
                mb_strtolower($department),
            ),
        );

        if ($mine->isEmpty()) {
            $mine = $clusters;
        }

        return $mine
            ->flatMap(fn (array $cluster) => $cluster['areas'] ?? [])
            ->map(fn ($area) => trim((string) $area))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /** Just the department names, in the framework's order. */
    public static function departments(): array
    {
        return array_column(static::tree(), 'department');
    }
}
