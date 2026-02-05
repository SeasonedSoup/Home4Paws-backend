<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PawsListing;
use App\Models\Reaction;
use App\Models\InboxNotification;
use App\Models\User;
class PawsController extends Controller
{
   public function store(Request $request)
{
     $request->validate([
        'title'       => 'required|string|max:30',
        'description' => 'required|string',
        'location'    => 'required|string',
        'photos'      => 'required|array|min:1|max:3',
        'photos.*'    => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        // ADD THIS:
        'fb_link'     => 'nullable|url|regex:/^(https?:\/\/)?(www\.)?facebook\.com\/.+/i',
    ]);

    $listing = PawsListing::create([
        'user_id'     => auth()->id(),
        'title'       => $request->title,
        'description' => $request->description,
        'location'    => $request->location,
        'fb_link'     => $request->fb_link, // ADD THIS
    ]);

    // 3. HANDLE FILE UPLOADS
    // Since 'photos' is required, we know it's there
    foreach ($request->file('photos') as $file) {
        // This stores the physical file in: storage/app/public/paws_images
        $path = $file->store('paws_images', 'public');

        // This saves the "address" (path) in the database
        $listing->photos()->create([
            'photo_path' => $path,
        ]);
    }

    // 4. RETURN JSON
    return response()->json([
        'success' => true,
        'paw'     => $listing->load('photos') // Load photos so frontend gets the new URLs
    ], 201);
}    public function show($id)
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
    $paws = PawsListing::with(['user:id,name,email', 'photos', 'reactions'])
        ->withCount('reactions')
        // 1. Filter by Search
        ->when($request->filled('search'), function ($query) use ($request) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        })
        // 2. Filter by Location
        ->when($request->filled('location') && $request->location !== 'All', function ($query) use ($request) {
            $query->where('location', $request->location);
        })
        // 3. Dynamic Sorting
        ->when($request->query('sort') === 'popular', 
            function ($query) {
                // Sort by reaction count first, then by newest for ties
                $query->orderBy('reactions_count', 'desc')->orderBy('created_at', 'desc');
            }, 
            function ($query) {
                // Default: Sort by newest
                $query->latest();
            }
        )
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
    // Fix: Use 'paws_id' instead of 'id' to match your schema
    $paws = PawsListing::where('paws_id', $id)
        ->where('user_id', auth()->id())
        ->firstOrFail();

    // This updates the 'status' column to 'adopted'
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
    $paw = PawsListing::findOrFail($id);

    // 1. Create or Find the reaction
    Reaction::firstOrCreate([
        'paws_id' => $id,
        'user_id' => $user_id,
    ], [
        'reaction_type' => 'like',
    ]);

    // 2. CREATE THE NOTIFICATION (Only if the liker isn't the owner)
    if ($paw->user_id !== $user_id) {
        InboxNotification::firstOrCreate([
            'receiver_id' => $paw->user_id,
            'sender_id'   => $user_id,
            'paws_id'     => $id,
            'type'        => 'like',
        ], [
            'message'     => auth()->user()->name . " liked your post: " . $paw->title,
            'is_read'     => false,
        ]);
    }

    return response()->json([
        'status' => 'success',
        'reactions_count' => $paw->reactions()->count(),
        'reactions' => $paw->reactions 
    ]);
}
    
public function logFacebookClick($id) // Rename from logEmailCopy if you're replacing it
{
    $user_id = auth()->id();
    $paw = PawsListing::findOrFail($id);

    if ($paw->user_id === $user_id) {
        return response()->json(['message' => 'Owner action ignored'], 200);
    }

    InboxNotification::firstOrCreate([
        'receiver_id' => $paw->user_id,
        'sender_id'   => $user_id,
        'paws_id'     => $id,
        'type'        => 'facebook_click', // Change type to match your logic
    ], [
        'message'     => auth()->user()->name . " visited your Facebook profile for: " . $paw->title,
        'is_read'     => false,
    ]);

    return response()->json(['message' => 'Facebook click logged']);
}
    public function getGlobalStats()
{
    // Verification: count() is a direct database aggregate and faster than fetching all records
    return response()->json([
        'status' => 'success',
        'data' => [
            'total_posts' => PawsListing::count(),
            'total_users' => User::count(),
        ]
    ]);
}
}
