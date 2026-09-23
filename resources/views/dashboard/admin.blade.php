@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <h2 class="fw-bold mb-4">Admin Dashboard</h2>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card p-3">
                <small class="text-muted">Orders &mdash; In Progress</small>
                <div class="fw-bold fs-4">{{ $orders_by_status['IN_PROGRESS'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <small class="text-muted">PASS Rate</small>
                <div class="fw-bold fs-4">{{ $inspection_totals['pass_rate'] !== null ? $inspection_totals['pass_rate'] . '%' : '—' }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <small class="text-muted">Active Stock Alerts</small>
                <div class="fw-bold fs-4 {{ $active_stock_alerts_count > 0 ? 'text-danger' : '' }}">{{ $active_stock_alerts_count }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <small class="text-muted">Corrective Actions Pending</small>
                <div class="fw-bold fs-4">{{ $pending_corrective_actions_count }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card p-3 h-100">
                <h5 class="fw-bold">Production Orders by Status</h5>
                <table class="table table-sm mb-0">
                    <tbody>
                        @foreach (['PLANNED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'] as $status)
                            <tr>
                                <td>{{ $status }}</td>
                                <td class="text-end fw-bold">{{ $orders_by_status[$status] ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3 h-100">
                <h5 class="fw-bold">Inspections</h5>
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr><td>PASS</td><td class="text-end fw-bold text-success">{{ $inspection_totals['pass'] }}</td></tr>
                        <tr><td>FAIL</td><td class="text-end fw-bold text-danger">{{ $inspection_totals['fail'] }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <h5 class="fw-bold mb-3">Recent Activity</h5>
    <div class="card">
        <table class="table table-striped mb-0">
            <thead><tr><th>When</th><th>User</th><th>Action</th><th>Details</th></tr></thead>
            <tbody>
                @forelse ($recent_activity as $entry)
                    <tr>
                        <td>{{ $entry->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $entry->user->name ?? 'System' }}</td>
                        <td>{{ $entry->action }}</td>
                        <td>{{ $entry->description ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No activity recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
