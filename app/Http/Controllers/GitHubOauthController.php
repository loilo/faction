<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Auth;
use Log;
use Socialite;

/**
 * Handle OAuth-based logins on the website
 */
class GitHubOauthController extends Controller
{
    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        session(['auth_redirect' => session('redirect_to')]);

        return Socialite::driver('github')
            ->stateless()
            ->scopes(['read:org'])
            ->redirect();
    }

    /**
     * Obtain user information from GitHub on successful authentication.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        $redirectUrl = session()->pull('auth_redirect') ?? '/';

        if (Auth::guard('oauth')->check()) {
            Log::info(
                sprintf(
                    'Auth::check() successful: %s?%s, user %s',
                    url()->current(),
                    $_SERVER['QUERY_STRING'],
                    gettype(Auth::guard('oauth')->user()),
                ),
            );

            // If OAuth was successful, remember session data
            session()->put(
                'authentication',
                perishable(config('auth.remember_duration')),
            );
            return response()->redirectTo($redirectUrl, 307);
        } else {
            Log::info(
                sprintf(
                    'Auth::check() failed: %s?%s, user %s',
                    url()->current(),
                    $_SERVER['QUERY_STRING'],
                    gettype(Auth::guard('oauth')->user()),
                ),
            );

            return response()->view('errors.403', [], 403);
        }
    }
}
