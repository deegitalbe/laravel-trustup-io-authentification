<?php

namespace Deegitalbe\LaravelTrustupIoAuthentification\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Deegitalbe\LaravelTrustupIoAuthentification\TrustupIoUserProvider;

class TrustUpIoAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $roles = null, string $guard = null)
    {
        $guard ??= config('trustup-io-authentification.guard');

        if ( ! auth($guard)->check() ) {
            $redirection = get_trustup_io_authentification_redirection_url();
            
            return $request->expectsJson()
                ? response(['message' => 'Unauthentificated', 'redirect' => $redirection], 401)
                : redirect()->to(
                    $redirection
                );
        }

        $roles = $roles ? explode('|', $roles) : config('trustup-io-authentification.roles');

        if ( ! auth($guard)->user()->hasAnyRole($roles) ) {
            return $request->expectsJson()
                ? response(['message' => 'Invalid role', 'redirect' => get_trustup_io_authentification_invalid_role_url()], 403)
                : redirect()->to(
                    get_trustup_io_authentification_invalid_role_url()
                );
        }

        return $next($request);
    }
}
