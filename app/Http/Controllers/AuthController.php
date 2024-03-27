<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function RegisterFromAdmin(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                // 'role' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->role = 'user';

            // Lakukan simpan user ke dalam database
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditambah',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function login(Request $request){
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|string',
    ]);
    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'error' => $validator->errors()->first()
        ], 400);
    }

    if(!Auth::attempt($request->only(['email', 'password']))){
        return response()->json([
            'success' => false,
            'message' => 'Invalid login details'
        ], 401);
    } else {
        $user = Auth::user();
        $role = $user->role;
        $token = $user->createToken('auth_token', [$role])->plainTextToken;
        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'role' => $role,
        ], 200);

    }
}
}
