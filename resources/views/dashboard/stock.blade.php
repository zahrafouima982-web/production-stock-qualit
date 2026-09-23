@extends('layouts.app')

@section('title', 'Stock Dashboard')

@section('content')
    <h2 class="fw-bold mb-4">Stock Dashboard</h2>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card p-3">
                <small class="text-muted">Total Components</small>
                <div class="fw-bold fs-4">{{ $total_components }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <small class="text-muted">Critical</small>
                <div class="fw-bold fs-4 {{ $critical_count > 0 ? 'text-warning' : '' }}">{{ $critical_count }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <small class="text-muted">Out of Stock</small>
                <div class="fw-bold fs-4 {{ $out_of_stock_count > 0 ? 'text-danger' : '' }}">{{ $out_of_stock_count }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <small class="text-muted">Active Alerts</small>
                <div class="fw-bold fs-4 {{ $active_alerts_count > 0 ? 'text-danger' : '' }}">{{ $active_alerts_count }}</div>
            </div>
        </div>
    </div>

    <h5 class="fw-bold mb-3">Recent Movements</h5>
    <div class="card mb-3">
        <table class="table table-striped mb-0">
            <thead><tr><th>Date</th><th>Component</th><th>Type</th><th>Quantity</th><th>By</th></tr></thead>
            <tbody>
                @forelse ($recent_movements as $movement)
                    <tr>
                        <td>{{ $movement->performed_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $movement->component->code }} &mdash; {{ $movement->component->name }}</td>
                        <td><span class="badge {{ $movement->type === 'IN' ? 'bg-success' : 'bg-danger' }}">{{ $movement->type }}</span></td>
                        <td>{{ $movement->quantity }}</td>
                        <td>{{ $movement->performedBy->name }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No movements recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('stock.movements.index') }}" class="btn btn-outline-dark">All Movements &rarr;</a>
        <a href="{{ route('stock.alerts.index') }}" class="btn btn-outline-dark">All Alerts &rarr;</a>
    </div>
@endsection
