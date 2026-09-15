@extends('layouts.app')

@section('title', 'New Production Line')

@section('content')
    <h2 class="fw-bold mb-4">New Production Line</h2>

    <form method="POST" action="{{ route('production-lines.store') }}" class="card p-4" style="max-width: 560px;">
        @csrf
        @include('production-lines._form')
        <button type="submit" class="btn btn-dark">Create Line</button>
    </form>
@endsection