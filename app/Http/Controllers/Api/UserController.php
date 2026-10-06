<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // BUG 1b: Model mentah dikembalikan langsung — seluruh kolom bocor ke klien.
    // Termasuk: password (hash bcrypt), remember_token, email_verified_at.
    // Seharusnya memakai UserResource yang sudah di-whitelist field-nya.
    public function index()
    {
        return UserResource::collection(User::query()->paginate(15));
    }

    public function show(User $user)
    {
        if (auth()->user()->role !== 'admin' && auth()->id() !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return new UserResource($user);
    }
}
