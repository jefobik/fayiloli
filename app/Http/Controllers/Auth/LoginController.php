<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * Tenant domain  → /home (RBAC-aware module dashboard)
     * Central domain → /admin/tenants (tenant management, admins only)
     *
     * We send tenant users to /home rather than / so they land on the
     * personalised dashboard (role badge, module launchpad, live stats)
     * instead of jumping straight into the documents list.
     */
    protected function redirectTo(): string
    {
        return tenancy()->initialized ? '/home' : '/admin/tenants';
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
