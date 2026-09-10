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
        @yield('content')
    </div>
</body>
</html>
