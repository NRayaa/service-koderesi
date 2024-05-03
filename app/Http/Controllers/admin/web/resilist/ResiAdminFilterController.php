<?php

namespace App\Http\Controllers\admin\web\resilist;

use App\Http\Controllers\Controller;
use App\Models\Waybill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResiAdminFilterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function filterByStatus(Request $request)
    {
        $userid = Auth::user()->id;
        $status = $request->input('status'); // Mengambil nilai dari parameter 'status' di URL

        // Filter berdasarkan status yang diberikan
        $waybills = Waybill::where('status', $status)
            ->where('user_id', $userid)
            ->with(['manifests' => function ($query) {
                $query->orderBy('date_manifest', 'desc')->take(1);
            }])
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data waybills filtered by status.',
            'data' => $waybills
        ], 200);
    }

}
