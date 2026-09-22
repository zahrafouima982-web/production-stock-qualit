@extends('layouts.app')

@section('title', 'Traceability')

@section('content')
    <h2 class="fw-bold mb-4">Traceability</h2>

    <form method="GET" action="{{ route('traceability.index') }}" class="card p-3 mb-4">
        <div class="input-group">
            <input type="text" name="q" class="form-control" placeholder="Search by order number or product name/code..."
                value="{{ $term }}">
            <button type="submit" class="btn btn-dark">Search</button>
            @if ($term)
                <a href="{{ route('traceability.index') }}" class="btn btn-outline-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="card">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Product</th>
                    <th>Line</th>
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
                        <td><span class="badge bg-secondary">{{ $order->status }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('traceability.show', $order) }}" class="btn btn-sm btn-outline-dark">Trace</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">
                        @if ($term)
                            No production order matches "{{ $term }}".
                        @else
                            No production orders yet.
                        @endif
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
