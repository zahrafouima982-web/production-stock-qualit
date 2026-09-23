@extends('layouts.app')

@section('title', 'Production Dashboard')

@section('content')
    <h2 class="fw-bold mb-4">Production Dashboard</h2>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card p-3">
                <small class="text-muted">Planned</small>
                <div class="fw-bold fs-4">{{ $orders_by_status['PLANNED'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <small class="text-muted">In Progress</small>
                <div class="fw-bold fs-4">{{ $orders_by_status['IN_PROGRESS'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <small class="text-muted">Completed</small>
                <div class="fw-bold fs-4">{{ $orders_by_status['COMPLETED'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <small class="text-muted">Cancelled</small>
                <div class="fw-bold fs-4">{{ $orders_by_status['CANCELLED'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card p-3 h-100">
                <h5 class="fw-bold">Planned vs. Produced (active orders)</h5>
                <p class="text-muted mb-2" style="font-size: 0.85rem;">Sum across PLANNED and IN_PROGRESS orders.</p>
                <div class="d-flex justify-content-between">
                    <span>Planned</span>
                    <span class="fw-bold">{{ $planned_quantity_total }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Produced so far</span>
                    <span class="fw-bold">{{ $produced_quantity_total }}</span>
                </div>
                @if ($planned_quantity_total > 0)
                    <div class="progress mt-2" style="height: 10px;">
                        <div class="progress-bar bg-dark" style="width: {{ min(100, round(($produced_quantity_total / $planned_quantity_total) * 100)) }}%"></div>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3 h-100">
                <h5 class="fw-bold">Quality Signal</h5>
                <p class="text-muted mb-2" style="font-size: 0.85rem;">FAILED inspections in the last 30 days &mdash; a rising count may point to a production issue.</p>
                <div class="fw-bold fs-3 {{ $recent_fail_inspections_count > 0 ? 'text-danger' : 'text-success' }}">
                    {{ $recent_fail_inspections_count }}
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('production.orders.index') }}" class="btn btn-outline-dark">View all Production Orders &rarr;</a>
@endsection
