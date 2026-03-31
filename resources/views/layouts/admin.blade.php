<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - SATKER</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F0F2F5;
            overflow-x: hidden;
        }

        .sidebar-active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 4px solid #FBBF24;
        }

        .sidebar-transition {
            transition: all 0.3s ease-in-out;
        }

        /* Rotasi icon arrow saat dropdown terbuka */
        .dropdown-active i.fa-chevron-right {
            transform: rotate(90deg);
            transition: transform 0.2s;
        }

        .dropdown-container {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .dropdown-container.open {
            max-height: 250px;
        }
    </style>
    @stack('styles')
</head>

<body class="min-h-screen bg-[#F0F2F5]">

    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <aside id="sidebar"
        class="sidebar-transition w-64 bg-[#112D4E] text-white flex flex-col fixed h-full z-50 -translate-x-full lg:translate-x-0">
        <div class="p-6 flex items-center justify-between border-b border-white/10">
            <div id="sidebarLogo" class="flex items-center space-x-3 opacity-100 transition-opacity duration-300">
                <div class="bg-yellow-500 p-2 rounded-lg text-[#112D4E]">
                    <i class="fas fa-shield-halved text-xl"></i>
                </div>
                <div>
                    <h1 class="font-bold leading-none tracking-tight text-lg">SATKER</h1>
                    <p class="text-[10px] text-gray-300 uppercase">Biro SDM Kemenag</p>
                </div>
            </div>
            <button onclick="toggleSidebar()" class="text-gray-400 hover:text-white focus:outline-none">
                <i class="fas fa-bars text-xl hidden lg:block"></i>
                <i class="fas fa-times text-xl lg:hidden"></i>
            </button>
        </div>

        <nav class="flex-grow py-4 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}"
                class="{{ request()->routeIs('admin.dashboard') ? 'sidebar-active text-yellow-400' : 'text-gray-300' }} flex items-center px-6 py-3 text-sm hover:bg-white/5 transition">
                <i class="fas fa-th-large w-6"></i> <span>Dashboard</span>
            </a>

            <div class="mt-2">
                <button onclick="toggleDropdown('masterDropdown')" id="btn-masterDropdown"
                    class="w-full flex items-center justify-between px-6 py-3 text-sm text-gray-300 hover:bg-white/5 transition group">
                    <div class="flex items-center">
                        <i class="fas fa-database w-6"></i>
                        <span class="font-semibold uppercase text-xs tracking-wider">Master Data</span>
                    </div>
                    <i class="fas fa-chevron-right text-[10px] transition-transform duration-200"></i>
                </button>

                <div id="masterDropdown"
                    class="dropdown-container bg-black/10 {{ request()->routeIs('admin.wilayah.*', 'admin.satker.*', 'admin.setting-kode.*', 'admin.jabatan.*', 'admin.pegawai.*', 'admin.periode.*') ? 'open' : '' }}">
                    
                    <a href="{{ route('admin.wilayah.index') }}"
                        class="{{ request()->routeIs('admin.wilayah.*') ? 'sidebar-active text-yellow-400' : 'text-gray-300' }} flex items-center pl-14 pr-6 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 transition">
                        <i class="fas fa-location-dot w-5 text-xs"></i> <span>Wilayah</span>
                    </a>
                    
                    <a href="{{ route('admin.satker.index') }}"
                        class="{{ request()->routeIs('admin.satker.index') ? 'sidebar-active text-yellow-400' : 'text-gray-300' }} flex items-center pl-14 pr-6 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 transition">
                        <i class="fas fa-building w-5 text-xs"></i> <span>Satuan Kerja</span>
                    </a>
                    <a href="{{ route('admin.jabatan.index') }}"
                        class="{{ request()->routeIs('admin.jabatan.*') ? 'sidebar-active text-yellow-400' : 'text-gray-300' }} flex items-center pl-14 pr-6 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 transition">
                        <i class="fas fa-id-card w-5 text-xs"></i> <span>Jabatan Fungsional</span>
                    </a>
                    
                    <a href="{{ route('admin.pegawai.index') }}"
                        class="{{ request()->routeIs('admin.pegawai.*') ? 'sidebar-active text-yellow-400' : 'text-gray-300' }} flex items-center pl-14 pr-6 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 transition">
                        <i class="fas fa-users w-5 text-xs"></i> <span>Pegawai</span>
                    </a>
                    
                    <a href="{{ route('admin.periode.index') }}"
                        class="{{ request()->routeIs('admin.periode.*') ? 'sidebar-active text-yellow-400' : 'text-gray-300' }} flex items-center pl-14 pr-6 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 transition">
                        <i class="fas fa-calendar-alt w-5 text-xs"></i> <span>Periode</span>
                    </a>
                </div>
            </div>

            {{-- <a href="{{ route('admin.penugasan.index') }}"
                class="{{ request()->routeIs('admin.penugasan.*') ? 'sidebar-active text-yellow-400' : 'text-gray-300' }} flex items-center px-6 py-3 mt-4 text-sm text-gray-300 hover:bg-white/5 transition">
                <i class="fas fa-user-tie w-6"></i> <span>Penugasan Pejabat</span>
            </a> --}}
            {{-- DROPDOWN PENGATURAN --}}
            <div class="mt-2">
                <button onclick="toggleDropdown('settingsDropdown')" id="btn-settingsDropdown"
                    class="w-full flex items-center justify-between px-6 py-3 text-sm text-gray-300 hover:bg-white/5 transition group">
                    <div class="flex items-center">
                        <i class="fas fa-cog w-6"></i>
                        <span class="font-semibold uppercase text-xs tracking-wider">Pengaturan</span>
                    </div>
                    <i class="fas fa-chevron-right text-[10px] transition-transform duration-200"></i>
                </button>

                <div id="settingsDropdown"
                    class="dropdown-container bg-black/10 {{ request()->routeIs('admin.audit.*') ? 'open' : '' }}">
                    {{-- Hapus class 'hidden' di sini --}}
                    <a href="{{ route('admin.audit.index') }}"
                        class="{{ request()->routeIs('admin.audit.*') ? 'sidebar-active text-yellow-400' : 'text-gray-300' }} flex items-center pl-14 pr-6 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 transition">
                        <i class="fas fa-clock-rotate-left w-5 text-xs"></i> <span>Audit Log</span>
                    </a>
                    @if(auth()->user()->roles()->where('key', 'super_admin')->exists())
                    <a href="{{ route('admin.setting-kode.index') }}"
                        class="{{ request()->routeIs('admin.setting-kode.*') ? 'sidebar-active text-yellow-400' : 'text-gray-300' }} flex items-center pl-14 pr-6 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 transition">
                        <i class="fas fa-code w-5 text-xs"></i> <span>Rumus Kode</span>
                    </a>
                    @endif
                </div>
            </div>
        </nav>

        <div class="p-4 border-t border-white/10 flex items-center justify-between bg-[#0D2440]">
            <div class="flex items-center space-x-3 overflow-hidden">
                <div
                    class="w-8 h-8 flex-shrink-0 rounded-full bg-blue-500 flex items-center justify-center text-xs font-bold uppercase">
                    {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                </div>
                <div class="text-[10px] truncate">{{ auth()->user()->email ?? 'admin@satker.go.id' }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-gray-400 hover:text-white transition">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
    </aside>

    <div id="mainContent" class="sidebar-transition flex-grow flex flex-col min-h-screen lg:ml-64">

        <header id="mobileHeader" class="bg-white shadow-sm border-b p-4 flex items-center lg:hidden shrink-0">
            <button onclick="toggleSidebar()" class="text-[#112D4E] p-2 focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <h2 class="ml-4 font-bold text-[#112D4E]">SATKER</h2>
        </header>

        <header id="desktopHeader"
            class="bg-white shadow-sm border-b px-8 py-4 hidden lg:flex items-center sticky top-0 z-30 sidebar-transition shrink-0">
            <button id="desktopHamburger" onclick="toggleSidebar()"
                class="text-[#112D4E] p-2 focus:outline-none mr-4 hidden">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <h2 class="font-bold text-[#112D4E]">Sistem Informasi Satuan Kerja</h2>
        </header>

        <main class="p-4 md:p-6 lg:p-8 flex-grow">
            @yield('content')
        </main>

        <footer class="p-6 text-center text-[10px] text-gray-400 uppercase tracking-widest shrink-0">
            © 2026 Biro SDM Kementerian Agama RI
        </footer>
    </div>

    <script>
        // Fungsi Dropdown Master Data
        function toggleDropdown(id) {
            const dropdown = document.getElementById(id);
            const btn = document.getElementById('btn-' + id);

            dropdown.classList.toggle('open');
            btn.classList.toggle('dropdown-active');

            // Logika arrow rotation
            const icon = btn.querySelector('.fa-chevron-right');
            if (dropdown.classList.contains('open')) {
                icon.style.transform = 'rotate(90deg)';
            } else {
                icon.style.transform = 'rotate(0deg)';
            }
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const overlay = document.getElementById('sidebarOverlay');
            const desktopHamburger = document.getElementById('desktopHamburger');
            const isDesktop = window.innerWidth >= 1024;

            if (isDesktop) {
                if (sidebar.classList.contains('lg:translate-x-0')) {
                    // SAAT SIDEBAR DISEMBUNYIKAN
                    sidebar.classList.replace('lg:translate-x-0', 'lg:-translate-x-full');
                    mainContent.classList.replace('lg:ml-64', 'lg:ml-0');
                    // Tampilkan hamburger di header atas
                    desktopHamburger.classList.remove('hidden');
                } else {
                    // SAAT SIDEBAR DITAMPILKAN
                    sidebar.classList.replace('lg:-translate-x-full', 'lg:translate-x-0');
                    mainContent.classList.replace('lg:ml-0', 'lg:ml-64');
                    // Sembunyikan hamburger di header atas (karena sudah ada tombol 'X' atau 'Bars' di dalam sidebar)
                    desktopHamburger.classList.add('hidden');
                }
            } else {
                // Mobile Toggle Logic (Tetap sama)
                if (sidebar.classList.contains('-translate-x-full')) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                } else {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                }
            }
        }

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                document.getElementById('sidebarOverlay').classList.add('hidden');
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')
</body>

</html>
