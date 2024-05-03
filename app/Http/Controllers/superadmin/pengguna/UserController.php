<?php

namespace App\Http\Controllers\superadmin\pengguna;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = User::where('role', 'admin')->paginate(10);

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada pengguna',
                    'data' => $data
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data pengguna tersedia',
                'data' => $data
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
            ]);

            // Jika validasi gagal
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $dataUser = new User;
            $dataUser->name = $request->name;
            $dataUser->email = $request->email;
            $dataUser->password = Hash::make($request->password);
            $dataUser->role = 'admin';
            $dataUser->key = Str::random(16);
            $dataUser->save();

            $dataProfile = new Profile;
            $dataProfile->name = $request->name;
            $dataProfile->email = $request->email;
            $dataProfile->user_id = $dataUser->id;
            $dataProfile->save();

            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'data' => $dataUser
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = User::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data user tidak tersedia',
                'data' => $data
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data user tersedia',
            'data' => $data
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name'=> 'required',
            'email'=> 'required',
            'password'=> 'nullable',
        ]);

        $data = User::find($id);
        $profile = Profile::where('user_id', $id)->first();

        if ($request->password) {
            $data->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $profile->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User tealh diupdate',
                'data' => $data
            ]);
        }

        $data->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);
        $profile->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User telah diupdate',
            'data' => $data
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = User::find($id);
        $data->delete();
        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ], 200);
    }
}
