<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Http\Controllers\Customer\BaseController;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;


class CustomerAuthController extends BaseController
{
    public function register(Request $request)
    {

        // $request->validate([
        //     'name' => 'required|string|max:50',
        //     'username' => 'required|string|max:50|unique:admins',
        //     'email' => 'required|string|email|max:255|unique:admins',
        //     'password' => 'required|string|min:6|confirmed',
        //     'phone_number' => 'required|string',
        // ]);

        $finddata=Customer::where('email',$request->email)
            ->orWhere('name',operator: $request->name)
            ->orWhere('username',$request->username)
            ->orWhere('phone_number',$request->phone_number)->first();

        if($finddata){
            return $this->sendError('Data already exists', 'Data already exists');
        }
        $admin = Customer::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'status'=>"Active",
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
        ]);

        return $this->sendResponse($admin, 'Register Success');
    }
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('customer')->attempt($credentials)) {
            return response()->json(['success'=>false,'message' => 'Invalid credentials'], 401);
        }


        $user = Auth::guard('customer')->user();

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

            // dd(JWTAuth::getToken());
            if (!JWTAuth::getToken()) {
                return response()->json(['error' => 'No token provided'], 401);
            }

            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json(['message' => 'Successfully logged out'], 200);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to logout, please try again.'], 500);
        }
    }



}
