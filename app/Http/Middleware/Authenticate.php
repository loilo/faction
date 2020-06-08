<?php

namespace App\Http\Middleware;

use Auth;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

/**
 * Redirect users to OAuth login page if configured
 */
class Authenticate extends Middleware
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return void
     */
    public function handle($request, \Closure $next, ...$guards)
    {
        $guard = $guards[0] ?? config('auth.defaults.guard');
        $this->auth->shouldUse($guard);

        if (!Auth::guard($guard)->check()) {
            // User authentication failed (user is not or no longer logged in)
            if ($guard === 'web') {
                // Regular website
                if (!empty(config('auth.github_orgs_whitelist'))) {
                    // Redirect to GitHub OAuth if configured
                    return redirect('login')->with(
                        'redirect_to',
                        $request->getUri(),
                    );
                } else {
                    // Yield 403
                    return response()->view('errors.403', [], 403);
                }
            } else {
                // Composer-targeted URLs
                return response('Could not authenticate', 403, [
                    'Content-Type' => 'text/plain',
                ]);
            }
        }

        return $next($request);
    }
}
