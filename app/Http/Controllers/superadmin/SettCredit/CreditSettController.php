<?php

namespace App\Http\Controllers\superadmin\SettCredit;

use App\Http\Controllers\Controller;
use App\Models\CreditSett;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CreditSettController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $credit = CreditSett::all();

        if($credit->isEmpty()){
            return response()->json([
                'success' => false,
                'message' => 'Credit setting tidak ada',
                'data' => $credit
            ]);
        }

        return response()->json([
            'success'=>true,
            'message'=>'Credit setting ditemukan',
            'data'=>$credit
        ]);
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
            'credit_name' => 'required',
            'credit_reduc' => 'required'
        ]);

        $input = new CreditSett();
        $input->credit_name = $request->credit_name;
        $input->credit_reduc = $request->credit_reduc;
        $input->save();

        return response()->json([
            'success' => true,
            'message' => 'Credit created successfully',
            'data' => $input
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = CreditSett::find($id);
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
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
        $validator = Validator::make($request->all(), [
            'credit_name' => 'required',
            'credit_reduc' => 'required'
        ]);

        $input = CreditSett::find($id);

        $data = $input->update([
            'credit_name' => $request->credit_name,
            'credit_reduc' => $request->credit_reduc
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Credit updated successfully',
            'data' => $data
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = CreditSett::find($id);
        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Credit deleted successfully',
            'data' => $data
        ]);
    }
}
