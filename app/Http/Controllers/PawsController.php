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
            'title' => 'required|string',
            'description' => 'required|string',
            'location' => 'required|string'
        ]);

        $paws = PawsListing::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'status' => 'available',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $paws
        ], 201);
    }
    public function show($id)
    {
    $paw = PawsListing::with(['user', 'photos', 'reactions'])
        ->withCount('reactions')
        ->findOrFail($id);

    return response()->json([
        'message' => 'PAWS post fetched successfully',
        'data' => $paw
    ]);
    }
    // 1. Add Request $request to the function arguments
public function index(Request $request)
{
    $paws = PawsListing::with(['user', 'photos', 'reactions'])
        ->withCount('reactions')
        ->when($request->filled('search'), function ($query) use ($request) {
            // Grouping the OR condition so it doesn't bypass other filters
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        })
        ->when($request->filled('location') && $request->location !== 'All', function ($query) use ($request) {
            $query->where('location', $request->location);
        })
        ->latest()
        ->paginate(10);

    return response()->json([
        'status' => 'success',
        'data' => $paws->items(),
        'current_page' => $paws->currentPage(),
        'last_page' => $paws->lastPage(),
        'total' => $paws->total()
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
        $paws = PawsListing::findOrFail($id);

        // Authorization check
        if ($paws->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $paws->delete();
        return response()->json(['message' => 'PAWS post deleted']);
    }


    public function like($id)
{
    $user_id = auth()->id();
    
    $existing = Reaction::where('paws_id', $id)
                ->where('user_id', $user_id)
                ->first();

    if ($existing) {
        $existing->delete(); // Unlike
    } else {
        Reaction::create([
            'paws_id' => $id,
            'user_id' => $user_id, // FIX: Changed from reacted_by to user_id
            'reaction_type' => 'like',
        ]);
    }

    // Load fresh data with reactions array and count
    $paws = PawsListing::with('reactions')->findOrFail($id);

    return response()->json([
        'status' => 'success',
        'reactions_count' => $paws->reactions->count(),
        'reactions' => $paws->reactions 
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
