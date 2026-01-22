<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PawsListing;
use App\Models\Reaction;

class PawsController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'caption' => 'required|string',
            'location' => 'required|string'
        ]);

        $paws = PawsListing::create([
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
        $paws = PawsListing::with(['user', 'photos', 'reactions'])
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
        $paws = PawsListing::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $paws->update(['status' => 'adopted']);

        return response()->json([
            'message' => 'PAWS post marked as adopted'
        ]);
    }

    public function destroy($id)
    {
        $paws = PawsListing::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $paws->delete();

        return response()->json([
            'message' => 'PAWS post deleted'
        ]);
    }

    public function like($id)
    {
        Reaction::firstOrCreate([
            'paws_id' => $id,
            'reacted_by' => auth()->id(),
            'reaction_type' => 'like',
        ]);

        return response()->json([
            'message' => 'Post liked'
        ]);
    }

    public function unlike($id)
    {
        Reaction::where('paws_id', $id)
            ->where('reacted_by', auth()->id())
            ->delete();

        return response()->json([
            'message' => 'Like removed'
        ]);
    }
}
