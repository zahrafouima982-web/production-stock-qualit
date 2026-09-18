<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - PQTMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color:#0f172a;">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold">PQTMS</span>

            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    @auth
                        @if (auth()->user()->isAdmin())
                            <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                        @endif

                        @if (auth()->user()->isAdmin() || auth()->user()->isProductionManager() || auth()->user()->isQualityController())
                            <li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}">Products</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('production-lines.index') }}">Production Lines</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('production.orders.index') }}">Production Orders</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('quality.inspections.index') }}">Quality Inspections</a></li>
                        @endif

                        @if (auth()->user()->isProductionManager())
                            <li class="nav-item"><a class="nav-link" href="{{ route('production.dashboard') }}">Production Dashboard</a></li>
                        @endif

                        @if (auth()->user()->isQualityController())
                            <li class="nav-item"><a class="nav-link" href="{{ route('quality.dashboard') }}">Quality Dashboard</a></li>
                        @endif

                        @if (auth()->user()->isStockManager())
                            <li class="nav-item"><a class="nav-link" href="{{ route('stock.dashboard') }}">Stock Dashboard</a></li>
                        @endif
                    @endauth
                </ul>
            </div>

            <div class="d-flex align-items-center text-white">
                <span class="me-3">
                    {{ auth()->user()->name }}
                    <span class="badge bg-warning text-dark ms-1">{{ auth()->user()->role->name }}</span>
                </span>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @yield('content')
    </div>
</body>
</html>
