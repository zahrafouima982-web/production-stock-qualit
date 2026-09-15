@extends('layouts.app')

@section('title', 'Edit Production Record')

@section('content')
    <h2 class="fw-bold mb-4">Edit Record &mdash; Order {{ $record->productionOrder->order_number }}</h2>

    <form method="POST" action="{{ route('production.records.update', $record) }}" class="card p-4" style="max-width: 560px;">
        @csrf
        @method('PUT')
        @include('production.records._form')
        <button type="submit" class="btn btn-dark">Save Changes</button>
    </form>
@endsection