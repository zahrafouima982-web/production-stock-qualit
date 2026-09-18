@extends('layouts.app')

@section('title', 'Quality Inspections')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Quality Inspections</h2>
        @can('create', \App\Models\QualityInspection::class)
            <a href="{{ route('quality.inspections.create') }}" class="btn btn-dark">+ New Inspection</a>
        @endcan
    </div>

    <div class="card">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Product</th>
                    <th>Inspector</th>
                    <th>Inspected At</th>
                    <th>Result</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($inspections as $inspection)
                    <tr>
                        <td>{{ $inspection->productionRecord->productionOrder->order_number }}</td>
                        <td>{{ $inspection->productionRecord->productionOrder->product->name }}</td>
                        <td>{{ $inspection->inspector->name }}</td>
                        <td>{{ $inspection->inspected_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        <td>
                            @php
                                $badge = match ($inspection->result) {
                                    'PASS' => 'bg-success',
                                    'FAIL' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badge }}">{{ $inspection->result }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('quality.inspections.show', $inspection) }}" class="btn btn-sm btn-outline-secondary">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No inspections recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $inspections->links() }}</div>
@endsection