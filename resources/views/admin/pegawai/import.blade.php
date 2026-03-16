@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-slate-200">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                <h3 class="text-lg font-bold text-slate-800 flex items-center">
                    <i class="fas fa-file-import mr-2 text-navy-custom"></i>
                    Import Data Pegawai via Excel
                </h3>
            </div>

            <div class="p-6">
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 flex items-start">
                        <i class="fas fa-check-circle mt-1 mr-3"></i>
                        <div>
                            <p class="font-bold">Berhasil!</p>
                            <p class="text-sm">{{ session('success') }}</p>
                            <p class="text-xs mt-1 italic font-semibold text-green-600">
                                *Pastikan untuk menjalankan <code class="bg-green-100 px-1 rounded">php artisan queue:work</code> di server agar data diproses.
                            </p>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 flex items-start">
                        <i class="fas fa-exclamation-circle mt-1 mr-3"></i>
                        <p class="text-sm">{{ session('error') }}</p>
                    </div>
                @endif

                <form action="{{ route('admin.pegawai.import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih File Excel</label>
                        <div class="relative border-2 border-dashed border-slate-300 rounded-lg p-6 hover:border-navy-custom transition-colors group bg-slate-50">
                            <input type="file" name="file_excel" id="file_excel" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required accept=".xlsx, .xls, .csv">
                            
                            <div class="text-center" id="file-preview">
                                <i class="fas fa-cloud-upload-alt text-4xl text-slate-400 group-hover:text-navy-custom transition-colors mb-2"></i>
                                <p class="text-sm text-slate-600">Klik atau seret file ke sini</p>
                                <p class="text-xs text-slate-400 mt-1">Format: .xlsx, .xls, .csv (Maks. 100MB)</p>
                            </div>
                        </div>
                        @error('file_excel')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="bg-blue-50 rounded-lg p-4 mb-6 border border-blue-100">
                        <h4 class="text-xs font-bold text-blue-800 uppercase tracking-wider mb-2">Ketentuan Import:</h4>
                        <ul class="text-xs text-blue-700 space-y-1 list-disc ml-4">
                            <li>Sistem akan mengambil **NIP BARU** dari kolom pertama (Kolom A).</li>
                            <li>Sistem akan melakukan pencarian otomatis ke API SIMSDM Kemenag menggunakan NIP Baru tersebut.</li>
                            <li>Data akan diproses di latar belakang (**Queue**) agar tidak memberatkan server.</li>
                            <li>Username akun adalah NIP Baru, dengan password default: <span class="font-mono font-bold">password123</span></li>
                        </ul>
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit" id="btn-submit" class="w-full sm:w-auto bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-6 rounded-lg shadow-md transition-all flex items-center justify-center uppercase tracking-widest text-sm">
                            <i class="fas fa-sync-alt mr-2" id="icon-submit"></i>
                            Mulai Sinkronisasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Preview Nama File
    document.getElementById('file_excel').addEventListener('change', function(e) {
        if(e.target.files.length > 0) {
            const fileName = e.target.files[0].name;
            const fileSize = (e.target.files[0].size / 1024 / 1024).toFixed(2); // Ukuran dalam MB
            const preview = document.getElementById('file-preview');
            
            // Validasi client-side sederhana untuk memberikan feedback cepat
            if (fileSize > 100) {
                alert('Ukuran file terlalu besar! Maksimal 100MB.');
                e.target.value = ''; // Reset input
                return;
            }

            preview.innerHTML = `
                <i class="fas fa-file-excel text-4xl text-green-600 mb-2"></i>
                <p class="text-sm font-bold text-slate-700">${fileName}</p>
                <p class="text-xs text-slate-500">${fileSize} MB</p>
                <p class="text-xs text-blue-500 mt-1 cursor-pointer">Klik untuk mengganti file</p>
            `;
        }
    });

    // Loading saat submit
    document.querySelector('form').addEventListener('submit', function() {
        const btn = document.getElementById('btn-submit');
        const icon = document.getElementById('icon-submit');
        
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        if(icon) icon.classList.add('fa-spin');
        btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Mengunggah & Menjadwalkan...`;
    });
</script>
@endsection