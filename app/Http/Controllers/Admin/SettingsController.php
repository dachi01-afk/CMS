<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        // Ambil data user yang sedang login
        $user = $request->user()->load('admin');
        // dd($user->admin);
        return view('admin.settings', ['user' => $user]);
    }
}
