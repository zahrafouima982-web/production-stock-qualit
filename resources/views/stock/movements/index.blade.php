@extends('layouts.app')

@section('title', 'Stock Movements')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Stock Movements</h2>
        @can('create', \App\Models\StockMovement::class)
            <a href="{{ route('stock.movements.create') }}" class="btn btn-dark">+ Record Movement</a>
        @endcan
    </div>

    <div class="card">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Component</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>Reference</th>
                    <th>Performed By</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movements as $movement)
                    <tr>
                        <td>{{ $movement->performed_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $movement->component->code }} &mdash; {{ $movement->component->name }}</td>
                        <td>
                            <span class="badge {{ $movement->type === 'IN' ? 'bg-success' : 'bg-danger' }}">{{ $movement->type }}</span>
                        </td>
                        <td>{{ $movement->quantity }} {{ $movement->component->unit_of_measure }}</td>
                        <td>{{ $movement->reference ?? '—' }}</td>
                        <td>{{ $movement->performedBy->name }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No movements recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $movements->links() }}</div>
@endsection
