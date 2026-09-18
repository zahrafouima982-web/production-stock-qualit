@extends('layouts.app')

@section('title', 'New Corrective Action')

@section('content')
    <h2 class="fw-bold mb-1">New Corrective Action</h2>
    <p class="text-muted mb-4">
        For defect <a href="{{ route('quality.defects.show', $defect) }}">#{{ $defect->id }}</a>
        ({{ $defect->defect_type }}, {{ $defect->severity }})
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

    <form method="POST" action="{{ route('quality.defects.corrective-action.store', $defect) }}" class="card p-4" style="max-width: 620px;">
        @csrf

        <div class="mb-3">
            <label for="root_cause" class="form-label">Root Cause</label>
            <textarea name="root_cause" id="root_cause" class="form-control" rows="2">{{ old('root_cause') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="action_description" class="form-label">Action Taken</label>
            <textarea name="action_description" id="action_description" class="form-control" rows="3" required>{{ old('action_description') }}</textarea>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="requires_reinspection" id="requires_reinspection" class="form-check-input" value="1"
                {{ old('requires_reinspection') ? 'checked' : '' }}>
            <label for="requires_reinspection" class="form-check-label">Requires reinspection before this can be validated</label>
        </div>

        <button type="submit" class="btn btn-dark">Create Corrective Action</button>
    </form>
@endsection