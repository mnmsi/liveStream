<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $redis = Redis::connection('default');

        // Get the total number of users
        $totalUsers = $redis->get('total_users');

        // Get the countries from the Redis cache
        $countriesKeys = $redis->keys('countries:*');

        // Get the values of the countries
        $countries = [];
        foreach ($countriesKeys as $key) {
            $country = $redis->get($key);
            $countryUsers = $redis->get("countries_users:$country");
            $countries[$country] = $countryUsers;
        }

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
