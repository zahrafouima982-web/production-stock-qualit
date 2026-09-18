@extends('layouts.app')

@section('title', 'Production Record')

@section('content')
    <h2 class="fw-bold mb-1">Production Record</h2>
    <p class="text-muted mb-4">
        Order <a href="{{ route('production.orders.show', $record->productionOrder) }}">{{ $record->productionOrder->order_number }}</a>
        &mdash; {{ $record->productionOrder->product->name }}
    </p>

    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card p-3"><small class="text-muted">Quantity</small><div class="fw-bold">{{ $record->produced_quantity }}</div></div></div>
        <div class="col-md-3"><div class="card p-3"><small class="text-muted">Shift</small><div class="fw-bold">{{ $record->shift ?? '—' }}</div></div></div>
        <div class="col-md-3"><div class="card p-3"><small class="text-muted">Recorded At</small><div class="fw-bold">{{ $record->recorded_at->format('Y-m-d H:i') }}</div></div></div>
        <div class="col-md-3"><div class="card p-3"><small class="text-muted">Recorded By</small><div class="fw-bold">{{ $record->recordedBy->name }}</div></div></div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Quality Inspections</h4>
        @can('create', \App\Models\QualityInspection::class)
            <a href="{{ route('quality.inspections.create', ['production_record_id' => $record->id]) }}" class="btn btn-sm btn-dark">+ New Inspection</a>
        @endcan
    </div>
    <div class="card">
        <table class="table table-striped mb-0">
            <thead>
                <tr><th>Result</th><th>Inspected At</th><th>Notes</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse ($record->qualityInspections as $inspection)
                    <tr>
                        <td>{{ $inspection->result }}</td>
                        <td>{{ $inspection->inspected_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        <td>{{ $inspection->notes ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('quality.inspections.show', $inspection) }}" class="btn btn-sm btn-outline-secondary">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Not inspected yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection