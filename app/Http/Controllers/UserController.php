<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('userType', 'department', 'employee')->get();

        return response()->json($users);
    }

    /**
     * Store a newly created user in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();
        $validated['password'] = bcrypt('123');

        $user = User::create($validated);

        return response()->json($user, 201);
    }

    /**
     * Display the specified user.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $user->load('userType', 'department');

        return response()->json($user);
    }

    /**
     * Update the specified user in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        if ($request->has('password')) {
            $validated['password'] = bcrypt('123');
        }

        $user->update($validated);

        return response()->json($user);
    }

    /**
     * Remove the specified user from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(null, 204);
    }
}
