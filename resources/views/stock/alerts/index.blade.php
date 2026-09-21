@extends('layouts.app')

@section('title', 'Stock Alerts')

@section('content')
    <h2 class="fw-bold mb-4">Stock Alerts</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Component</th>
                    <th>Status</th>
                    <th>Triggered At</th>
                    <th>Resolved At</th>
                    <th>Resolved By</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($alerts as $alert)
                    <tr>
                        <td>{{ $alert->component->code }} &mdash; {{ $alert->component->name }}</td>
                        <td>
                            <span class="badge {{ $alert->status === 'ACTIVE' ? 'bg-danger' : 'bg-success' }}">{{ $alert->status }}</span>
                        </td>
                        <td>{{ $alert->triggered_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $alert->resolved_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        <td>{{ $alert->resolvedBy->name ?? ($alert->resolved_at ? 'System (auto)' : '—') }}</td>
                        <td class="text-end">
                            @can('resolve', $alert)
                                <form method="POST" action="{{ route('stock.alerts.resolve', $alert) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success">Resolve</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No alerts.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $alerts->links() }}</div>
@endsection
