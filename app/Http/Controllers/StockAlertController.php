<?php

namespace App\Http\Controllers;

use App\Models\StockAlert;
use App\Services\StockService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class StockAlertController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private StockService $stockService)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', StockAlert::class);

        $alerts = StockAlert::with(['component', 'resolvedBy'])
            ->latest('triggered_at')
            ->paginate(20);

        return view('stock.alerts.index', compact('alerts'));
    }

    public function resolve(Request $request, StockAlert $alert): RedirectResponse
    {
        $this->authorize('resolve', $alert);

        try {
            $this->stockService->resolveAlert($alert, $request->user());
        } catch (RuntimeException $e) {
            return back()->withErrors(['alert' => $e->getMessage()]);
        }

        return redirect()->route('stock.alerts.index')->with('status', 'Alert resolved.');
    }
}
