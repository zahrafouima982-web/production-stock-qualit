<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductionLine;
use App\Models\ProductionOrder;
use App\Services\ProductionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductionOrderController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private ProductionService $productionService)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', ProductionOrder::class);

        $orders = ProductionOrder::with(['product', 'productionLine'])
            ->latest()
            ->paginate(15);

        return view('production.orders.index', compact('orders'));
    }

    public function create(): View
    {
        $this->authorize('create', ProductionOrder::class);

        $products = Product::where('is_active', true)->orderBy('name')->get();
        $productionLines = ProductionLine::where('is_active', true)->orderBy('name')->get();

        return view('production.orders.create', compact('products', 'productionLines'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ProductionOrder::class);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'production_line_id' => 'required|exists:production_lines,id',
            'planned_quantity' => 'required|integer|min:1',
        ]);

        $order = $this->productionService->createOrder($validated, $request->user());

        return redirect()->route('production.orders.show', $order)
            ->with('status', "Order {$order->order_number} created.");
    }

    public function show(ProductionOrder $order): View
    {
        $this->authorize('view', $order);

        $order->load(['product', 'productionLine', 'createdBy', 'productionRecords.recordedBy']);

        return view('production.orders.show', compact('order'));
    }

    public function edit(ProductionOrder $order): View
    {
        $this->authorize('update', $order);

        $products = Product::where('is_active', true)->orderBy('name')->get();
        $productionLines = ProductionLine::where('is_active', true)->orderBy('name')->get();

        return view('production.orders.edit', compact('order', 'products', 'productionLines'));
    }

    public function update(Request $request, ProductionOrder $order): RedirectResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'production_line_id' => 'required|exists:production_lines,id',
            'planned_quantity' => 'required|integer|min:1',
        ]);

        $this->productionService->updateOrder($order, $validated, $request->user());

        return redirect()->route('production.orders.show', $order)->with('status', 'Order updated.');
    }

    public function cancel(Request $request, ProductionOrder $order): RedirectResponse
    {
        $this->authorize('cancel', $order);

        $this->productionService->cancelOrder($order, $request->user());

        return redirect()->route('production.orders.show', $order)->with('status', 'Order cancelled.');
    }
}