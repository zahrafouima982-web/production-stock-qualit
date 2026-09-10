<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PQTMS')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, sans-serif;
            padding: 1rem;
        }
        .auth-card {
            background: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            max-width: 420px;
            width: 100%;
            overflow: hidden;
        }
        .brand-strip {
            background: #0f172a;
            color: #f8b400;
            padding: 1.75rem 1.5rem;
            text-align: center;
        }
        .brand-strip small {
            display: block;
            color: #94a3b8;
            letter-spacing: 0.05em;
            margin-top: 0.25rem;
        }
        .btn-industrial {
            background-color: #0f172a;
            border-color: #0f172a;
        }
        .btn-industrial:hover {
            background-color: #1e293b;
            border-color: #1e293b;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="brand-strip">
            <h4 class="mb-0 fw-bold">PQTMS</h4>
            <small>PRODUCTION &middot; QUALITY &middot; TRACEABILITY</small>
        </div>
        <div class="p-4 p-md-5">
            @yield('content')
        </div>
    </div>
</body>
</html>
