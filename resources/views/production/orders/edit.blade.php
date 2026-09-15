@extends('layouts.app')

@section('title', 'Edit Production Order')

@section('content')
    <h2 class="fw-bold mb-4">Edit Order {{ $order->order_number }}</h2>

    <form method="POST" action="{{ route('production.orders.update', $order) }}" class="card p-4" style="max-width: 560px;">
        @csrf
        @method('PUT')
        @include('production.orders._form')
        <button type="submit" class="btn btn-dark">Save Changes</button>
    </form>
@endsection