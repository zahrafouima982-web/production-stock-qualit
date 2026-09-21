@extends('layouts.app')

@section('title', 'New Component')

@section('content')
    <h2 class="fw-bold mb-4">New Component</h2>

    <form method="POST" action="{{ route('components.store') }}" class="card p-4" style="max-width: 560px;">
        @csrf
        @include('components._form')
        <button type="submit" class="btn btn-dark">Create Component</button>
    </form>

    <p class="text-muted mt-3" style="font-size: 0.85rem;">
        New components always start at 0 stock. Use "Record Movement" afterward to add initial quantity.
    </p>
@endsection
