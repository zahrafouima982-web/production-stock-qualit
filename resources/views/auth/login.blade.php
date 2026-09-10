@extends('layouts.guest')

@section('title', 'Sign In - PQTMS')

@section('content')
    <h5 class="text-center mb-4 text-secondary">Sign in to your account</h5>

    @session('status')
        <div class="alert alert-success">{{ $value }}</div>
    @endsession

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                class="form-control @error('email') is-invalid @enderror"
                required
                autofocus
                autocomplete="username"
            >
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                class="form-control @error('password') is-invalid @enderror"
                required
                autocomplete="current-password"
            >
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="remember" id="remember" class="form-check-input">
            <label for="remember" class="form-check-label">Remember me</label>
        </div>

        <button type="submit" class="btn btn-industrial w-100 text-white">Sign in</button>
    </form>

    <p class="text-center text-muted mt-4 mb-0" style="font-size: 0.8rem;">
        Internal use only &mdash; Production, Quality &amp; Traceability Management System
    </p>
@endsection
