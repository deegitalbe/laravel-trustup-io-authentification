<?php

namespace Deegitalbe\LaravelTrustupIoAuthentification\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrustUpIoAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $roles = null)
    {
        if ( ! auth()->check() ) {
            return redirect()->to(
                config('trustup-io-authentification.url').'?callback=' . urlencode(url()->to('/'))
            );
        }

        $roles = $roles ? explode('|', $roles) : config('trustup-io-authentification.roles');

        if ( ! auth()->user()->hasAnyRole($roles) ) {
            return redirect()->to(
                config('trustup-io-authentification.url').'/errors/invalid-role'
            );
        }

        return $next($request);
    }
}
