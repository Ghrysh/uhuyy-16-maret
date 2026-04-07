<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        Log::info('Login attempt', [
            'login' => $request->login
        ]);

        $request->validate([
            'login' => ['required'],
            'password' => ['required']
        ]);

        $login = $request->login;
        $password = $request->password;

        // Daftar role yang diizinkan untuk masuk ke sistem (Ditambahkan Admin Jafung)
        $allowedRoles = [
            'super_admin', 
            'admin_satker', 
            'pejabat', 
            'admin_jafung_pengguna', 
            'admin_jafung_pembina'
        ];

        /*
        |--------------------------------------------------------------------------
        | LOGIN DATABASE (EMAIL ATAU NIP 111111)
        |--------------------------------------------------------------------------
        */

        if (filter_var($login, FILTER_VALIDATE_EMAIL) || $login == '111111') {

            Log::info('Login menggunakan database lokal');

            if (Auth::attempt([
                filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'nip' => $login,
                'password' => $password
            ])) {
                
                $user = Auth::user();
                
                // Cek Role juga untuk Local Login agar aman
                $hasAccess = $user->roles()->whereIn('key', $allowedRoles)->exists();
                
                if (!$hasAccess) {
                    Auth::logout();
                    Log::warning('Login database ditolak: Tidak memiliki role', ['user_id' => $user->id]);
                    return back()->withErrors([
                        'login' => 'Akses ditolak. Akun Anda belum memiliki akses (Role) di sistem ini.'
                    ]);
                }

                $request->session()->regenerate();
                Log::info('Login database berhasil', [
                    'user_id' => $user->id
                ]);

                return redirect()->intended(route('admin.dashboard'));
            }

            Log::warning('Login database gagal', [
                'login' => $login
            ]);

            return back()->withErrors([
                'login' => 'Email/NIP atau password salah.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | LOGIN VIA API KEMENAG
        |--------------------------------------------------------------------------
        */

        Log::info('Request auth ke API Kemenag');

        $auth = $this->authenticateWithKemenag($login, $password);

        if (!$auth) {
            Log::warning('Auth API gagal', [
                'login' => $login
            ]);

            return back()->withErrors([
                'login' => 'NIP atau password salah.'
            ]);
        }

        Log::info('Auth API berhasil', [
            'nip' => $auth['nip']
        ]);

        /*
        |--------------------------------------------------------------------------
        | AMBIL PROFILE
        |--------------------------------------------------------------------------
        */

        Log::info('Mengambil profile user', [
            'nip' => $auth['nip']
        ]);

        $profile = $this->getProfile($auth['token']);

        if (!$profile) {
            Log::error('Gagal mengambil profile', [
                'nip' => $auth['nip']
            ]);

            return back()->withErrors([
                'login' => 'Gagal mengambil profil user dari API.'
            ]);
        }

        Log::info('Profile berhasil diambil');

        /*
        |--------------------------------------------------------------------------
        | SYNC USER KE DATABASE
        |--------------------------------------------------------------------------
        */

        Log::info('Sync user ke database');

        $user = $this->syncUserFromProfile(
            $auth['nip'],
            $profile
        );

        Log::info('User berhasil sync', [
            'user_id' => $user->id,
            'nip' => $user->nip
        ]);

        /*
        |--------------------------------------------------------------------------
        | CEK ROLE SEBELUM LOGIN
        |--------------------------------------------------------------------------
        */
        // Cukup cek apakah user ini punya minimal 1 role apa saja di database
        $hasAccess = $user->roles()->exists();

        if (!$hasAccess) {
            Log::warning('User tidak memiliki role apa pun', ['user_id' => $user->id]);
            return back()->withErrors([
                'login' => 'Akses ditolak. NIP Anda terverifikasi, namun belum di-assign ke Role apa pun oleh Super Admin.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | LOGIN LARAVEL
        |--------------------------------------------------------------------------
        */

        Auth::login($user);

        Log::info('User login ke Laravel', [
            'user_id' => $user->id
        ]);

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /*
    |--------------------------------------------------------------------------
    | AUTH API KEMENAG
    |--------------------------------------------------------------------------
    */

    private function authenticateWithKemenag(string $login, string $password): ?array
    {
        try {

            $response = Http::asForm()
                ->timeout(60)
                ->retry(3, 2000)
                ->post(
                    'https://api.kemenag.go.id/mobile/auth/login',
                    [
                        'nip' => $login,
                        'password' => $password
                    ]
                );

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            if (!($data['status'] ?? false)) {
                return null;
            }

            return [
                'nip' => $data['user']['id'],
                'token' => $data['token']
            ];

        } catch (\Throwable $e) {

            Log::error('Auth API Error', [
                'message' => $e->getMessage()
            ]);

            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GET PROFILE
    |--------------------------------------------------------------------------
    */

    private function getProfile(string $token)
    {

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->get('https://api.kemenag.go.id/mobile/home/profil');

        return $response->ok()
            ? json_decode($response->body())
            : false;
    }

    /*
    |--------------------------------------------------------------------------
    | SYNC USER
    |--------------------------------------------------------------------------
    */

    private function syncUserFromProfile(string $nip, $profile): User
    {

        $data = $profile->data ?? $profile;

        /*
        |--------------------------------------------------------------------------
        | Simpan user
        |--------------------------------------------------------------------------
        */

        $user = User::updateOrCreate(

            ['nip' => $nip],

            [
                'name' => $data->NAMA_LENGKAP ?? 'User '.$nip,
                'email' => $data->EMAIL_DINAS ?? $nip.'@kemenag.go.id',
                'password' => Hash::make(Str::random(16))
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | SIMPAN DETAIL
        |--------------------------------------------------------------------------
        */

        UserDetail::updateOrCreate(

            ['nip_baru' => $nip],

            [

                'nip' => $data->NIP ?? $nip,
                'nip_baru' => $data->NIP_BARU ?? $nip,

                'nama' => $data->NAMA ?? null,
                'nama_lengkap' => $data->NAMA_LENGKAP ?? null,
                'agama' => $data->AGAMA ?? null,
                'tempat_lahir' => $data->TEMPAT_LAHIR ?? null,
                'tanggal_lahir' => $this->formatDate($data->TANGGAL_LAHIR ?? null),
                'jenis_kelamin' => $data->JENIS_KELAMIN ?? null,

                'pendidikan' => $data->PENDIDIKAN ?? null,
                'jenjang_pendidikan' => $data->JENJANG_PENDIDIKAN ?? null,

                'kode_level_jabatan' => $data->KODE_LEVEL_JABATAN ?? null,
                'level_jabatan' => $data->LEVEL_JABATAN ?? null,

                'pangkat' => $data->PANGKAT ?? null,
                'gol_ruang' => $data->GOL_RUANG ?? null,

                'tmt_cpns' => $this->formatDate($data->TMT_CPNS ?? null),
                'tmt_pangkat' => $this->formatDate($data->TMT_PANGKAT ?? null),

                'mk_tahun' => $data->MK_TAHUN ?? null,
                'mk_bulan' => $data->MK_BULAN ?? null,

                'gaji_pokok' => $data->GAJI_POKOK ?? null,

                'tipe_jabatan' => $data->TIPE_JABATAN ?? null,
                'kode_jabatan' => $data->KODE_JABATAN ?? null,
                'tampil_jabatan' => $data->TAMPIL_JABATAN ?? null,

                'tmt_jabatan' => $this->formatDate($data->TMT_JABATAN ?? null),

                'kode_satuan_kerja' => $data->KODE_SATUAN_KERJA ?? null,
                'satker_1' => $data->SATKER_1 ?? null,

                'satker_2' => $data->SATKER_2 ?? null,
                'kode_satker_2' => $data->KODE_SATKER_2 ?? null,

                'satker_3' => $data->SATKER_3 ?? null,
                'kode_satker_3' => $data->KODE_SATKER_3 ?? null,

                'satker_4' => $data->SATKER_4 ?? null,
                'kode_satker_4' => $data->KODE_SATKER_4 ?? null,

                'satker_5' => $data->SATKER_5 ?? null,
                'kode_satker_5' => $data->KODE_SATKER_5 ?? null,

                'kode_grup_satuan_kerja' => $data->KODE_GRUP_SATUAN_KERJA ?? null,
                'grup_satuan_kerja' => $data->GRUP_SATUAN_KERJA ?? null,

                'status_kawin' => $data->STATUS_KAWIN ?? null,

                'alamat_1' => $data->ALAMAT_1 ?? null,
                'alamat_2' => $data->ALAMAT_2 ?? null,

                'telepon' => $data->TELEPON ?? null,
                'no_hp' => $data->NO_HP ?? null,
                'email' => $data->EMAIL ?? null,

                'kab_kota' => $data->KAB_KOTA ?? null,
                'provinsi' => $data->PROVINSI ?? null,
                'kode_pos' => $data->KODE_POS ?? null,

                'status_pegawai' => $data->STATUS_PEGAWAI ?? null,

                'lat' => $data->LAT ?? null,
                'lon' => $data->LON ?? null,

                'satker_kelola' => $data->SATKER_KELOLA ?? null,
                'hari_kerja' => $data->HARI_KERJA ?? null,

            ]
        );

        return $user;
    }

    private function formatDate($date)
    {
        if (!$date) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}