<?php

namespace App\Http\Controllers\admin\web\resilist;

use App\Http\Controllers\Controller;
use App\Http\Controllers\superadmin\SettCredit\CreditSettController;
use App\Models\CreditOut;
use App\Models\CreditSett;
use App\Models\Manifest;
use App\Models\User;
use App\Models\Waybill;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ResiAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
        $userid = Auth::id();
        $waybills = Waybill::where('user_id', $userid)
        ->select('id', 'title', 'waybill', 'status', 'courier', 'origin', 'destination', 'origin_address', 'destination_address',)
        ->where('display_status', 'display')
        ->with(['manifests' => function ($query) {
            $query->select('id', 'waybill_id', 'note', 'status', 'date_manifest')
            ->orderBy('date_manifest', 'desc')
            ->take(1);
        }])
        ->get();

        if($waybills->isEmpty()) {
            return response()->json([
                'success'=>false,
                'message' => 'Tidak ada data waybill yang tersedia.',
                'data' => $waybills
                ], 200);
        }

        return response()->json([
            'success'=>true,
            'message' => 'Data waybill yang tersedia.',
            'data'=>$waybills
        ], 200);

        } catch (QueryException $e) {
            // Masalah di database
            return response()->json([
                'success'=>false,
                'message' => 'Terjadi kesalahan saat memproses data.',
                ], 500);
        } catch (\Exception $e) {
            // Tangani pengecualian umum lainnya
            return response()->json([
                'success'=>false,
                'message' => 'Terjadi kesalahan internal.'
                ], 500);
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

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'waybill_id' => 'required',
            'courier_code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $idwaybill = $request->input('waybill_id');

        if (Waybill::where('waybill', $idwaybill)->where('user_id', Auth::id())->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Data waybill sudah ada.',
                'waybill_id' => $idwaybill
            ]);
        }

        try {
            $waybillId = $request->input('waybill_id');
            $courierCode = $request->input('courier_code');

            $baseURL = 'https://api.biteship.com';
            $apiKey = 'biteship_live.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYW1lIjoiS29kZXJlc2kiLCJ1c2VySWQiOiI2NWY5YWIyZDI3MmNhODAwMTE1NGNlM2YiLCJpYXQiOjE3MTA5OTUwNjR9.pErJQ1rdJ0bhkf6q45YfGZgdNu7Ij9vh3dFZH3B8CCw';

            $client = new Client(['base_uri' => $baseURL]);

            $response = $client->get("/v1/trackings/{$waybillId}/couriers/{$courierCode}", [
                'headers' => [
                    'Authorization' => $apiKey,
                    'Content-Type' => 'application/json',
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data['success']) && $data['success'] === false) {
                return response()->json(['error' => $data['message']], 500);
            }

            $userid = Auth::id();
            $user = User::where('id', $userid)->first();
            $userCredit = $user->total_tokens;

            // $creditSett = CreditSett::where('credit_name', 'cek_resi')->credit_reduc;

            // if($userCredit == 0){
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Credit anda telah habis | Mohon isi ulang',
            //     ]);
            // }

            // $updateCredit = $userCredit - $creditSett;

            // $user->update([
            //     'total_tokens' => $updateCredit
            // ]);

            // $creditOut = CreditOut::create([
            //     'user_id' => $userid,
            //     'credit_out' => $creditSett
            // ]);


            $waybill = Waybill::create([
                'waybill' => $waybillId,
                'title' => $request->title,
                'courier' => $data['courier']['company'],
                'origin' => $data['origin']['contact_name'],
                'destination' => $data['destination']['contact_name'],
                'origin_address' => $data['origin']['address'],
                'destination_address' => $data['destination']['address'],
                'status' => $data['status'],
                'display_status' => 'display',
                'status_loop' => 'none',
                'user_id' => $userid
            ]);

            foreach ($data['history'] as $history) {
                $updatedAt = Carbon::parse($history['updated_at']);

                Manifest::create([
                    'note' => $history['note'],
                    'status' => $history['status'],
                    'waybill_id' => $waybill->id,
                    'user_id' => $userid,
                    'date_manifest' => $updatedAt
                ]);
            }

            return response()->json([
                'message' => 'Data waybill dan manifest berhasil disimpan.',
                'data' => $waybill,
                // 'credit' => $updateCredit
            ]);
        } catch (ClientException $e) {
            // Tangani kesalahan jika request ke API gagal
            $errorResponse = json_decode($e->getResponse()->getBody(), true);
            $errorMessage = isset($errorResponse['error']) ? $errorResponse['error'] : 'Unknown error occurred.';
            return response()->json(['error' => $errorMessage], 400);
        } catch (\Exception $e) {
            // Tangani kesalahan umum
            return response()->json(['error' => 'Terjadi kesalahan saat menyimpan data.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $userid = Auth::id();
            $data = Waybill::where('id', $id)
            ->where('user_id', $userid)
            ->with(['manifests' => function ($query) {
                $query->orderBy('date_manifest', 'desc');
            }])->first();


            return response()->json(compact('data'));
        } catch (ModelNotFoundException $e) {
            // Tangani jika data tidak ditemukan
            return response()->json([
            'status' => false,
            'error' => 'Data Tidak ada',
            ], 404);
        } catch (\Exception $e) {
            // Tangani kesalahan umum lainnya
            return response()->json(['error' => 'Something went wrong'], 500);
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
        $waybill = Waybill::findOrFail($id)->with(['manifests' => function ($query) {
            $query->orderBy('date_manifest', 'desc');
        }])->first();;

        $latest_manifest = Manifest::where('waybill_id', $id)
                                ->orderBy('date_manifest', 'desc')
                                ->first();

        // $countManifest = $waybill->manifests->count();
        $waybill_id = $waybill->waybill;
        $courier_id = $waybill->courier;

        $baseURL = 'https://api.biteship.com';
        $apiKey = 'biteship_live.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYW1lIjoiS29kZXJlc2kiLCJ1c2VySWQiOiI2NWY5YWIyZDI3MmNhODAwMTE1NGNlM2YiLCJpYXQiOjE3MTA5OTUwNjR9.pErJQ1rdJ0bhkf6q45YfGZgdNu7Ij9vh3dFZH3B8CCw';

        $client = new Client(['base_uri' => $baseURL]);

        $response = $client->get("/v1/trackings/{$waybill_id}/couriers/{$courier_id}", [
            'headers' => [
                'Authorization' => $apiKey,
                'Content-Type' => 'application/json',
            ]
            ]);

            $userid = Auth::id();
            $user = User::where('id', $userid)->first();
            $userCredit = $user->total_tokens;

            $creditSett = CreditSett::where('credit_name', 'cek_resi')->credit_reduc;

            if($userCredit == 0){
                return response()->json([
                    'success' => false,
                    'message' => 'Credit anda telah habis | Mohon isi ulang',
                ]);
            }

            $updateCredit = $userCredit - $creditSett;

            $user->update([
                'total_tokens' => $updateCredit
            ]);

        $data = json_decode($response->getBody(), true);
        usort($data['history'], function($a, $b) {
            return strtotime($b['updated_at']) - strtotime($a['updated_at']);
        });

        // Ambil catatan sejarah dengan tanggal terbaru dari data yang di-decode
        $latest_history = reset($data['history']);
        $latest_history['updated_at'] = date('Y-m-d H:i:s', strtotime($latest_history['updated_at']));

        if (strtotime($latest_manifest->date_manifest) <= strtotime($latest_history['updated_at'])) {
            Manifest::create([
                'waybill_id' => $waybill->id,
                'date_manifest' => $latest_history['updated_at'],
                'note' => $latest_history['note'],
                'status' => $latest_history['status'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil update manifest',
                'data' => $latest_history
            ]);
        }

        // Mengembalikan respons JSON dengan catatan sejarah terbaru
        return response()->json([
            'success' => false,
            'message' => 'Gagal update manifest',
            'data' => 'Tidak ada data terbaru'
        ]);


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $waybill = Waybill::find($id);
        $waybill->manifests()->delete();
        $waybill->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data waybill berhasil dihapus.'
        ], 200);
    }

    public function archive(string $id) {
        Waybill::where('id', $id)->update(['display_status' => 'archive']);
        $data = Waybill::find($id);
        return response()->json([
            'success' => true,
            'message' => 'Data waybill berhasil diarchive.',
            'waybill_id' => $data
        ]);
    }

    public function search(Request $request){
        $request->validate([
            'q' => 'required|string', // Parameter untuk pencarian
        ]);

        // Ambil parameter pencarian dari request
        $query = $request->input('q');

        // Lakukan pencarian
        $waybills = Waybill::where('user_id', Auth::id())
                            ->where(function ($queryBuilder) use ($query) {
                                $queryBuilder->where('waybill', 'LIKE', "%$query%")
                                             ->orWhere('title', 'LIKE', "%$query%");
                            })
                            ->get();

        // Jika hasil pencarian tidak ditemukan
        if ($waybills->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data waybill tidak ditemukan',
            ]);
        }

        // Kembalikan hasil pencarian
        return response()->json([
            'success' => true,
            'data' => $waybills
        ]);

    }
}
