@extends('layouts.app')

@section('title', 'Defect #' . $defect->id)

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h2 class="fw-bold mb-1">Defect #{{ $defect->id }}</h2>
            <span class="badge bg-secondary">{{ $defect->status }}</span>
        </div>
        @can('create', \App\Models\CorrectiveAction::class)
            @if ($defect->correctiveActions->isEmpty())
                <a href="{{ route('quality.defects.corrective-action.create', $defect) }}" class="btn btn-dark">+ Add Corrective Action</a>
            @endif
        @endcan
    </div>

    <p class="text-muted mb-4">
        From inspection <a href="{{ route('quality.inspections.show', $defect->qualityInspection) }}">#{{ $defect->qualityInspection->id }}</a>
        on order {{ $defect->qualityInspection->productionRecord->productionOrder->order_number }}
        &mdash; {{ $defect->qualityInspection->productionRecord->productionOrder->product->name }}
    </p>

    <div class="row g-4 mb-4">
        <div class="col-md-4"><div class="card p-3"><small class="text-muted">Type</small><div class="fw-bold">{{ $defect->defect_type }}</div></div></div>
        <div class="col-md-4"><div class="card p-3"><small class="text-muted">Severity</small><div class="fw-bold">{{ $defect->severity }}</div></div></div>
        <div class="col-md-4"><div class="card p-3"><small class="text-muted">Detected By</small><div class="fw-bold">{{ $defect->detectedBy->name }}</div></div></div>
    </div>

    <div class="card p-3 mb-4">
        <small class="text-muted">Description</small>
        <div>{{ $defect->description }}</div>
    </div>

    <h4 class="fw-bold mb-3">Corrective Actions</h4>
    <div class="card">
        <table class="table table-striped mb-0">
            <thead>
                <tr><th>Status</th><th>Responsible</th><th>Requires Reinspection</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse ($defect->correctiveActions as $action)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $action->status }}</span></td>
                        <td>{{ $action->responsibleUser->name }}</td>
                        <td>{{ $action->requires_reinspection ? 'Yes' : 'No' }}</td>
                        <td class="text-end">
                            <a href="{{ route('quality.corrective-actions.show', $action) }}" class="btn btn-sm btn-outline-secondary">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No corrective action recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection