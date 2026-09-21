<?php

namespace App\Http\Controllers;

use App\Http\Requests\Stock\StoreStockMovementRequest;
use App\Models\Component;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

/**
 * No show/edit/update/destroy: stock_movements is an append-only ledger
 * (same rule as production_records / quality_inspections) — index to read
 * history, store to add a new entry, never edit or delete one.
 */
class StockMovementController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private StockService $stockService)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', StockMovement::class);

        $movements = StockMovement::with(['component', 'performedBy'])
            ->latest('performed_at')
            ->paginate(20);

        return view('stock.movements.index', compact('movements'));
    }

    public function create(): View
    {
        $this->authorize('create', StockMovement::class);

        $components = Component::where('is_active', true)->orderBy('name')->get();

        return view('stock.movements.create', compact('components'));
    }

    public function store(StoreStockMovementRequest $request): RedirectResponse
    {
        $this->authorize('create', StockMovement::class);

        try {
            $data = $request->validated();

            $movement = $data['type'] === StockMovement::TYPE_IN
                ? $this->stockService->recordIn($data, $request->user())
                : $this->stockService->recordOut($data, $request->user());
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['quantity' => $e->getMessage()]);
        }

        return redirect()->route('stock.movements.index')
            ->with('status', "Movement recorded: {$movement->type} {$movement->quantity} on {$movement->component->code}.");
    }
}
