@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
    <h2 class="fw-bold mb-4">Edit Product</h2>

    <form method="POST" action="{{ route('products.update', $product) }}" class="card p-4" style="max-width: 560px;">
        @csrf
        @method('PUT')
        @include('products._form')
        <button type="submit" class="btn btn-dark">Save Changes</button>
    </form>
@endsection