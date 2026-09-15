@extends('layouts.app')

@section('title', 'Production Orders')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Production Orders</h2>
        @can('create', \App\Models\ProductionOrder::class)
            <a href="{{ route('production.orders.create') }}" class="btn btn-dark">+ New Order</a>
        @endcan
    </div>

    <div class="card">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Product</th>
                    <th>Line</th>
                    <th>Planned</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->product->name }}</td>
                        <td>{{ $order->productionLine->name }}</td>
                        <td>{{ $order->planned_quantity }}</td>
                        <td>
                            @php
                                $badge = match ($order->status) {
                                    'PLANNED' => 'bg-secondary',
                                    'IN_PROGRESS' => 'bg-primary',
                                    'COMPLETED' => 'bg-success',
                                    'CANCELLED' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badge }}">{{ $order->status }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('production.orders.show', $order) }}" class="btn btn-sm btn-outline-secondary">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No production orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $orders->links() }}</div>
@endsection