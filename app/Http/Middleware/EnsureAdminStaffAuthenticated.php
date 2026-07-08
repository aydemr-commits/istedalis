<?php

namespace App\Http\Middleware;

use App\Models\Staff;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminStaffAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $staff = Staff::find($request->session()->get('staff_id'));

        abort_unless($staff?->isAdmin(), 403, 'Bu islem icin admin yetkisi gerekir.');

        return $next($request);
    }
}
