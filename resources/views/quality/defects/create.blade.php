@extends('layouts.app')

@section('title', 'Declare Quality Defect')

@section('content')
    <h2 class="fw-bold mb-1">Declare Quality Defect</h2>
    <p class="text-muted mb-4">
        Inspection <a href="{{ route('quality.inspections.show', $inspection) }}">#{{ $inspection->id }}</a>
        &mdash; {{ $inspection->productionRecord->productionOrder->product->name ?? '' }}
    </p>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('quality.inspections.defects.store', $inspection) }}" class="card p-4" style="max-width: 560px;">
        @csrf

        <div class="mb-3">
            <label for="defect_type" class="form-label">Defect Type</label>
            <select name="defect_type" id="defect_type" class="form-select" required>
                <option value="">Select a type...</option>
                @foreach (['DIMENSIONAL', 'ELECTRICAL', 'COSMETIC', 'ASSEMBLY', 'OTHER'] as $type)
                    <option value="{{ $type }}" @selected(old('defect_type') === $type)>{{ $type }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="severity" class="form-label">Severity</label>
            <select name="severity" id="severity" class="form-select" required>
                <option value="">Select severity...</option>
                @foreach (['MINOR', 'MAJOR', 'CRITICAL'] as $severity)
                    <option value="{{ $severity }}" @selected(old('severity') === $severity)>{{ $severity }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" rows="3" required>{{ old('description') }}</textarea>
        </div>

        <button type="submit" class="btn btn-dark">Record Defect</button>
    </form>
@endsection