<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width:600px">

    <div class="card shadow border-0 p-5 text-center">

        <h2 class="text-success mb-3">
            Welcome Dashboard
        </h2>

        <h5>
            {{ Auth::user()->name }}
        </h5>

        <p>
            {{ Auth::user()->mobile }}
        </p>

        <form action="{{ route('logout') }}" method="POST">

            @csrf

            <button class="btn btn-danger">
                Logout
            </button>

        </form>

    </div>

</div>

</body>
</html>