@extends('layouts.app')

@section('title', 'Corrective Action #' . $action->id)

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h2 class="fw-bold mb-1">Corrective Action #{{ $action->id }}</h2>
            <span class="badge bg-secondary">{{ $action->status }}</span>
        </div>
        <div>
            @can('validate', $action)
                <form method="POST" action="{{ route('quality.corrective-actions.validate', $action) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">Validate</button>
                </form>
            @endcan
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p class="text-muted mb-4">
        Defect <a href="{{ route('quality.defects.show', $action->qualityDefect) }}">#{{ $action->qualityDefect->id }}</a>
        &mdash; Order {{ $action->qualityDefect->qualityInspection->productionRecord->productionOrder->order_number }}
        &mdash; {{ $action->qualityDefect->qualityInspection->productionRecord->productionOrder->product->name }}
    </p>

    <div class="row g-4 mb-4">
        <div class="col-md-4"><div class="card p-3"><small class="text-muted">Responsible</small><div class="fw-bold">{{ $action->responsibleUser->name }}</div></div></div>
        <div class="col-md-4"><div class="card p-3"><small class="text-muted">Requires Reinspection</small><div class="fw-bold">{{ $action->requires_reinspection ? 'Yes' : 'No' }}</div></div></div>
        <div class="col-md-4"><div class="card p-3"><small class="text-muted">Validated By</small><div class="fw-bold">{{ $action->validatedBy->name ?? '—' }}</div></div></div>
    </div>

    <div class="card p-3 mb-4">
        <small class="text-muted">Root Cause</small>
        <div>{{ $action->root_cause ?? '—' }}</div>
    </div>

    <div class="card p-3 mb-4">
        <small class="text-muted">Action Taken</small>
        <div>{{ $action->action_description }}</div>
    </div>

    @can('update', $action)
        <h4 class="fw-bold mb-3">Update Status</h4>
        <form method="POST" action="{{ route('quality.corrective-actions.update', $action) }}" class="card p-4" style="max-width: 420px;">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    @foreach (['OPEN', 'IN_PROGRESS', 'DONE'] as $status)
                        <option value="{{ $status }}" @selected($action->status === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-outline-dark">Save Status</button>
        </form>
        <p class="text-muted mt-2" style="font-size: 0.85rem;">
            Mark as <strong>DONE</strong> once the fix is applied. Validation then requires a passing reinspection on the same production record.
        </p>
    @endcan
@endsection