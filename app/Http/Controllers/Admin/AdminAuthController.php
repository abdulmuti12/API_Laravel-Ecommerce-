<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\Admin\BaseController;



class AdminAuthController extends BaseController
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            return $this->sendError('Unauthorized', 'Invalid credentials');
        }

        $user = Auth::user();

        return $this->sendResponse([
            "id" => $user->id,
            "username" => $user->username,
            "email" => $user->email,
            "token" => $token
        ], 'Login Success');
    }
    public function logout(Request $request)
    {
        try {
            $token = JWTAuth::getToken();
            if (!$token) {
                return response()->json(['error' => 'No token provided'], 401);
            }

            JWTAuth::invalidate($token);

            return response()->json(['message' => 'Successfully logged out'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to logout, please try again.'], 500);
        }

    }
    public function register(Request $request)
    {

        // $request->validate([
        //     'name' => 'required|string|max:50',
        //     'username' => 'required|string|max:50|unique:admins',
        //     'email' => 'required|string|email|max:255|unique:admins',
        //     'password' => 'required|string|min:6|confirmed',
        //     'phone_number' => 'required|string',
        // ]);

        $finddata=Admin::where('email',$request->email)
            ->orWhere('name',operator: $request->name)
            ->orWhere('username',$request->username)
            ->orWhere('phone_number',$request->phone_number)->first();

        if($finddata){
            return $this->sendError('Data already exists', 'Data already exists');
        }
        $admin = Admin::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
        ]);

        // Buat token JWT untuk admin yang baru terdaftar
        $token = JWTAuth::fromUser($admin);

        return $this->sendResponse($admin, 'Register Success');
    }

    protected  function  respondWithToken($token)
    {
       return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }
}
