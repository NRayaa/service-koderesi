<?php

namespace App\Http\Controllers\admin\web\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Manifest;
use App\Models\Waybill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userid = Auth::user()->id;
        $manifest = Manifest::where('user_id', $userid)
        ->select('id', 'note', 'status', 'date_manifest', 'waybill_id')
        ->with(['waybill' => function ($query) {
            $query
            ->select('user_id', 'waybill', 'id')
            ->take(1);
        }])
        ->orderBy('date_manifest', 'desc')
        ->take(5)->get();


        return response()->json([
            'success' => true,
            'message' => 'Data Dashboard',
            'data' => $manifest
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function statistic()
    {
        $userid = Auth::user()->id;
        $totalResi = Waybill::where('user_id', $userid)->count();
        $totalOnProgress = Waybill::where('user_id', $userid)
        ->whereIn('status', ['confirmed', 'allocated', 'pickingUp', 'picked', 'droppingOff'])
        ->count();
        $totalDelivered = Waybill::where('user_id', $userid)
        ->where('status', 'delivered')
        ->count();

        return response()->json([
            'success' => true,
            'status' => 'Data Statistic',
            'data' => [
                'total_resi' => $totalResi,
                'total_on_progress' => $totalOnProgress,
                'total_delivered' => $totalDelivered
            ]
            ]);

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
