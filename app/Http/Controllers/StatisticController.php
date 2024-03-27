<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Waybill;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexHalamanResiList()
    {
        try {
            $waybillAll = Waybill::all()->count();
            $waybillOnProgress = Waybill::whereNotIn('status', ['delivered', 'returned', 'reject'])->count();
            $waybillDelivered = Waybill::where('status', 'delivered')->count();

            return response()->json([
                'status' => 'success',
                'message' => 'Data Statistik halaman Resi List Berhasil di tampilkan',
                'data' => [
                    'all_resi_total' => $waybillAll,
                    'on_progress_resi' => $waybillOnProgress,
                    'delivered_resi' => $waybillDelivered
                ]
            ], 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch Resi List statistics: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function indexHalamanKredit()
    {
        try {
            $totalCredit = User::sum('total_tokens');

            if ($totalCredit === null) {
                throw new \Exception('Failed to retrieve total credit.');
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Data Statistik halaman Kredit Berhasil di tampilkan',
                'data' => $totalCredit
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function indexHalamanTransaksi(){
        try {
            $totalRevenue = Transaction::sum('amount_bill');

            if ($totalRevenue === null) {
                throw new \Exception('Failed to retrieve total revenue.');
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Data Statistik halaman Transaksi Berlangsung di tampilkan',
                'data' => $totalRevenue
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
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
