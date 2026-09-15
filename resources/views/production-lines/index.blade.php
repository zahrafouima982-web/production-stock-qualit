@extends('layouts.app')

@section('title', 'Production Lines')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Production Lines</h2>
        @can('create', \App\Models\ProductionLine::class)
            <a href="{{ route('production-lines.create') }}" class="btn btn-dark">+ New Line</a>
        @endcan
    </div>

    <div class="card">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($productionLines as $line)
                    <tr>
                        <td>{{ $line->code }}</td>
                        <td>{{ $line->name }}</td>
                        <td>{{ $line->location ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $line->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $line->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-end">
                            @can('update', $line)
                                <a href="{{ route('production-lines.edit', $line) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            @endcan
                            @can('delete', $line)
                                <form method="POST" action="{{ route('production-lines.destroy', $line) }}" class="d-inline" onsubmit="return confirm('Archive this line?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Archive</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No production lines yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $productionLines->links() }}</div>
@endsection