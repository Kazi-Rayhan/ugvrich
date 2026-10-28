<?php

namespace App\Models\Concerns;

use App\Models\ServiceCategory;
use App\Support\Money;
use App\Support\Sales\Totals;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * What a proposal and an order share: a running number, priced lines, the
 * categories they cover, and figures worked out from the lines on the server.
 *
 * The using model defines NUMBER_PREFIX, items() and a pivot table name.
 */
trait HasSalesLines
{
    public static function bootHasSalesLines(): void
    {
        // A placeholder until the id is known; the id then gives a number that
        // two people saving at once can never both get.
        static::creating(function ($model) {
            $model->number ??= 'TMP-'.Str::random(20);
        });

        // Conditions are saved as a plain list; a condition left empty is dropped.
        static::saving(function ($model) {
            if ($model->isDirty('conditions') && is_array($model->conditions)) {
                $model->conditions = collect($model->conditions)
                    ->filter(fn ($row) => is_array($row) && filled($row['label'] ?? null) && filled($row['details'] ?? null))
                    ->map(fn (array $row) => [
                        'source' => $row['source'] ?? null,
                        'heading' => $row['heading'] ?? null,
                        'label' => trim((string) $row['label']),
                        'details' => trim((string) $row['details']),
                    ])
                    ->values()
                    ->all();
            }
        });

        static::created(function ($model) {
            if (str_starts_with((string) $model->number, 'TMP-')) {
                $model->number = sprintf('%s-%s-%05d', static::NUMBER_PREFIX, now()->format('Y'), $model->id);
                $model->saveQuietly();
            }
        });
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ServiceCategory::class, $this->categoryPivotTable());
    }

    /** Recompute every figure from the saved lines; the form's own numbers are never trusted. */
    public function recalculate(): void
    {
        $items = $this->items()->get();

        foreach ($items as $item) {
            $line = Totals::line($item->quantity, $item->unit_price, $item->discount);
            $lineTotal = Money::fromMinor($line['total']);

            if ((string) $item->line_total !== $lineTotal) {
                $item->forceFill(['line_total' => $lineTotal])->saveQuietly();
            }
        }

        $totals = Totals::of(
            $items->map(fn ($item) => $item->only(['quantity', 'unit_price', 'discount'])),
            $this->extra_discount,
            $this->tax_rate,
        );

        $this->forceFill([
            'subtotal' => Money::fromMinor($totals['subtotal']),
            'discount_total' => Money::fromMinor($totals['discount_total']),
            'tax_amount' => Money::fromMinor($totals['tax_amount']),
            'total' => Money::fromMinor($totals['total']),
        ])->saveQuietly();
    }
}
