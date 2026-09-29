<?php

namespace Database\Seeders;

use App\Models\Innovation;
use Illuminate\Database\Seeder;

/**
 * Loads the nine innovations from the proposal into the admin.
 *
 * The document stays the source: config/innovation_framework.php holds the
 * English, lang/bn/innovation_framework.php the Bangla, and this copies both
 * into the table so the wing starts with exactly what the page already showed
 * and edits from there.
 *
 * Matched on the English name, so running it again refreshes the document text
 * without touching an uploaded photograph, the ordering, or a record the admin
 * has since added.
 */
class InnovationRecordSeeder extends Seeder
{
    public function run(): void
    {
        $en = config('innovation_framework');
        $bn = require lang_path('bn/innovation_framework.php');

        $this->current($en, $bn);
        $this->proposed($en, $bn);
    }

    /** Current innovations are written positionally: [name, lead, highlights]. */
    protected function current(array $en, array $bn): void
    {
        foreach ($en['current'] ?? [] as $i => [$name, $lead, $highlights]) {
            [$nameBn, $leadBn, $highlightsBn] = $bn['current'][$i] ?? [null, null, null];

            $this->write($name, [
                'phase' => Innovation::CURRENT,
                'lead' => $lead,
                'highlights' => $highlights,
                'name_bn' => $nameBn,
                'lead_bn' => $leadBn,
                'highlights_bn' => $highlightsBn,
                'image' => $en['current_covers'][$i] ?? null,
                'sort_order' => $i,
            ]);
        }
    }

    protected function proposed(array $en, array $bn): void
    {
        foreach ($en['proposed'] ?? [] as $i => $item) {
            $twin = $bn['proposed'][$i] ?? [];

            $fields = ['subtitle', 'tagline', 'concept', 'how', 'why', 'departments', 'sdg'];

            $this->write($item['name'], [
                'phase' => Innovation::PROPOSED,
                'number' => $item['no'] ?? null,
                // The document prints the Bangla name as the English entry's
                // `native`, and the two swap over in the Bangla file.
                'name_bn' => $item['native'] ?? ($twin['name'] ?? null),
                'image' => $en['proposed_covers'][$i] ?? null,
                'sort_order' => $i,
                ...collect($fields)->mapWithKeys(fn (string $field) => [
                    $field => $item[$field] ?? null,
                    $field.'_bn' => $twin[$field] ?? null,
                ])->all(),
            ]);
        }
    }

    /** @param array<string, mixed> $attributes */
    protected function write(string $name, array $attributes): void
    {
        $record = Innovation::firstOrNew(['name' => $name]);

        if ($record->exists) {
            // A photograph and an order chosen in the admin outlive a re-seed;
            // only the document's own wording is refreshed.
            unset($attributes['sort_order']);

            if (filled($record->getRawOriginal('image'))) {
                unset($attributes['image']);
            }
        }

        $record->fill($attributes)->save();
    }
}
