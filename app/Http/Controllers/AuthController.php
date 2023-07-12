<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $r)
    {
        try {
            $data = $r->validated();
            // return $data['email'];
            $pass= Hash::make($data['password']);
            $username = strstr($data['email'], '@', true);
            $user = User::create([
                'username'=>$username,
                'password'=>$pass,
                'email'=> $data['email']
            ]);
            $token = $user->createToken(User::USER_TOKEN);

            return $this->success([
                'user' => $user,
                'token' => $token->plainTextToken,

            ], 'User has been registred Successfully');
        } catch (Exception $e) {
            return $e->getLine();
        }
    }
    public function login(LoginRequest $loginRequest)
    {
        $isValid = $this->isValidCredential($loginRequest);
        if (!$isValid['success']) {
            return $this->error($isValid['message'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $user = $isValid['user'];
        $token = $user->createToken(User::USER_TOKEN);

        return $this->success([
            'user' => $user,
            'token' => $token->plainTextToken,

        ], 'User has been registred Successfully');
    }
    public function isValidCredential(LoginRequest $loginRequest)
    {
        $data = $loginRequest->validated();
        $user = User::where('email', $data['email'])->first();
        if ($user == null) {
            return [
                'success' => false,
                'message' => 'Invalid Credentials'
            ];
        }
        if (Hash::check($data['password'], $user->password)) {
            return [
                'success' => true,
                'user' => $user
            ];
        }
        return [
            'success' => false,
            'message' => 'Incorrect Password'
        ];
    }

    /**
     * Logins a user with token
     *
     * @return JsonResponse
     */
    public function loginWithToken(): JsonResponse
    {
        return $this->success(auth()->user(), 'Login successfully!');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logout successfully!');
    }
}
