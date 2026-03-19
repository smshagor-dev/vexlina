<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SteadfastKey;

class SteadfastKeyController extends Controller
{
    public function index()
    {
        $steadfastKey = SteadfastKey::first();
        return view('steadfast.index', compact('steadfastKey'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'steadfast_api_key'       => 'required|string',
            'steadfast_secret_key'    => 'required|string',
            'steadfast_base_url'      => 'required|url',
            'steadfast_webhook_token' => 'required|string',
        ]);

        // Always update the single config row
        $steadfastKey = SteadfastKey::updateOrCreate(
            ['id' => 1],
            $validated
        );

        return response()->json([
            'status'  => true,
            'message' => 'Steadfast settings updated successfully',
            'data'    => $steadfastKey
        ]);
    }
}
