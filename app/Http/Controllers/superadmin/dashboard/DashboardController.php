<?php

namespace App\Http\Controllers\superadmin\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Waybill;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexTotal()
    {
        try {
            $totalUser = User::where('role', 'user')->count();
            $totalRevenue = Transaction::sum('amount_bill');
            $totalWaybill = Waybill::count();
            $totalWaybillOP = Waybill::whereNotIn('status', ['delivered', 'returned', 'reject'])->count();
            $totalWaybillDelivered = Waybill::where('status', 'delivered')->count();

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => 'Data Total Dashboard',
                'data' => [
                    'Total User' => $totalUser,
                    'Total Revenue' => $totalRevenue,
                    'Total Waybill' => $totalWaybill,
                    'Total Waybill OP' => $totalWaybillOP,
                    'Total Waybill Delivered' => $totalWaybillDelivered
                ],

            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Error occurred while fetching data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function indexUser()
    {
        try {
            $newestUsers = User::orderBy('created_at', 'desc')->take(5)->get();

            return response()->json([
                'success' => true,
                'message' => 'Data User Dashboard',
                'data' => $newestUsers
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred while fetching newest users data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function indexTransaction()
    {
        try {
            $newestTransactions = Transaction::orderBy('created_at', 'desc')->take(5)->get();

            $transactions = $newestTransactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'user_name' => $transaction->user->name, // Ambil nama pengguna
                    'method_payment' => $transaction->method_payment, // Perbaikan penulisan
                    'code_transaction' => $transaction->code_transaction,
                    'amount_bill' => $transaction->amount_bill,
                    'amount_credit' => $transaction->amount_credit,
                    'transaction_date' => $transaction->transaction_date,
                    'transaction_time' => $transaction->transaction_time,
                    'status' => $transaction->status
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Data Transaksi Dashboard',
                'data' => $transactions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred while fetching newest transactions data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
