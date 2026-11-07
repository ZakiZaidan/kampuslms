<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return UserResource::collection(User::paginate(15));
    }

    public function show(User $user)
    {
        if (auth()->user()->role !== 'admin' && auth()->id() !== $user->id) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        return new UserResource($user);
    }
}
