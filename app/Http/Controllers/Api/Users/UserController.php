<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\UsersRequests\DeleteAccountUserRequest;
use App\Http\Requests\UsersRequests\LoginUserRequest;
use App\Http\Requests\UsersRequests\LogoutUserRequest;
use App\Http\Requests\UsersRequests\ProfileUserRequest;
use App\Http\Requests\UsersRequests\RegisterUserRequest;
use App\Http\Requests\UsersRequests\UpdatePasswordRequest;
use App\Http\Requests\UsersRequests\UpdateProfileRequest;
use App\Models\Users\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function register(RegisterUserRequest $request) {

        try {
            $user = User::create([
                "username" => $request->username,
                "email" => $request->email,
                "password" => bcrypt($request->password),
            ]);

            $token = $user->createToken('myapptoken')->plainTextToken;

            return response()->json(['message' => 'User created successfully!', 'token' => $token], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['message' => 'Failed to create user'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function login(LoginUserRequest $request) {

        try {

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'The credentials are incorrect.'], Response::HTTP_UNAUTHORIZED);
            }

            $token = $user->createToken('myapptoken')->plainTextToken;

            return response()->json(['message' => 'User logged in successfully!', 'token' => $token], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['message'=> 'Failed to login'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function userProfile(ProfileUserRequest $request) {

        return response()->json(['user' => $request->user()], Response::HTTP_OK);
    }

    public function updateProfile(UpdateProfileRequest $request) {

        $user = $request->user();

        $user->fill($request->validated())->save();

        return response()->json(['message' => 'Profile updated successfully!', 'user' => $user], Response::HTTP_OK);

    }

    public function updatePassword(UpdatePasswordRequest $request) {

        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['message' => 'The old password is incorrect.'], Response::HTTP_UNAUTHORIZED);
        }

        $user->fill(['password' => Hash::make($request->new_password)])->save();

        return response()->json(['message' => 'Password changed successfully!'], Response::HTTP_OK);

    }

    public function logout(LogoutUserRequest $request) {

        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'User logged out successfully!'], Response::HTTP_OK);
    }

    public function deleteAccount(DeleteAccountUserRequest $request) {

        $request->user()->delete();

        return response()->json(['message' => 'User deleted successfully!'], Response::HTTP_OK);
    }
}
