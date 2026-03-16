<?php

namespace App\Http\Controllers;

use App\Models\LogSistem;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index()
    {
        // Mengambil data log terbaru, dipaginasi 15 data per halaman
        $logs = LogSistem::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.audit.index', compact('logs'));
    }
}