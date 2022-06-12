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
            return $request->expectsJson()
                ? response(['message' => 'Unauthentificated', 'redirect' => config('trustup-io-authentification.url').'?callback=' . urlencode(url()->to('/'))], 401)
                : redirect()->to(
                    config('trustup-io-authentification.url').'?callback=' . urlencode(url()->to('/'))
                );
        }

        $roles = $roles ? explode('|', $roles) : config('trustup-io-authentification.roles');

        if ( ! auth($guard)->user()->hasAnyRole($roles) ) {
            return $request->expectsJson()
                ? response(['message' => 'Invalid role', 'redirect' => config('trustup-io-authentification.url').'/errors/invalid-role'], 403)
                : redirect()->to(
                    config('trustup-io-authentification.url').'/errors/invalid-role'
                );
        }

        return $next($request);
    }
}
