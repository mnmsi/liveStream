<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRegistration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // check database.sqlite file exists
        if (!file_exists(database_path('database.sqlite'))) {
            // Create the database.sqlite file with permission
            touch(database_path('database.sqlite'));
        }

        // cehck database is empty
        if (filesize(database_path('database.sqlite')) == 0) {
            // Run the migration
            Artisan::call('migrate:fresh');
        }

        if (User::count() > 0) {
            // Users are registered, allow access to the next middleware or route
            return $next($request);
        }

        // Users table is empty, redirect to the registration page
        return redirect()->route('register.show');
    }
}
