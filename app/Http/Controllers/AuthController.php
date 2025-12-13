<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Interfaces\IAuthService;
use Illuminate\Http\Request;
class AuthController extends Controller
{
    protected $authService;

    public function __construct(
        IAuthService $authService
    ) {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        $validatedData = $request->validated();
        $RegisterResult = $this->authService->register(
            $validatedData['name'],
            $validatedData['password'],
            $validatedData['email']
        );

        return response()->json(
            $RegisterResult,
            201
        );
    }

    public function login(LoginRequest $request)
    {

        $validatedData = $request->validated();

        $userData = $this->authService->login(
            $validatedData['email'],
            $validatedData['password']
        );

        return response()->json($userData);
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
