<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards) {
        $guards = empty($guards) ? [null] : $guards;
        $is_guest = true;

        foreach ($guards as $guard) {
            if (!$this->auth->guard($guard)->guest()) {
                $this->auth->shouldUse($guard);
                $is_guest = false;
                break;
            }
        }

        if ($is_guest) {
            $status = Response::HTTP_UNAUTHORIZED;
            return response()->json([
                        'status' => 'ERROR',
                        'message' => Response::$statusTexts[$status]
                            ], $status);
        }
        return $next($request);
    }
}
