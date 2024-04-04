<?php

namespace App\Http\Controllers;

use App\Models\CountryStat;
use App\Models\Session;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class DashboardController extends Controller
{
    public function dashboard()
    {
        // Get the total number of users
        $totalUsers = Session::where('updated_at', '>=', Carbon::now()->subSeconds(12))->count();

        // Get the values of the countries
        $countries = CountryStat::get()
            ->pluck('count', 'country')
            ->toArray();

        // Get the total number of countries
        $totalCountries = count($countries);

        return view('dashboard', [
            'totalUsers'     => $totalUsers ?? 0,
            'totalCountries' => $totalCountries,
            'countries'      => array_keys($countries),
            'countriesUsers' => array_values($countries),
        ]);
    }
}
