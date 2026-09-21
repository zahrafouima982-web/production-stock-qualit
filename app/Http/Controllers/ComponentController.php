<?php

namespace App\Http\Controllers;

use App\Http\Requests\Stock\StoreComponentRequest;
use App\Http\Requests\Stock\UpdateComponentRequest;
use App\Models\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ComponentController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        $this->authorize('viewAny', Component::class);

        $components = Component::orderBy('name')->paginate(15);

        return view('components.index', compact('components'));
    }

    public function create(): View
    {
        $this->authorize('create', Component::class);

        return view('components.create');
    }

    public function store(StoreComponentRequest $request): RedirectResponse
    {
        $this->authorize('create', Component::class);

        // current_quantity is never accepted from the form — a new
        // component always starts at 0; stock is only ever added via a
        // recorded IN movement (see StockService::recordIn()).
        Component::create($request->validated() + ['current_quantity' => 0]);

        return redirect()->route('components.index')->with('status', 'Component created successfully.');
    }

    public function edit(Component $component): View
    {
        $this->authorize('update', $component);

        return view('components.edit', compact('component'));
    }

    public function update(UpdateComponentRequest $request, Component $component): RedirectResponse
    {
        $this->authorize('update', $component);

        $component->update($request->validated());

        return redirect()->route('components.index')->with('status', 'Component updated successfully.');
    }

    public function destroy(Component $component): RedirectResponse
    {
        $this->authorize('delete', $component);

        $component->delete();

        return redirect()->route('components.index')->with('status', 'Component archived.');
    }
}
