<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Proposal;

/**
 * Print-ready proposal and order pages, opened from the admin panel. The
 * browser's own "Save as PDF" turns either into a PDF, so no PDF library is
 * needed. Each page checks the same permission as viewing it in the panel.
 */
class SalesPrintController extends Controller
{
    public function proposal(Proposal $proposal)
    {
        abort_unless(auth()->user()?->can('view', $proposal), 403);

        $proposal->load(['client', 'items', 'categories']);

        return view('sales.print', ['document' => $proposal, 'kind' => 'proposal']);
    }

    public function order(Order $order)
    {
        abort_unless(auth()->user()?->can('view', $order), 403);

        $order->load(['client', 'items', 'categories', 'proposal', 'payments']);

        return view('sales.print', ['document' => $order, 'kind' => 'order']);
    }
}
