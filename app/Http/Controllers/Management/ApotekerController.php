<?php

namespace App\Http\Controllers\Management;

use App\Models\Apoteker;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ApotekerController extends Controller
{
    public function createApoteker(Request $request)
    {
        $request->validate([
            'nama_apoteker' => ['required', 'string', 'max:100'],
            'email_apoteker' => ['required', 'email', 'unique:apoteker,email_apoteker'],
            'no_hp_apoteker' => ['required'],
        ]);

        $dataApoteker = Apoteker::create([
            'nama_apoteker' => $request->nama_apoteker,
            'email_apoteker' => $request->email_apoteker,
            'no_hp_apoteker' => $request->no_hp_apoteker,
        ]);

        return response()->json(['status' => 201, 'data' => $dataApoteker, 'message' => 'Data Berhasil Di Tambahkan']);
    }

    public function getApotekerById($id)
    {
        $apoteker = Apoteker::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $apoteker
        ]);
    }

    public function updateApoteker(Request $request, $id)
    {
        $dataApoteker = Apoteker::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_apoteker'          => 'required|string|max:255',
            'email_apoteker'         => 'required|email|unique:apoteker,email_apoteker,' . $dataApoteker->id,
            'no_hp_apoteker'         => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $dataApoteker->update([
            'nama_apoteker' => $request->nama_apoteker,
            'email_apoteker' => $request->email_apoteker,
            'no_hp_apoteker' => $request->no_hp_apoteker,
        ]);

        return response()->json(['status' => 200, 'data' => $dataApoteker, 'message' => 'Data Berhasil Di Update']);
    }

    public function deleteApoteker($id)
    {
        $dataApoteker = Apoteker::findOrFail($id);
        $dataApoteker->delete();

        return response()->json([
            'success' => true,
            'data' => $dataApoteker,
            'message' => 'Data Berhasil Di Dihapus',
        ], 200);
    }
}
