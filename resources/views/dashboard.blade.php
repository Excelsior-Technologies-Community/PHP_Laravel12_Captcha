@php
    use Illuminate\Support\Str;
@endphp

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #020617, #0f172a, #1e293b);
            font-family: Arial, sans-serif;
            padding: 40px;
            color: white;
        }

        .dashboard-card {
            background: rgba(255, 255, 255, .08);
            backdrop-filter: blur(18px);
            border-radius: 25px;
            padding: 35px;
            border: 1px solid rgba(255, 255, 255, .1);
            box-shadow: 0 10px 40px rgba(0, 0, 0, .35);
            margin-bottom: 30px;
        }

        .profile-circle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #3b82f6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            font-weight: 700;
            margin: auto;
            color: white;
        }

        .table {
            color: white;
        }

        .table td,
        .table th {
            background: transparent !important;
            color: white !important;
        }

        .search-box {
            height: 50px;
            border-radius: 12px;
            background: rgba(255, 255, 255, .1);
            border: none;
            color: white;
            padding-left: 15px;
        }

        .search-box:focus {
            outline: none;
            box-shadow: none;
            background: rgba(255, 255, 255, .15);
            color: white;
        }

        .logout-btn {
            border: none;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            width: 100%;
            font-weight: 600;
        }

        .pagination svg {
            width: 18px;
        }

        .pagination .page-link {
            background: rgba(255, 255, 255, .08);
            border: none;
            color: white;
            margin: 0 4px;
            border-radius: 10px;
        }

        .pagination .page-link:hover {
            background: #3b82f6;
            color: white;
        }

        .pagination .active .page-link {
            background: #10b981;
            color: white;
        }
        
    </style>

</head>

<body>

    <div class="container">

        <div class="dashboard-card text-center">

            <div class="profile-circle">

                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}

            </div>

            <h2 class="mt-3">
                Welcome {{ Auth::user()->name }}
            </h2>

            <p>
                {{ Auth::user()->mobile }}
            </p>

            <p>
                Last Login:
                {{ Auth::user()->last_login
    ? Auth::user()->last_login->format('d M Y h:i A')
    : 'First Login' }}
            </p>

            <form action="{{ route('logout') }}" method="POST">

                @csrf

                <button class="logout-btn mt-3">

                    Logout

                </button>

            </form>

        </div>

        <div class="dashboard-card">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <h3>
                    Login Activity
                </h3>

                <form method="GET" action="{{ route('dashboard') }}">

                    <input type="text" name="search" value="{{ request('search') }}" class="search-box"
                        placeholder="Search IP or Browser">

                </form>

            </div>

            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>IP Address</th>

                            <th>Browser</th>

                            <th>Login Time</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($activities as $activity)

                            <tr>

                                <td>
                                    {{ $activity->id }}
                                </td>

                                <td>
                                    {{ $activity->ip_address }}
                                </td>

                                <td>

                                    {{ Str::limit($activity->user_agent, 50) }}

                                </td>

                                <td>

                                    {{ \Carbon\Carbon::parse($activity->login_time)->format('d M Y h:i A') }}

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="4" class="text-center">

                                    No Activity Found

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-4">

                <div class="d-flex justify-content-center">

                    {{ $activities->onEachSide(1)->links('pagination::bootstrap-5') }}

                </div>

            </div>

        </div>

    </div>

    <!-- AUTO LOGOUT FORM -->

    <form id="autoLogoutForm" action="{{ route('logout') }}" method="POST">

        @csrf

    </form>

    <script>

        let timeout;

        function resetTimer() {

            clearTimeout(timeout);

            timeout = setTimeout(() => {

                alert('Session Expired');

                document.getElementById('autoLogoutForm').submit();

            }, 120000);

        }

        window.onload = resetTimer;

        document.onmousemove = resetTimer;

        document.onkeypress = resetTimer;

    </script>

</body>

</html>