<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ], [
            'email.required' => 'Email is required',
            'email.email' => 'Email is not valid',
            'email.unique' => 'Email is already registered',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        Profile::create([
            'user_id' => $user->id
        ]);

        $token = $user->createToken('UserToken')->accessToken;

        return response()->json([
            'status' => 'OK',
            'token' => $token
        ], 200);
    }

    public function login(Request $request)
    {
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (auth()->attempt($data)) {
            $token = auth()->user()->createToken('UserToken')->accessToken;
            return response()->json([
                'status' => 'OK',
                'token'  => $token
            ], 200);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function profile()
    {
        $user = auth()->user()->id;
        $profile = Profile::where('user_id', $user)->first();

        $results = [
            'id' => $profile->user_id,
            'nama' => $profile->user->name,
            'telp' => $profile->telp,
            'alamat' => $profile->alamat
        ];

        return response()->json([
            'status' => 'OK',
            'results' => [
                'data' => $results
            ]
        ], 200);
    }

    public function updateProfile (Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'telp' => 'required|string|max:15',
            'alamat' => 'required|string|max:255'
        ], [
            'nama.required' => 'Nama is required',
            'nama.max' => 'Nama is too long',
            'telp.required' => 'Telp is required',
            'telp.max' => 'Telp is too long',
            'alamat.required' => 'Alamat is required',
            'alamat.max' => 'Alamat is too long'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user()->id;
        $profile = Profile::where('user_id', $user)->first();

        $profile->user->update([
            'name' => $request->nama
        ]);

        $profile->where('user_id', $user)->update([
            'telp' => $request->telp,
            'alamat' => $request->alamat
        ]);

        return response()->json([
            'status' => 'OK',
            'message' => 'Profile updated successfully'
        ], 200);
    }
}
