<?php

namespace App\Http\Controllers;

use App\Models\ProductionOrder;
use App\Models\ProductionRecord;
use App\Services\ProductionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductionRecordController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private ProductionService $productionService)
    {
    }

    public function index(ProductionOrder $order): View
    {
        $this->authorize('viewAny', [ProductionRecord::class, $order]);

        $records = $order->productionRecords()
            ->with('recordedBy')
            ->latest('recorded_at')
            ->paginate(15);

        return view('production.records.index', compact('order', 'records'));
    }

    public function create(ProductionOrder $order): View
    {
        $this->authorize('create', [ProductionRecord::class, $order]);

        return view('production.records.create', compact('order'));
    }

    public function store(Request $request, ProductionOrder $order): RedirectResponse
    {
        $this->authorize('create', [ProductionRecord::class, $order]);

        $validated = $request->validate([
            'produced_quantity' => 'required|integer|min:1',
            'shift' => 'nullable|string',
            'recorded_at' => 'nullable|date',
        ]);

        $this->productionService->recordProduction($order, $validated, $request->user());

        return redirect()->route('production.orders.show', $order)->with('status', 'Production recorded.');
    }

    public function show(ProductionRecord $record): View
    {
        $this->authorize('view', $record);

        $record->load(['productionOrder.product', 'recordedBy', 'qualityInspections']);

        return view('production.records.show', compact('record'));
    }

    public function edit(ProductionRecord $record): View
    {
        $this->authorize('update', $record);

        return view('production.records.edit', compact('record'));
    }

    public function update(Request $request, ProductionRecord $record): RedirectResponse
    {
        $this->authorize('update', $record);

        $validated = $request->validate([
            'produced_quantity' => 'required|integer|min:1',
            'shift' => 'nullable|string',
            'recorded_at' => 'nullable|date',
        ]);

        $this->productionService->updateRecord($record, $validated, $request->user());

        return redirect()->route('production.orders.show', $record->productionOrder)->with('status', 'Record updated.');
    }
}