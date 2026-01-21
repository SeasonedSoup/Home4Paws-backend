<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PawsListings;
use Illuminate\Support\Facades\Auth;

class PawsController extends Controller
{
    public function store(Request $request) {
        $request->validate([
            'caption' => 'required|string',
            'location' => 'required|string'
        ]);

        $pawst = PawsListings::create([
            'user_id' => Auth::id(),  
            'caption' => $request->caption,
            'location' => $request->location,
            'status' => 'available',  
        ]);

        return response()->json([
            'status' => 'success',
            'paws_listing data' => $pawst
        ], 201); 
    }
    public function index()
{
    $paws = PawsListings::with('user') // assumes relation user() in PawsListing
                ->orderBy('created_at', 'desc')
                ->take(20)
                ->get();

    return response()->json([
        'message' => 'PAWS posts fetched successfully',
        'data' => $paws
    ]);
}

}
