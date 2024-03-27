<?php

namespace App\Http\Controllers\pengguna;

use App\Http\Controllers\Controller;
use App\Models\User;
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
            $data = User::where('role', 'user')->paginate(10);

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'status' => 'Database empty',
                    'message' => 'Tidak ada pengguna'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'status' => 'success',
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
            $dataUser->role = 'user';
            $dataUser->save();

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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
