@extends('layouts.app')

@section('title', 'Production Records - Order ' . $order->order_number)

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h2 class="fw-bold mb-1">Production Records</h2>
            <p class="text-muted mb-0">
                Order <a href="{{ route('production.orders.show', $order) }}">{{ $order->order_number }}</a>
                &mdash; {{ $order->product->name }}
            </p>
        </div>
        @can('create', \App\Models\ProductionRecord::class)
            @if ($order->isCancellable())
                <a href="{{ route('production.orders.records.create', $order) }}" class="btn btn-dark">+ Record Production</a>
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
                @forelse ($records as $record)
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
                    <tr><td colspan="5" class="text-center text-muted py-4">No production recorded yet for this order.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $records->links() }}</div>
@endsection