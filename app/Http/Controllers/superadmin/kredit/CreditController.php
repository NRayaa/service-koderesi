<?php

namespace App\Http\Controllers\superadmin\kredit;

use App\Http\Controllers\Controller;
use App\Models\CreditIn;
use App\Models\CreditOut;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CreditController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Mengambil data user dengan urutan berdasarkan waktu pembuatan
            $data = User::orderBy('created_at', 'desc')->paginate(10);

            // Mengambil data user terbaru
            $lastUser = User::orderBy('created_at', 'desc')->first();

            // Memeriksa apakah ada data user yang ditemukan
            if (!$lastUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'data tidak ditemukan',
                    'data' => []
                ], 200);
            }

            $id = $lastUser->id;

            $dataDetail = User::find($id);
            // Mengambil data detail kredit masuk (CreditIn) untuk user terbaru
            $dataDetailIn = CreditIn::where('user_id', $id)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
                ->groupBy('date')
                ->get();

            // Mengambil data detail kredit keluar (CreditOut) untuk user terbaru
            $dataDetailOut = CreditOut::where('user_id', $id)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
                ->groupBy('date')
                ->get();

            // Mengembalikan respons JSON dengan status sukses dan data yang ditemukan
            return response()->json([
                'success' => true,
                'message' => 'data tersedia',
                'data' => $data,
                'data_detail' => [
                    'detail' => $dataDetail,
                    'credit' => [
                        'in' => $dataDetailIn,
                        'out' => $dataDetailOut
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            // Mengembalikan respons JSON dengan status gagal dan pesan error
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Mengambil data user dengan paginasi
            $data = User::paginate(10);

            // Mengambil data detail user dengan relasi CreditIn dan CreditOut berdasarkan ID
            $dataDetail = User::find($id);
            $dataDetailIn = CreditIn::where('user_id', $id)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
                ->where('user_id', $id)
                ->groupBy('date')
                ->get();

            $dataDetailOut = CreditOut::where('user_id', $id)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
                ->where('user_id', $id)
                ->groupBy('date')
                ->get();

            // Mengembalikan respons JSON dengan status sukses dan data yang ditemukan
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $data,
                'data_detail' => [
                    'detail' => $dataDetail,
                    'credit' => [
                        'in' => $dataDetailIn,
                        'out' => $dataDetailOut
                    ]
                ]



            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Mengembalikan respons JSON dengan status gagal dan pesan error bahwa data tidak ditemukan
            return response()->json([
                'status' => false,
                'message' => 'Data not found',
            ], 404);
        } catch (\Exception $e) {
            // Mengembalikan respons JSON dengan status gagal dan pesan error
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
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
    public function updateKredit(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'amount' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data Tidak Valid',
                    'data' => $validator->errors()
                ]);
            }

            $user = User::findOrFail($id);

            if ($user->total_tokens > $request->amount) {
                // Jika jumlah amount lebih besar dari total tokens, masukkan ke dalam tabel CreditOut
                $creditOut = new CreditOut();
                $creditOut->user_id = $id;
                $creditOut->amount = $user->total_tokens - $request->amount;
                $creditOut->save();

                // Update total tokens user
                $user->total_tokens = $request->amount;
                $user->save();

                return response()->json([
                    'status' => true,
                    'message' => 'success, kredit berkurang',
                    'data' => $creditOut
                ]);
            }

            // Masukkan data ke dalam tabel CreditIn
            // dd($request->amount, $user->total_tokens, );
            $total = $request->amount - $user->total_tokens;
            $creditIn = new CreditIn();
            $creditIn->user_id = $id;
            $creditIn->amount = $total;
            $creditIn->save();

            // Update total tokens user
            $user->total_tokens = $request->amount;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'success, kredit bertambah',
                'data' => $creditIn
            ]);
        } catch (ModelNotFoundException $e) {
            // Menangani jika user tidak ditemukan
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        } catch (\Exception $e) {
            // Menangani kesalahan umum
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
