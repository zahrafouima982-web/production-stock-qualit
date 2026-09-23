@extends('layouts.app')

@section('title', 'Order ' . $order->order_number)

@section('content')
    @php
        $badge = match ($order->status) {
            'PLANNED' => 'bg-secondary',
            'IN_PROGRESS' => 'bg-primary',
            'COMPLETED' => 'bg-success',
            'CANCELLED' => 'bg-danger',
            default => 'bg-secondary',
        };
        $totalProduced = $order->productionRecords->sum('produced_quantity');
    @endphp

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h2 class="fw-bold mb-1">Order {{ $order->order_number }}</h2>
            <span class="badge {{ $badge }}">{{ $order->status }}</span>
        </div>
        <div>
            @can('update', $order)
                <a href="{{ route('production.orders.edit', $order) }}" class="btn btn-outline-secondary">Edit</a>
            @endcan
            @can('cancel', $order)
                <form method="POST" action="{{ route('production.orders.cancel', $order) }}" class="d-inline" onsubmit="return confirm('Cancel this order?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">Cancel Order</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card p-3"><small class="text-muted">Product</small><div class="fw-bold">{{ $order->product->name }}</div></div></div>
        <div class="col-md-3"><div class="card p-3"><small class="text-muted">Line</small><div class="fw-bold">{{ $order->productionLine->name }}</div></div></div>
        <div class="col-md-3"><div class="card p-3"><small class="text-muted">Planned</small><div class="fw-bold">{{ $order->planned_quantity }}</div></div></div>
        <div class="col-md-3"><div class="card p-3"><small class="text-muted">Produced</small><div class="fw-bold">{{ $totalProduced }}</div></div></div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">
            <a href="{{ route('production.orders.records.index', $order) }}" class="text-decoration-none text-dark">Production Records</a>
        </h4>
        @can('create', \App\Models\ProductionRecord::class)
            @if ($order->isCancellable())
                <a href="{{ route('production.orders.records.create', $order) }}" class="btn btn-sm btn-dark">+ Record Production</a>
            @endif
        @endcan
    </div>

    <div class="card">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Recorded At</th>
                    <th>Shift</th>
                    <th>Quantity</th>
                    <th>Recorded By</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($order->productionRecords as $record)
                    <tr>
                        <td>{{ $record->recorded_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $record->shift ?? '—' }}</td>
                        <td>{{ $record->produced_quantity }}</td>
                        <td>{{ $record->recordedBy->name }}</td>
                        <td class="text-end">
                            <a href="{{ route('production.records.show', $record) }}" class="btn btn-sm btn-outline-secondary">View</a>
                            @can('update', $record)
                                <a href="{{ route('production.records.edit', $record) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No production recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection