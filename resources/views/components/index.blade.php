@extends('layouts.app')

@section('title', 'Components')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Components</h2>
        @can('create', \App\Models\Component::class)
            <a href="{{ route('components.create') }}" class="btn btn-dark">+ New Component</a>
        @endcan
    </div>

    <div class="card">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Unit</th>
                    <th>Current Qty</th>
                    <th>Safety Threshold</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($components as $component)
                    <tr>
                        <td>{{ $component->code }}</td>
                        <td>{{ $component->name }}</td>
                        <td>{{ $component->unit_of_measure }}</td>
                        <td>{{ $component->current_quantity }}</td>
                        <td>{{ $component->safety_stock_threshold }}</td>
                        <td>
                            @php
                                $badge = match ($component->stock_status) {
                                    'NORMAL' => 'bg-success',
                                    'CRITICAL' => 'bg-warning text-dark',
                                    'OUT_OF_STOCK' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badge }}">{{ $component->stock_status }}</span>
                        </td>
                        <td class="text-end">
                            @can('update', $component)
                                <a href="{{ route('components.edit', $component) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            @endcan
                            @can('delete', $component)
                                <form method="POST" action="{{ route('components.destroy', $component) }}" class="d-inline" onsubmit="return confirm('Archive this component?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Archive</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No components yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $components->links() }}</div>
@endsection
