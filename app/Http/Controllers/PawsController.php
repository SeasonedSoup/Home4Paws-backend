<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PawsListings;

class PawsController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'caption' => 'required|string',
            'location' => 'required|string'
        ]);

        $paws = PawsListings::create([
            'user_id' => auth()->id(),
            'caption' => $request->caption,
            'location' => $request->location,
            'status' => 'available',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $paws
        ], 201);
    }

    public function index()
    {
        $paws = PawsListings::with(['user', 'photos'])
            ->latest()
            ->take(20)
            ->get();

        return response()->json([
            'message' => 'PAWS posts fetched successfully',
            'data' => $paws
        ]);
    }

    public function markAdopted($id)
    {
        $paws = PawsListings::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $paws->update(['status' => 'adopted']);

        return response()->json([
            'message' => 'PAWS post marked as adopted'
        ]);
    }

    public function destroy($id)
    {
        $paws = PawsListings::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $paws->delete();

        return response()->json([
            'message' => 'PAWS post deleted'
        ]);
    }
}
