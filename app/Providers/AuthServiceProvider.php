<?php

namespace App\Providers;

use App\Models\AnonymousUser;
use App\Models\WebhookUser;
use Auth;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\ServerRequest;
use Http;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Loilo\GithubWebhook\Exceptions\WebhookException;
use Loilo\GithubWebhook\Handler;
use Socialite;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        // Register auth for public sites which just lets all requests procede
        Auth::viaRequest('public', [$this, 'authPublic']);

        // Register auth for IP address based whitelists
        Auth::viaRequest('ip', [$this, 'authViaIpWhitelist']);

        // Register auth for the configured GitHub OAuth app
        Auth::viaRequest('oauth', [$this, 'authViaGitHubApp']);

        // Register auth for stateless verification with GitHub access tokens
        Auth::viaRequest('token', [$this, 'authViaGitHubToken']);

        // Cascade public auth, IP check and token OAuth for browser access
        Auth::viaRequest(
            'public.ip.oauth',
            fn(Request $request) => $this->authPublic($request) ??
                ($this->authViaIpWhitelist($request) ??
                    $this->authViaGitHubApp($request)),
        );

        // Cascade public auth, IP check and access token for Composer access
        Auth::viaRequest(
            'public.ip.token',
            fn(Request $request) => $this->authPublic($request) ??
                ($this->authViaIpWhitelist($request) ??
                    $this->authViaGitHubToken($request)),
        );

        // Register auth for webhook requests
        Auth::viaRequest('webhook', [$this, 'authViaWebHook']);
    }

    /**
     * Authenticate as an anonymous user if the app is configured as public.
     */
    public function authPublic(Request $request)
    {
        return config('auth.public') ? new AnonymousUser() : null;
    }

    /**
     * Check if a given IPv4 address is in a network
     *
     * @param  string $ip    IP to check in IPV4 format eg. 127.0.0.1
     * @param  string $range IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1 is accepted and /32 assumed
     * @return bool
     */
    protected function isIpInRange($ip, $range): bool
    {
        if (strpos($range, '/') === false) {
            $range .= '/32';
        }
        // $range is in IP/CIDR format eg 127.0.0.1/24
        list($range, $netmask) = explode('/', $range, 2);
        $range_decimal = ip2long($range);
        $ip_decimal = ip2long($ip);
        $wildcard_decimal = pow(2, 32 - $netmask) - 1;
        $netmask_decimal = ~$wildcard_decimal;
        return ($ip_decimal & $netmask_decimal) ==
            ($range_decimal & $netmask_decimal);
    }

    /**
     * Authenticate as an anonymous user if the request's IP address
     * is part of the IP or subnet whitelist.
     */
    public function authViaIpWhitelist(Request $request)
    {
        // Check IP whitelist
        $ipWhitelist = config('auth.ip_whitelist');

        // If whitelist is empty, this guard is always authenticating a user
        if (!empty($ipWhitelist)) {
            foreach ($ipWhitelist as $ipOrSubnet) {
                if ($this->isIpInRange($request->ip(), $ipOrSubnet)) {
                    return new AnonymousUser();
                }
            }
        }

        return null;
    }

    /**
     * Get a user object in exchange for a GitHub access token
     */
    protected function getUserFromToken(string $token)
    {
        $orgsWhitelist = config('auth.github_orgs_whitelist');

        if (!empty($orgsWhitelist)) {
            $orgs = Http::withBasicAuth('token', $token)->get(
                'https://api.github.com/user/orgs',
            );

            if (!$orgs->ok()) {
                return null;
            }

            foreach ($orgs->json() as $org) {
                if (in_array($org['login'], $orgsWhitelist)) {
                    return new AnonymousUser();
                }
            }
        }

        return null;
    }

    /**
     * Authenticate as an anonymous user if an access token is provided that
     * belongs to a user of an authorized organization.
     */
    public function authViaGitHubToken(Request $request)
    {
        $user = $request->getUser();
        $token = $request->getPassword();

        if ($user === 'token' && !is_null($token)) {
            return $this->getUserFromToken($token);
        }

        return null;
    }

    /**
     * Authenticate as an anonymous user if an OAuth authorization code is
     * obtainable from the query string and the requesting user is part of
     * an authorized organization.
     */
    public function authViaGitHubApp(Request $request)
    {
        // We need to memoize the authorization state per request
        // because OAuth authorization codes can only be exchanged once.
        static $cachedAuthenticationState = false;
        if ($cachedAuthenticationState !== false) {
            return $cachedAuthenticationState;
        }

        /**
         * @var \App\Models\Perishable
         */
        $authentication = session('authentication');

        // Check the session for a remembered authentication
        if (!is_null($authentication)) {
            if ($authentication->expired) {
                session()->forget('authentication');
                $cachedAuthenticationState = null;
                return null;
            } else {
                $cachedAuthenticationState = new AnonymousUser();
                return $cachedAuthenticationState;
            }
        }

        $orgsWhitelist = config('auth.github_orgs_whitelist');

        // Use Socialite to validate the authorization code if applicable
        if (!empty($orgsWhitelist)) {
            try {
                $authentication = Socialite::driver('github')
                    ->stateless()
                    ->user();
            } catch (ClientException $e) {
                $cachedAuthenticationState = null;
                return null;
            }

            if (is_null($authentication)) {
                $cachedAuthenticationState = null;
                return null;
            }

            // Use token obtained through the OAuth process
            // to check for user's organization memberships
            $orgs = Http::withBasicAuth('token', $authentication->token)->get(
                'https://api.github.com/user/orgs',
            );

            if (!$orgs->ok()) {
                $cachedAuthenticationState = null;
                return null;
            }

            foreach ($orgs->json() as $org) {
                if (in_array($org['login'], $orgsWhitelist)) {
                    $cachedAuthenticationState = $authentication;
                    return $authentication;
                }
            }
        }

        $cachedAuthenticationState = null;
        return null;
    }

    /**
     * Authenticate as a webhook user if the provided signature is valid for
     * the configured webhook secret.
     */
    public function authViaWebHook(Request $request)
    {
        // The webhook library accepts PSR-7 requests, therefore we need to
        // create such a request from Laravel's Illuminate request object.
        $psrRequest = new ServerRequest(
            $request->getMethod(),
            $request->getUri(),
            $request->headers->all(),
            $request->getContent(),
        );

        // We need to parse x-www-form-urlencoded requests manually
        if (
            $request->headers->get('Content-Type') ===
            'application/x-www-form-urlencoded'
        ) {
            parse_str($request->getContent(), $post);
            $psrRequest = $psrRequest->withParsedBody($post);
        }

        $handler = new Handler(config('auth.github_webhook_secret'));

        try {
            $delivery = $handler->handle($psrRequest);

            return new WebhookUser($delivery);
        } catch (WebhookException $e) {
            return null;
        }
    }
}
