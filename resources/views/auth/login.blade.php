<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            background:linear-gradient(135deg,#020617,#0f172a,#1e293b);
            font-family:Arial,sans-serif;
        }

        .auth-card{
            width:100%;
            max-width:470px;
            background:rgba(255,255,255,.08);
            backdrop-filter:blur(18px);
            border-radius:25px;
            padding:40px;
            border:1px solid rgba(255,255,255,.1);
            box-shadow:0 10px 40px rgba(0,0,0,.35);
        }

        .title{
            color:#fff;
            font-size:34px;
            font-weight:700;
            text-align:center;
            margin-bottom:8px;
        }

        .subtitle{
            color:#cbd5e1;
            text-align:center;
            margin-bottom:30px;
        }

        .form-control{
            height:55px;
            border-radius:14px;
            border:none;
            background:rgba(255,255,255,.12);
            color:#fff;
            padding-left:18px;
        }

        .form-control::placeholder{
            color:#cbd5e1;
        }

        .form-control:focus{
            background:rgba(255,255,255,.18);
            color:#fff;
            border:1px solid #38bdf8;
            box-shadow:none;
        }

        .captcha-box{
            background:#fff;
            border-radius:16px;
            padding:14px;
            margin-bottom:20px;
        }

        .captcha-text{
            font-size:30px;
            font-weight:800;
            color:#0f172a;
            letter-spacing:3px;
        }

        .refresh-btn{
            border:none;
            width:45px;
            height:45px;
            border-radius:12px;
            background:#0f172a;
            color:#fff;
            font-size:22px;
            transition:.3s;
        }

        .refresh-btn:hover{
            transform:rotate(180deg);
            background:#1e293b;
        }

        .btn-auth{
            height:55px;
            border:none;
            border-radius:14px;
            background:linear-gradient(135deg,#10b981,#059669);
            color:#fff;
            font-size:18px;
            font-weight:600;
            transition:.3s;
        }

        .btn-auth:hover{
            transform:translateY(-2px);
            box-shadow:0 10px 20px rgba(16,185,129,.4);
        }

        .bottom-link{
            text-align:center;
            margin-top:22px;
        }

        .bottom-link a{
            color:#38bdf8;
            text-decoration:none;
            font-weight:600;
        }

        .alert{
            border-radius:14px;
        }

    </style>

</head>

<body>

<div class="auth-card">

    <h1 class="title">Welcome Back</h1>

    <p class="subtitle">
        Login to continue
    </p>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>
        </div>
    @endif

    <form action="{{ route('login.store') }}" method="POST">

        @csrf

        <div class="mb-3">

            <input
                type="text"
                name="mobile"
                value="{{ old('mobile') }}"
                autocomplete="username"
                class="form-control"
                placeholder="Mobile Number"
                required>

        </div>

        <div class="mb-4">

            <input
                type="password"
                name="password"
                autocomplete="current-password"
                class="form-control"
                placeholder="Password"
                required>

        </div>


        <div class="captcha-box">

            <div class="d-flex justify-content-center align-items-center gap-3">

                <div class="captcha-text" id="captcha">

                    {{ session('captcha_question') }} = ?

                </div>

                <button
                    type="button"
                    class="refresh-btn"
                    id="refreshCaptcha">

                    ↻

                </button>

            </div>

        </div>


        <div class="mb-4">

            <input
                type="text"
                name="captcha"
                class="form-control"
                placeholder="Solve Captcha"
                required>

        </div>

        <button class="btn btn-auth w-100">

            Login

        </button>

    </form>

    <div class="bottom-link">

        <a href="{{ route('register') }}">
            Create new account
        </a>

    </div>

</div>


<script>

document.getElementById('refreshCaptcha').addEventListener('click',function(){

    fetch('/refresh-captcha')

    .then(response=>response.json())

    .then(data=>{

        document.getElementById('captcha').innerHTML =
        data.captcha + ' = ?';

    });

});

</script>

</body>
</html>