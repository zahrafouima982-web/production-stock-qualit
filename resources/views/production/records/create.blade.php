@extends('layouts.app')

@section('title', 'Record Production')

@section('content')
    <h2 class="fw-bold mb-4">Record Production &mdash; Order {{ $order->order_number }}</h2>

    <form method="POST" action="{{ route('production.orders.records.store', $order) }}" class="card p-4" style="max-width: 560px;">
        @csrf
        @include('production.records._form')
        <button type="submit" class="btn btn-dark">Save Record</button>
    </form>
@endsection