<?php

namespace App\Http\Controllers\superadmin\resilist;

use App\Http\Controllers\Controller;
use App\Models\Waybill;
use Illuminate\Http\Request;

class WaybillFilterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function filterByStatus(Request $request)
    {
        $status = $request->input('status'); // Mengambil nilai dari parameter 'status' di URL

        // Filter berdasarkan status yang diberikan
        $waybills = Waybill::where('status', $status)
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

    /**
     * Show the form for creating a new resource.
     */

}
