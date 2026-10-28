<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Akun ber-role "cctv"/"ppk" hanya boleh ke halaman CCTV: permintaan GET ke
 * halaman lain dialihkan ke Daftar Paket CCTV (termasuk dashboard setelah login), selainnya 403.
 */
class BlockCctvOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isCctvOnly()) {
            return $request->isMethod('GET')
                ? redirect()->route('cctv.paket')
                : abort(403);
        }

        return $next($request);
    }
}
