<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kelola Satker</title>

    <!-- Google Font Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #4f46e5, #3b82f6);
            color: #fff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .container {
            background: rgba(255, 255, 255, 0.12);
            padding: 40px;
            border-radius: 14px;
            max-width: 420px;
            width: 90%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        h1 {
            margin-bottom: 12px;
            font-size: 32px;
            font-weight: 600;
        }

        p {
            font-size: 15px;
            line-height: 1.7;
            margin-bottom: 30px;
            font-weight: 400;
        }

        a.button {
            display: inline-block;
            padding: 12px 30px;
            background: #22c55e;
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        a.button:hover {
            background: #16a34a;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Selamat Datang</h1>
        <p>
            Ini adalah <strong>sistem satker kelola</strong>
        </p>

        <a href="/login" class="button">Login</a>
    </div>

</body>
</html>
