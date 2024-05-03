<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\UsersRequests\AutoVerificationEmailRequest;
use App\Http\Requests\UsersRequests\DeleteAccountUserRequest;
use App\Http\Requests\UsersRequests\ForgotPasswordRequest;
use App\Http\Requests\UsersRequests\LoginUserRequest;
use App\Http\Requests\UsersRequests\LogoutUserRequest;
use App\Http\Requests\UsersRequests\ProfileUserRequest;
use App\Http\Requests\UsersRequests\RegisterUserRequest;
use App\Http\Requests\UsersRequests\ResendVerificationEmailRequest;
use App\Http\Requests\UsersRequests\ResetPasswordRequest;
use App\Http\Requests\UsersRequests\UpdatePasswordRequest;
use App\Http\Requests\UsersRequests\UpdateProfileRequest;
use App\Models\Users\User;
use App\Notifications\UserNotification\ResetPasswordNotification;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function register(RegisterUserRequest $request)
    {

        try {
            $user = User::create([
                "username" => $request->username,
                "email" => $request->email,
                "password" => bcrypt($request->password),
            ]);

            //TODO: enviar correo de verificación como una notificación personalizada
            $user->sendEmailVerificationNotification();

            $token = $user->createToken('myapptoken')->plainTextToken;

            return response()->json(['message' => 'User created successfully!', 'token' => $token], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error('Registrarion failed:', ['exception' => $e]);
            return response()->json(['error' => 'Failed to create user'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function verifyEmail($id_user, $hash)
    {

        $user = User::where('id_user', $id_user)->firstOrFail();

        if ($hash !== $user->verification_token) {
            return response()->json(['error' => 'Invalid token.'], Response::HTTP_UNAUTHORIZED);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['info' => 'Email has already been verified.'], Response::HTTP_OK);
        }

        $user->verification_token = null;
        $user->markEmailAsVerified();
        $user->save();

        return response()->json([
            'message' => 'Email verified successfully!',
            'redirectTo' => 'localhost:3000/',
        ], Response::HTTP_OK);
    }

    public function autoVerificationEmail(AutoVerificationEmailRequest $request)
    {
        $user = $request->user();
        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification email sent successfully!'], Response::HTTP_OK);
    }

    public function resendVerificationEmail(ResendVerificationEmailRequest $request)
    {

        $user = User::where('email', $request->email)->firstOrFail();

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification email sent successfully!'], Response::HTTP_OK);
    }

    public function login(LoginUserRequest $request)
    {
        try {

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'The credentials are incorrect.'], Response::HTTP_UNAUTHORIZED);
            }

            $token = $user->createToken('myapptoken')->plainTextToken;

            return response()->json(['message' => 'User logged in successfully!', 'token' => $token], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Failed to login'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function userProfile(ProfileUserRequest $request)
    {

        return response()->json(['user' => $request->user()], Response::HTTP_OK);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {

        $user = $request->user();

        $user->fill($request->validated())->save();

        return response()->json(['message' => 'Profile updated successfully!', 'user' => $user], Response::HTTP_OK);
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {

        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['message' => 'The old password is incorrect.'], Response::HTTP_UNAUTHORIZED);
        }

        $user->fill(['password' => Hash::make($request->new_password)])->save();

        return response()->json(['message' => 'Password changed successfully!'], Response::HTTP_OK);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['info' => 'No user found with this email.'], Response::HTTP_NOT_FOUND);
        }

        $token = Str::random(60);

        $user->password_reset_token = $token;
        $user->save();

        $user->notify(new ResetPasswordNotification($token, $request->email, $user->username));

        return response()->json(['Password reset link sent to your email!' => $token], Response::HTTP_OK);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || $user->password_reset_token !== $request->token) {
            return response()->json(['error' => 'Invalid token.'], Response::HTTP_UNAUTHORIZED);
        }

        $user->password = Hash::make($request->password);
        $user->password_reset_token = null;
        $user->save();

        return response()->json(['message' => 'Password reset successfully!'], Response::HTTP_OK);
    }

    public function logout(LogoutUserRequest $request)
    {

        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'User logged out successfully!'], Response::HTTP_OK);
    }

    public function deleteAccount(DeleteAccountUserRequest $request)
    {

        $request->user()->delete();

        return response()->json(['message' => 'User deleted successfully!'], Response::HTTP_OK);
    }
}