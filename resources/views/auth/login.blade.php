<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SATKER</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F8FAFC;
        }
    </style>
</head>

<body class="flex flex-col min-h-screen">

    <div class="flex-grow flex flex-col items-center justify-center p-4">
        <div class="text-center mb-8">
            <div class="bg-[#1D4076] w-16 h-16 rounded-xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                <i class="fas fa-shield-halved text-white text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">SATKER</h1>
            <p class="text-sm text-gray-500 font-medium">Sistem Manajemen Satuan Kerja</p>
            <p class="text-xs text-gray-400">Biro SDM - Kementerian Agama RI</p>
        </div>

        <div class="bg-white w-full max-w-md rounded-2xl shadow-sm border border-gray-100 p-10">
            <div class="text-center mb-8">
                <h2 class="text-xl font-semibold text-gray-800">Masuk ke Akun</h2>
                <p class="text-sm text-gray-400 mt-1">Masukkan email dan password Anda</p>
            </div>

            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-5">
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        Email / NIP
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <i class="far fa-envelope"></i>
                        </span>
                        <input id="email" type="text" name="login" value="{{ old('login') }}" required
                            autofocus placeholder="Email atau NIP"
                            class="block w-full pl-11 pr-4 py-3 bg-[#F8FAFC] border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">

                    </div>
                    @error('email')
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-8">
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input id="password" type="password" name="password" required placeholder="••••••••"
                            class="block w-full pl-11 pr-11 py-3 bg-[#F8FAFC] border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">

                        <span id="togglePassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 cursor-pointer hover:text-gray-600">
                            <i id="eyeIcon" class="far fa-eye text-xs"></i>
                        </span>
                    </div>
                    @error('password')
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full bg-[#1D4076] hover:bg-[#15325b] text-white font-semibold py-3 rounded-xl transition-colors shadow-md">
                    Masuk
                </button>

                <div class="text-center mt-6">
                    <p class="text-sm text-gray-500">
                        Belum punya akun?
                        <a href="{{ route('register') }}" class="text-[#1D4076] font-bold hover:underline">Daftar</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <footer class="py-6 text-center text-[10px] text-gray-400 uppercase tracking-widest">
        © 2024 Biro SDM Kementerian Agama RI
    </footer>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        const eyeIcon = document.querySelector('#eyeIcon');

        togglePassword.addEventListener('click', function(e) {
            // Toggle tipe input antara password dan text
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            // Toggle ikon mata
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
        });
    </script>

</body>

</html>
