@extends('layouts.app')

@section('title', 'Record Stock Movement')

@section('content')
    <h2 class="fw-bold mb-4">Record Stock Movement</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('stock.movements.store') }}" class="card p-4" style="max-width: 560px;">
        @csrf

        <div class="mb-3">
            <label for="component_id" class="form-label">Component</label>
            <select name="component_id" id="component_id" class="form-select" required>
                <option value="">Select a component...</option>
                @foreach ($components as $component)
                    <option value="{{ $component->id }}" @selected(old('component_id') == $component->id)>
                        {{ $component->code }} &mdash; {{ $component->name }} (current: {{ $component->current_quantity }} {{ $component->unit_of_measure }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="type" class="form-label">Movement Type</label>
            <select name="type" id="type" class="form-select" required>
                <option value="IN" @selected(old('type') === 'IN')>IN (receiving stock)</option>
                <option value="OUT" @selected(old('type') === 'OUT')>OUT (consuming stock)</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" step="0.01" min="0.01" name="quantity" id="quantity" class="form-control"
                value="{{ old('quantity') }}" required>
        </div>

        <div class="mb-3">
            <label for="reference" class="form-label">Reference</label>
            <input type="text" name="reference" id="reference" class="form-control" placeholder="e.g. supplier invoice, linked PO"
                value="{{ old('reference') }}">
        </div>

        <div class="mb-3">
            <label for="performed_at" class="form-label">Date</label>
            <input type="datetime-local" name="performed_at" id="performed_at" class="form-control"
                value="{{ old('performed_at', now()->format('Y-m-d\TH:i')) }}">
        </div>

        <button type="submit" class="btn btn-dark">Save Movement</button>
    </form>
@endsection
