@extends('layouts.app')

@section('title', 'New Product')

@section('content')
    <h2 class="fw-bold mb-4">New Product</h2>

    <form method="POST" action="{{ route('products.store') }}" class="card p-4" style="max-width: 560px;">
        @csrf
        @include('products._form')
        <button type="submit" class="btn btn-dark">Create Product</button>
    </form>
@endsection