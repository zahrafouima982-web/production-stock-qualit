@extends('layouts.app')

@section('title', 'Edit Component')

@section('content')
    <h2 class="fw-bold mb-4">Edit Component</h2>

    <div class="row g-4 mb-4">
        <div class="col-md-4"><div class="card p-3"><small class="text-muted">Current Quantity</small><div class="fw-bold">{{ $component->current_quantity }} {{ $component->unit_of_measure }}</div></div></div>
        <div class="col-md-8"><div class="card p-3"><small class="text-muted">Status</small><div class="fw-bold">{{ $component->stock_status }}</div></div></div>
    </div>

    <form method="POST" action="{{ route('components.update', $component) }}" class="card p-4" style="max-width: 560px;">
        @csrf
        @method('PUT')
        @include('components._form')
        <button type="submit" class="btn btn-dark">Save Changes</button>
    </form>

    <p class="text-muted mt-3" style="font-size: 0.85rem;">
        Current quantity can't be edited here — it's derived from stock movements only.
        <a href="{{ route('stock.movements.create') }}">Record a movement</a> to change it.
    </p>
@endsection
