@extends('layouts.app')

@section('title', 'Trace: ' . $order->order_number)

@section('content')
    @php
        $orderBadge = match ($order->status) {
            'PLANNED' => 'bg-secondary',
            'IN_PROGRESS' => 'bg-primary',
            'COMPLETED' => 'bg-success',
            'CANCELLED' => 'bg-danger',
            default => 'bg-secondary',
        };
    @endphp

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h2 class="fw-bold mb-1">Order {{ $order->order_number }}</h2>
            <span class="badge {{ $orderBadge }}">{{ $order->status }}</span>
        </div>
        <a href="{{ route('traceability.index') }}" class="btn btn-outline-secondary">&larr; Back to search</a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card p-3"><small class="text-muted">Product</small><div class="fw-bold">{{ $order->product->name }}</div></div></div>
        <div class="col-md-3"><div class="card p-3"><small class="text-muted">Production Line</small><div class="fw-bold">{{ $order->productionLine->name }}</div></div></div>
        <div class="col-md-3"><div class="card p-3"><small class="text-muted">Planned Qty</small><div class="fw-bold">{{ $order->planned_quantity }}</div></div></div>
        <div class="col-md-3"><div class="card p-3"><small class="text-muted">Created By</small><div class="fw-bold">{{ $order->createdBy->name }}</div></div></div>
    </div>

    <div class="alert alert-light border mb-4" style="font-size: 0.85rem;">
        <strong>Note:</strong> components and stock movements are not linked to production orders in this system
        (no foreign key connects them — see the architecture notes) and are therefore not shown below.
    </div>

    <h4 class="fw-bold mb-3">Production Records &amp; Quality Chain</h4>

    @forelse ($order->productionRecords as $record)
        <div class="card mb-4">
            <div class="card-header bg-white">
                <strong>Record #{{ $record->id }}</strong>
                &mdash; {{ $record->produced_quantity }} units, {{ $record->shift ?? 'no shift specified' }}
                on {{ $record->recorded_at->format('Y-m-d H:i') }}
                by {{ $record->recordedBy->name }}
            </div>
            <div class="card-body">
                @forelse ($record->qualityInspections as $inspection)
                    @php
                        $resultBadge = match ($inspection->result) {
                            'PASS' => 'bg-success',
                            'FAIL' => 'bg-danger',
                            default => 'bg-secondary',
                        };
                    @endphp
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <a href="{{ route('quality.inspections.show', $inspection) }}">Inspection #{{ $inspection->id }}</a>
                                <span class="badge {{ $resultBadge }} ms-2">{{ $inspection->result }}</span>
                            </div>
                            <small class="text-muted">
                                {{ $inspection->inspected_at?->format('Y-m-d H:i') ?? '—' }} by {{ $inspection->inspector->name }}
                            </small>
                        </div>

                        @if ($inspection->inspectionItems->isNotEmpty())
                            <table class="table table-sm mb-2">
                                <thead><tr><th>Criterion</th><th>Result</th></tr></thead>
                                <tbody>
                                    @foreach ($inspection->inspectionItems as $item)
                                        <tr>
                                            <td>{{ $item->criterion_name }}</td>
                                            <td>{{ $item->result }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        @forelse ($inspection->qualityDefects as $defect)
                            <div class="bg-light rounded p-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <span>
                                        <a href="{{ route('quality.defects.show', $defect) }}">Defect #{{ $defect->id }}</a>
                                        &mdash; {{ $defect->defect_type }} ({{ $defect->severity }})
                                    </span>
                                    <span class="badge bg-secondary">{{ $defect->status }}</span>
                                </div>
                                <small class="text-muted">Detected by {{ $defect->detectedBy->name }}</small>

                                @forelse ($defect->correctiveActions as $action)
                                    <div class="mt-2 ps-3 border-start">
                                        <a href="{{ route('quality.corrective-actions.show', $action) }}">Corrective Action #{{ $action->id }}</a>
                                        <span class="badge bg-secondary">{{ $action->status }}</span>
                                        <div class="text-muted" style="font-size: 0.85rem;">
                                            Responsible: {{ $action->responsibleUser->name }}
                                            @if ($action->validatedBy)
                                                &mdash; Validated by {{ $action->validatedBy->name }} on {{ $action->validated_at->format('Y-m-d') }}
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted mt-1" style="font-size: 0.85rem;">No corrective action yet.</div>
                                @endforelse
                            </div>
                        @empty
                            @if ($inspection->result === 'FAIL')
                                <div class="text-muted" style="font-size: 0.85rem;">No defect declared yet for this failed inspection.</div>
                            @endif
                        @endforelse
                    </div>
                @empty
                    <p class="text-muted mb-0">Not inspected yet.</p>
                @endforelse
            </div>
        </div>
    @empty
        <p class="text-muted">No production recorded yet for this order.</p>
    @endforelse

    <h4 class="fw-bold mb-3 mt-5">Activity Timeline</h4>
    <div class="card">
        <table class="table table-striped mb-0">
            <thead>
                <tr><th>When</th><th>User</th><th>Action</th><th>Details</th></tr>
            </thead>
            <tbody>
                @forelse ($timeline as $entry)
                    <tr>
                        <td>{{ $entry->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $entry->user->name ?? 'System' }}</td>
                        <td>{{ $entry->action }}</td>
                        <td>{{ $entry->description ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No activity recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
