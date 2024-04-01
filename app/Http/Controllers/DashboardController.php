<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $totalUsers = Redis::connection('default')->get('total_users');

        return view('dashboard', [
            'totalUsers' => $totalUsers,
        ]);
    }
}
