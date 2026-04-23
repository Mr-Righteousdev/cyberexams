<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceLocalNetwork
{
    private array $localRanges = [
        '127.0.0.1',
        '::1',
        '192.168.',
        '10.',
        '172.16.',
        '172.17.',
        '172.18.',
        '172.19.',
        '172.20.',
        '172.21.',
        '172.22.',
        '172.23.',
        '172.24.',
        '172.25.',
        '172.26.',
        '172.27.',
        '172.28.',
        '172.29.',
        '172.30.',
        '172.31.',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        foreach ($this->localRanges as $range) {
            if (str_starts_with($ip, $range)) {
                return $next($request);
            }
        }

        return redirect()->route('network.blocked');
    }
}
