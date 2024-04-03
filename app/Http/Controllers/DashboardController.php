<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class DashboardController extends Controller
{
    public function dashboard()
    {
        // Get the total number of users
        $totalUsers = Redis::connection('default')->get('total_users');

        // Get the countries from the Redis cache
        $countriesKeys = Redis::connection('default')->keys('countries:*');

        // Get the values of the countries
        $countries = [];
        foreach ($countriesKeys as $key) {
            $countries[] = Redis::connection('default')->get($key);
        }

        // Get the total number of countries
        $totalCountries = count($countries);

        return view('dashboard', [
            'totalUsers'     => $totalUsers ?? 0,
            'countries'      => $countries,
            'totalCountries' => $totalCountries,
        ]);
    }
}
