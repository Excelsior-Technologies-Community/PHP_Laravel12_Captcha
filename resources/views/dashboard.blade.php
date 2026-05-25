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
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #020617, #0f172a, #1e293b);
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .dashboard-card {
            width: 100%;
            max-width: 650px;
            background: rgba(255, 255, 255, .08);
            backdrop-filter: blur(18px);
            border-radius: 25px;
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, .1);
            box-shadow: 0 10px 40px rgba(0, 0, 0, .35);
            text-align: center;
        }

        .profile-circle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #3b82f6);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: auto;
            font-size: 35px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .title {
            color: #22c55e;
            font-size: 34px;
            font-weight: 700;
        }

        .user-name {
            color: #fff;
            font-size: 26px;
            font-weight: 600;
            margin-top: 10px;
        }

        .mobile {
            color: #cbd5e1;
        }

        .info-card {
            background: rgba(255, 255, 255, .08);
            padding: 18px;
            border-radius: 16px;
            margin-top: 25px;
            color: #fff;
        }

        .logout-btn {
            height: 55px;
            border: none;
            border-radius: 14px;
            margin-top: 25px;
            font-size: 18px;
            font-weight: 600;
            width: 100%;
            color: white;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            transition: .3s;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(239, 68, 68, .4);
        }
    </style>

</head>

<body>

    <div class="dashboard-card">

        <div class="profile-circle">
            {{ strtoupper(substr(Auth::user()->name,0,1)) }}
        </div>

        <h2 class="title">
            Welcome Dashboard
        </h2>

        <div class="user-name">
            {{ Auth::user()->name }}
        </div>

        <div class="mobile">
            {{ Auth::user()->mobile }}
        </div>


        <div class="info-card">

            <h5>
                Last Login
            </h5>

            <p class="mb-0">

                {{ Auth::user()->last_login ? Auth::user()->last_login->format('d M Y h:i A') : 'First Login' }}

            </p>

        </div>


        <form action="{{ route('logout') }}" method="POST">

            @csrf

            <button class="logout-btn">

                Logout

            </button>

        </form>

    </div>

</body>

</html>