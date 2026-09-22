<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductionLine\StoreProductionLineRequest;
use App\Http\Requests\ProductionLine\UpdateProductionLineRequest;
use App\Models\ProductionLine;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductionLineController extends Controller
{
    use AuthorizesRequests;

    // authorizeResource() removed — it calls $this->middleware() internally,
    // which doesn't exist on this project's Controller base (Laravel 12
    // slim skeleton: App\Http\Controllers\Controller extends nothing).
    // Manual authorize() per action, same pattern as ProductController /
    // ProductionOrderController / every Quality & Stock controller.

    public function index(): View
    {
        $this->authorize('viewAny', ProductionLine::class);

        $productionLines = ProductionLine::orderBy('name')->paginate(15);

        return view('production-lines.index', compact('productionLines'));
    }

    public function create(): View
    {
        $this->authorize('create', ProductionLine::class);

        return view('production-lines.create');
    }

    public function store(StoreProductionLineRequest $request): RedirectResponse
    {
        $this->authorize('create', ProductionLine::class);

        ProductionLine::create($request->validated());

        return redirect()->route('production-lines.index')->with('status', 'Production line created successfully.');
    }

    public function edit(ProductionLine $productionLine): View
    {
        $this->authorize('update', $productionLine);

        return view('production-lines.edit', ['productionLine' => $productionLine]);
    }

    public function update(UpdateProductionLineRequest $request, ProductionLine $productionLine): RedirectResponse
    {
        $this->authorize('update', $productionLine);

        $productionLine->update($request->validated());

        return redirect()->route('production-lines.index')->with('status', 'Production line updated successfully.');
    }

    public function destroy(ProductionLine $productionLine): RedirectResponse
    {
        $this->authorize('delete', $productionLine);

        $productionLine->delete();

        return redirect()->route('production-lines.index')->with('status', 'Production line archived.');
    }
}