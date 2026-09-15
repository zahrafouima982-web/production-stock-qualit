<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductionLine\StoreProductionLineRequest;
use App\Http\Requests\ProductionLine\UpdateProductionLineRequest;
use App\Models\ProductionLine;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductionLineController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ProductionLine::class, 'production_line');
    }

    public function index(): View
    {
        $productionLines = ProductionLine::orderBy('name')->paginate(15);

        return view('production-lines.index', compact('productionLines'));
    }

    public function create(): View
    {
        return view('production-lines.create');
    }

    public function store(StoreProductionLineRequest $request): RedirectResponse
    {
        ProductionLine::create($request->validated());

        return redirect()->route('production-lines.index')->with('status', 'Production line created successfully.');
    }

    public function edit(ProductionLine $productionLine): View
    {
        return view('production-lines.edit', ['productionLine' => $productionLine]);
    }

    public function update(UpdateProductionLineRequest $request, ProductionLine $productionLine): RedirectResponse
    {
        $productionLine->update($request->validated());

        return redirect()->route('production-lines.index')->with('status', 'Production line updated successfully.');
    }

    public function destroy(ProductionLine $productionLine): RedirectResponse
    {
        $productionLine->delete();

        return redirect()->route('production-lines.index')->with('status', 'Production line archived.');
    }
}
