@extends('layouts.app')

@section('title', 'Inspection #' . $inspection->id)

@section('content')
    @php
        $badge = match ($inspection->result) {
            'PASS' => 'bg-success',
            'FAIL' => 'bg-danger',
            default => 'bg-secondary',
        };
    @endphp

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h2 class="fw-bold mb-1">Inspection #{{ $inspection->id }}</h2>
            <span class="badge {{ $badge }}">{{ $inspection->result }}</span>
        </div>
        @can('reinspect', $inspection)
            @if ($inspection->result === 'FAIL')
                <form method="POST" action="{{ route('quality.inspections.reinspect', $inspection) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-dark">Start Reinspection</button>
                </form>
            @endif
        @endcan
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card p-3"><small class="text-muted">Order</small><div class="fw-bold">{{ $inspection->productionRecord->productionOrder->order_number }}</div></div></div>
        <div class="col-md-3"><div class="card p-3"><small class="text-muted">Product</small><div class="fw-bold">{{ $inspection->productionRecord->productionOrder->product->name }}</div></div></div>
        <div class="col-md-3"><div class="card p-3"><small class="text-muted">Inspector</small><div class="fw-bold">{{ $inspection->inspector->name }}</div></div></div>
        <div class="col-md-3"><div class="card p-3"><small class="text-muted">Inspected At</small><div class="fw-bold">{{ $inspection->inspected_at?->format('Y-m-d H:i') ?? '—' }}</div></div></div>
    </div>

    @if ($inspection->notes)
        <div class="alert alert-light border mb-4">{{ $inspection->notes }}</div>
    @endif

    <h4 class="fw-bold mb-3">Inspection Items</h4>
    <div class="card mb-4">
        <table class="table table-striped mb-0">
            <thead>
                <tr><th>Criterion</th><th>Result</th><th>Remarks</th></tr>
            </thead>
            <tbody>
                @foreach ($inspection->inspectionItems as $item)
                    <tr>
                        <td>{{ $item->criterion_name }}</td>
                        <td>
                            @php
                                $itemBadge = match ($item->result) {
                                    'PASS' => 'bg-success',
                                    'FAIL' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $itemBadge }}">{{ $item->result }}</span>
                        </td>
                        <td>{{ $item->remarks ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Quality Defects</h4>
        @can('create', \App\Models\QualityDefect::class)
            @if ($inspection->result === 'FAIL')
                <a href="{{ route('quality.inspections.defects.create', $inspection) }}" class="btn btn-sm btn-dark">+ Declare Defect</a>
            @endif
        @endcan
    </div>

    <div class="card">
        <table class="table table-striped mb-0">
            <thead>
                <tr><th>Type</th><th>Severity</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse ($inspection->qualityDefects as $defect)
                    <tr>
                        <td>{{ $defect->defect_type }}</td>
                        <td>{{ $defect->severity }}</td>
                        <td><span class="badge bg-secondary">{{ $defect->status }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('quality.defects.show', $defect) }}" class="btn btn-sm btn-outline-secondary">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">
                        @if ($inspection->result === 'FAIL')
                            No defect declared yet for this failed inspection.
                        @else
                            Inspection passed &mdash; no defects.
                        @endif
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection