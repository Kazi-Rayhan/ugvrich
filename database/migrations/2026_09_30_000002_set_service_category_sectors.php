<?php

use App\Models\ServiceCategory;
use Illuminate\Database\Migrations\Migration;

/**
 * Fills in each main service's sector from the services already beneath it.
 *
 * The department was recorded per service, so the sector a main service belongs
 * to is already implied by its contents — this reads it rather than asking
 * someone to set five dropdowns by hand. A first guess only: the field is in
 * the admin, and anything set there survives, because this skips a category
 * that already has one.
 */
return new class extends Migration
{
    public function up(): void
    {
        ServiceCategory::with('services')->get()->each(function (ServiceCategory $category) {
            if (filled($category->department)) {
                return;
            }

            $department = $category->services
                ->pluck('department')
                ->filter()
                ->countBy()
                ->sortDesc()
                ->keys()
                ->first();

            if ($department) {
                $category->update(['department' => $department]);
            }
        });
    }

    public function down(): void
    {
        // The column itself is dropped by the migration that added it.
    }
};
