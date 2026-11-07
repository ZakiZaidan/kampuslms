<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Petakan sinonim peran (Bahasa Indonesia & Inggris)
        $normalizedRoles = [];
        foreach ($roles as $role) {
            $role = strtolower(trim($role));
            $normalizedRoles[] = $role;

            if ($role === 'dosen' || $role === 'lecturer') {
                $normalizedRoles[] = 'dosen';
                $normalizedRoles[] = 'lecturer';
            } elseif ($role === 'mahasiswa' || $role === 'student') {
                $normalizedRoles[] = 'mahasiswa';
                $normalizedRoles[] = 'student';
            }
        }

        $userRole = strtolower(trim($user->role ?? ''));

        if (! in_array($userRole, $normalizedRoles, true)) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk membuka halaman ini.');
        }

        return $next($request);
    }
}
