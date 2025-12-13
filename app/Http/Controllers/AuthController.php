<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Interfaces\IAuthService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{

    public function __construct(
        protected readonly IAuthService $authService
    ) {
    }

    public function register(RegisterRequest $request)
    {
        $validatedData = $request->validated();
        $RegisterResult = $this->authService->register(
            $validatedData['name'],
            $validatedData['password'],
            $validatedData['email']
        );

        return (new UserResource($RegisterResult))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);

    }

    public function login(LoginRequest $request)
    {

        $validatedData = $request->validated();

        $userData = $this->authService->login(
            $validatedData['email'],
            $validatedData['password']
        );

        return (new UserResource($userData))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
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
