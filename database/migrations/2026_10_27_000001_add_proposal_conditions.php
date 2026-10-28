<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Scope and conditions on proposals.
 *
 * A category (and, more narrowly, a service) can carry a template of
 * conditions: "Number of pages: 10", "Payment gateways: 2", "2D design: …".
 * Choosing that category or service on a proposal copies its template in,
 * where it is edited for that client. The proposal keeps its own copy, as
 * the order made from it does, so editing a template later changes nothing
 * already agreed.
 *
 * Only adds columns. Two categories get a starter template, and only while
 * theirs is still empty.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            $table->json('proposal_conditions')->nullable();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->json('proposal_conditions')->nullable();
        });

        Schema::table('proposals', function (Blueprint $table) {
            $table->json('conditions')->nullable()->after('delivery_timeline');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->json('conditions')->nullable()->after('expected_delivery_date');
        });

        $starters = [
            'smart-ict-services' => [
                ['label' => 'Number of pages', 'details' => 'Up to 10 pages (Home, About, Services, Contact and others agreed with the client).'],
                ['label' => 'Design', 'details' => 'Modern, clean look; fully responsive on mobile, tablet and desktop.'],
                ['label' => 'Payment gateways', 'details' => '2 payment gateways integrated.'],
                ['label' => 'Payment gateway details', 'details' => 'e.g. bKash and SSLCommerz. Merchant accounts and their fees are the client\'s; RICH integrates and tests them.'],
                ['label' => 'Domain & hosting', 'details' => 'Supplied by the client, or arranged by RICH at cost for the first year.'],
                ['label' => 'Revisions', 'details' => 'Up to 2 rounds of design revisions before development starts.'],
                ['label' => 'Support', 'details' => '3 months of free bug-fix support after launch.'],
            ],
            'smart-infrastructure-services' => [
                ['label' => '2D design', 'details' => 'Architectural floor plans, elevations and sections in AutoCAD, to scale.'],
                ['label' => '3D design', 'details' => '3D model with exterior renders (up to 4 views); interior renders quoted separately.'],
                ['label' => 'Structural design', 'details' => 'Structural analysis and drawings to BNBC, where included in the services.'],
                ['label' => 'Revisions', 'details' => 'Up to 2 rounds of revisions on each drawing set.'],
                ['label' => 'Site visits', 'details' => 'Up to 2 site visits within the city; travel beyond is charged at cost.'],
                ['label' => 'Deliverables', 'details' => 'Drawings in PDF and DWG; printed copies on request.'],
            ],
        ];

        foreach ($starters as $slug => $rows) {
            DB::table('service_categories')
                ->where('slug', $slug)
                ->whereNull('proposal_conditions')
                ->update(['proposal_conditions' => json_encode($rows, JSON_UNESCAPED_UNICODE)]);
        }
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('conditions'));
        Schema::table('proposals', fn (Blueprint $table) => $table->dropColumn('conditions'));
        Schema::table('services', fn (Blueprint $table) => $table->dropColumn('proposal_conditions'));
        Schema::table('service_categories', fn (Blueprint $table) => $table->dropColumn('proposal_conditions'));
    }
};
