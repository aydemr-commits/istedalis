<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('staff_id')) {
            return redirect()->route('staff.login')->withErrors(['staff_no' => 'Lutfen staff girisi yapin.']);
        }

        return $next($request);
    }
}
