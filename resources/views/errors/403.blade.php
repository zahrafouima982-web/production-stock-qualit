<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - PQTMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="text-center px-3">
        <h1 class="display-4 fw-bold text-warning">403</h1>
        <p class="lead">
            {{ $exception->getMessage() ?: 'You do not have permission to access this page.' }}
        </p>
        <a href="{{ url('/') }}" class="btn btn-warning mt-3">Return to Dashboard</a>
    </div>
</body>
</html>
