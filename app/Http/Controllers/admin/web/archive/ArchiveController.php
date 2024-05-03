<?php

namespace App\Http\Controllers\admin\web\archive;

use App\Http\Controllers\Controller;
use App\Models\Waybill;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArchiveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
        $userid = Auth::id();
        $waybills = Waybill::where('user_id', $userid)
        ->where('display_status', 'archive')
        ->with(['manifests' => function ($query) {
            $query->orderBy('date_manifest', 'desc')->take(1);
        }])
        ->get();

        if($waybills->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada data waybill yang tersedia.',
                'data' => $waybills
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data waybill yang tersedia.',
            'data' => $waybills
        ], 200);

        } catch (QueryException $e) {
            // Masalah di database
            return response()->json(['error' => 'Terjadi kesalahan saat memproses data.'], 500);
        } catch (\Exception $e) {
            // Tangani pengecualian umum lainnya
            return response()->json(['error' => 'Terjadi kesalahan internal.'], 500);
        }
    }

    public function unarchive(string $id)
    {
        Waybill::where('id', $id)->update(['display_status' => 'display']);
        $data = Waybill::find($id);
        return response()->json([
            'success' => true,
            'message' => 'Data waybill berhasil diarchive.',
            'waybill_id' => $data
        ]);
    }
}
