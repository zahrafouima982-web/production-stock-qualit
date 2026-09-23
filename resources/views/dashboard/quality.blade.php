@extends('layouts.app')

@section('title', 'Quality Dashboard')

@section('content')
    <h2 class="fw-bold mb-4">Quality Dashboard</h2>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card p-3">
                <small class="text-muted">PASS Rate</small>
                <div class="fw-bold fs-4">{{ $inspection_totals['pass_rate'] !== null ? $inspection_totals['pass_rate'] . '%' : '—' }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <small class="text-muted">PASS / FAIL</small>
                <div class="fw-bold fs-4"><span class="text-success">{{ $inspection_totals['pass'] }}</span> / <span class="text-danger">{{ $inspection_totals['fail'] }}</span></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <small class="text-muted">Open Defects</small>
                <div class="fw-bold fs-4 {{ $open_defects_count > 0 ? 'text-danger' : '' }}">{{ $open_defects_count }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <small class="text-muted">Pending Validation</small>
                <div class="fw-bold fs-4">{{ $pending_validation_count }}</div>
            </div>
        </div>
    </div>

    <h5 class="fw-bold mb-3">Recent Inspections</h5>
    <div class="card">
        <table class="table table-striped mb-0">
            <thead><tr><th>Order</th><th>Product</th><th>Inspector</th><th>Inspected At</th><th>Result</th></tr></thead>
            <tbody>
                @forelse ($recent_inspections as $inspection)
                    <tr>
                        <td><a href="{{ route('quality.inspections.show', $inspection) }}">{{ $inspection->productionRecord->productionOrder->order_number }}</a></td>
                        <td>{{ $inspection->productionRecord->productionOrder->product->name }}</td>
                        <td>{{ $inspection->inspector->name }}</td>
                        <td>{{ $inspection->inspected_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $inspection->result === 'PASS' ? 'bg-success' : ($inspection->result === 'FAIL' ? 'bg-danger' : 'bg-secondary') }}">
                                {{ $inspection->result }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No inspections recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        <a href="{{ route('quality.inspections.index') }}" class="btn btn-outline-dark">View all Inspections &rarr;</a>
    </div>
@endsection
