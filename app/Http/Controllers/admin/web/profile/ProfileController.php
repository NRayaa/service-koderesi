<?php

namespace App\Http\Controllers\admin\web\profile;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userid = Auth::user()->id;
        $data = Profile::where('user_id', $userid)->first();

        // Membuat URL gambar jika gambar tersedia
        $imageUrl = null;
        if ($data && $data->image) {
            // Menggunakan Storage untuk mengakses gambar
            $imageUrl = url("images/" . $data->image);
        }

        // Menambahkan URL gambar ke dalam data
        $data->image_url = $imageUrl;

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function uploadImage(Request $request)
    {
        $userid = Auth::user()->id;
        $data = Profile::where('user_id', $userid)->first();

        $validator = Validator::make($request->all(), [
            'image' => 'required|image', // Menambahkan validasi untuk tipe dan ukuran gambar
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Jika sudah ada foto, hapus foto sebelumnya
        if ($data->image != null) {
            $oldImagePath = public_path('images/') . $data->image;
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath); // Hapus foto lama
            }
        }

        // Simpan foto baru
        $imageName = time() . '.' . $request->image->extension();
        $request->image->move(public_path('images'), $imageName);

        // Update record dengan foto baru
        $data->update([
            'image' => $imageName
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'data' => $data
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'username' => 'nullable',
            'phone_number' => 'nullable',
        ]);

        $userid = Auth::user()->id;
        $profile = Profile::where('user_id', $userid)->first();

        $profile->update([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'phone_number' => $request->phone_number,
            'user_id' => $userid
        ]);

        return response()->json([
            'success' => true,
            'data' => $profile
        ], 200);
    }

    public function deleteProfile(Request $request)
    {
        // Hapus foto pengguna
        $userid = Auth::id();
        $user = User::find($userid);
        $data = Profile::where('user_id', $userid)->first();

        // Jika ada foto, hapus foto sebelumnya
        if ($data && $data->image) {
            $oldImagePath = public_path('images/') . $data->image;
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath); // Hapus foto lama
            }
        }

        // Update record dengan menghapus foto
        $data->update(['image' => null]);

        // Hapus data profil
        $data->delete();
        $user->delete();
        auth()->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image and profile data deleted successfully'
        ], 200);
    }
}
