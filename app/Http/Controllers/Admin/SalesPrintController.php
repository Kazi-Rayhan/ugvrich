<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Proposal;
use App\Models\WorkOrder;

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

    /** The work order on its controlled form, page for page as the approved PDF. */
    public function workOrder(WorkOrder $workOrder)
    {
        abort_unless(auth()->user()?->can('view', $workOrder), 403);

        $sector = WorkOrder::sector($workOrder->sector);
        abort_if($sector === [], 404, 'This work order has no sector.');

        return view('sales.work-order', ['order' => $workOrder, 'sector' => $sector]);
    }
}
