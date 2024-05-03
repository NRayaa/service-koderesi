<?php

namespace App\Http\Controllers\superadmin\transaksi;

use App\Http\Controllers\Controller;
use App\Models\CreditIn;
use App\Models\CreditOut;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = User::with(['transaction' => function ($query) {
                $query->select('user_id', DB::raw('SUM(amount_bill) as total_amount_bill'), DB::raw('SUM(amount_credit) as total_amount_credit'))
                    ->groupBy('user_id');
            }])
                ->orderBy('created_at', 'desc')
                ->get();

            $userDetail = User::with(['transaction' => function($query){
                $query->orderBy('transaction_date', 'desc');
            }])->orderBy('created_at', 'desc')->first();

            if (!$userDetail) {
                throw new \Exception('User detail not found.');
            }

            $userId = $userDetail->id;

            $dataDetailIn = CreditIn::where('user_id', $userId)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
                ->where('user_id', $userId)
                ->groupBy('date')
                ->get();

            $dataDetailOut = CreditOut::where('user_id', $userId)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
                ->where('user_id', $userId)
                ->groupBy('date')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data Transaksi',
                'data' => $data,
                'detail' => [
                    'detail' => $userDetail,
                    'preview' => [
                        'in' => $dataDetailIn,
                        'out' => $dataDetailOut
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'methode_payment' => 'required',
                'code_transaction' => 'required',
                'amount_bill' => 'required',
                'amount_credit' => 'required',
                'transaction_date' => 'required',
                'transaction_time' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()
                ]);
            }

            // Cari pengguna berdasarkan user_id
            $user = User::find($request->user_id);

            // Jika pengguna tidak ditemukan
            if (!$user) {
                throw new \Exception('User not found.');
            }

            // Tambahkan kredit ke total_tokens pengguna
            $addToken = $user->total_tokens + $request->amount_credit;

            // Lakukan update total_tokens
            $user->total_tokens = $addToken;
            $user->save();

            // Simpan data transaksi
            $data = new Transaction();
            $data->fill($request->all());
            $data->status = 'success';
            $data->save();

            // Simpan detail transaksi kredit
            $detail = new CreditIn();
            $detail->user_id = $request->user_id;
            $detail->amount = $request->amount_credit;
            $detail->save();

            return response()->json([
                'success' => true,
                'message' => 'Data Transaksi Berhasil',
                'data' => $data,
                'recent_credit' => $addToken,
                'data_detail' => $detail
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $userId)
    {
        try {
            $data = User::with(['transaction' => function ($query) {
                $query->select('user_id', DB::raw('SUM(amount_bill) as total_amount_bill'), DB::raw('SUM(amount_credit) as total_amount_credit'))
                    ->groupBy('user_id');
            }])
                ->orderBy('created_at', 'desc')
                ->get();

            $userDetail = User::find($userId);

            // Jika user dengan ID yang diberikan tidak ditemukan
            if (!$userDetail) {
                throw new \Exception('User not found.');
            }

            $userDetail->load(['transaction' => function($query){
                $query->orderBy('transaction_date', 'desc');
            }]);

            $dataDetailIn = CreditIn::where('user_id', $userId)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
                ->where('user_id', $userId)
                ->groupBy('date')
                ->get();

            $dataDetailOut = CreditOut::where('user_id', $userId)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
                ->where('user_id', $userId)
                ->groupBy('date')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data Transaksi',
                'data' => $data,
                'detail' => [
                    'detail' => $userDetail,
                    'preview' => [
                        'in' => $dataDetailIn,
                        'out' => $dataDetailOut
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
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
    public function update(Request $request, string $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
