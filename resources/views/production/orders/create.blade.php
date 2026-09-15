@extends('layouts.app')

@section('title', 'New Production Order')

@section('content')
    <h2 class="fw-bold mb-4">New Production Order</h2>

    <form method="POST" action="{{ route('production.orders.store') }}" class="card p-4" style="max-width: 560px;">
        @csrf
        @include('production.orders._form')
        <button type="submit" class="btn btn-dark">Create Order</button>
    </form>
@endsection