<?php

namespace Deegitalbe\LaravelTrustupIoAuthentification\Http\Middleware;

use Closure;
use Deegitalbe\LaravelTrustupIoAuthentification\TrustupIoUserContract;
use Illuminate\Http\Request;

class TrustUpIoAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $roles = null, string $guard = null)
    {
        $guard ??= config('trustup-io-authentification.guard');

        if (! auth($guard)->check()) {
            $redirection = get_trustup_io_authentification_redirection_url();

            return $request->expectsJson()
                ? response(['message' => 'Unauthentificated', 'redirect' => $redirection], 401)
                : redirect()->to(
                    $redirection
                );
        }

        /** @var TrustupIoUserContract */
        $user = auth($guard)->user();
        $roles = $roles ? explode('|', $roles) : config('trustup-io-authentification.roles');

        if (! empty($roles) && ! $user->hasAnyRole($roles)) {
            return $request->expectsJson()
                ? response(['message' => 'Invalid role', 'redirect' => get_trustup_io_authentification_invalid_role_url()], 403)
                : redirect()->to(
                    get_trustup_io_authentification_invalid_role_url()
                );
        }

        $hasImpersonatingToken = $request->hasHeader('X-Impersonating-Token');
        $impersonatingRoles = config('trustup-io-authentification.impersonating_roles');
        if (! $hasImpersonatingToken || empty($impersonatingRoles)) {
            return $next($request);
        }

        if ($user->getImpersonatingUser()?->hasAnyRole($impersonatingRoles)) {
            return $next($request);
        }

        return $request->expectsJson()
            ? response(['message' => 'Invalid impersonation', 'redirect' => get_trustup_io_authentification_invalid_role_url()], 403)
            : redirect()->to(
                get_trustup_io_authentification_invalid_role_url()
            );
    }
}
