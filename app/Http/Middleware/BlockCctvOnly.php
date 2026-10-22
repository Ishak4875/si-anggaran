<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Akun ber-role "cctv" hanya boleh ke halaman CCTV Biota: permintaan GET ke
 * halaman lain dialihkan ke sana (termasuk dashboard setelah login), selainnya 403.
 */
class BlockCctvOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isCctvOnly()) {
            return $request->isMethod('GET')
                ? redirect()->route('cctv.index')
                : abort(403);
        }

        return $next($request);
    }
}
