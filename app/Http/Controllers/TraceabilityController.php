<?php

namespace App\Http\Controllers;

use App\Models\ProductionOrder;
use App\Services\TraceabilityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Read-only by design — no store/update/destroy anywhere in this controller.
 * Open to all four authenticated roles (see routes/web.php comment): the
 * permission matrix grants every role "R" on traceability, and nothing here
 * can mutate data, so there's no dedicated Policy class — a Policy exists to
 * gate an action on a specific Eloquent model, and this feature is
 * inherently cross-model.
 */
class TraceabilityController extends Controller
{
    public function __construct(private TraceabilityService $traceability)
    {
    }

    public function index(Request $request): View
    {
        $term = $request->query('q');

        $orders = $this->traceability->searchOrders($term);

        return view('traceability.index', compact('orders', 'term'));
    }

    public function show(ProductionOrder $order): View
    {
        $order = $this->traceability->loadOrderChain($order);
        $timeline = $this->traceability->buildActivityTimeline($order);

        return view('traceability.show', compact('order', 'timeline'));
    }
}
