<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Ringkasan profil distributor + akses ke pengaturan akun & alamat.
     */
    public function index()
    {
        $user = Auth::user()->loadMissing(['warehouse.province', 'warehouse.regency', 'warehouse.district']);

        return view('buyer.distributor.profile.index', compact('user'));
    }
}
