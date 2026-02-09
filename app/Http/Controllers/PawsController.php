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
    $query = PawsListing::with(['user:id,name,email', 'photos', 'reactions'])
        ->withCount('reactions');

        $query->when($request->filled('user_id'), function ($q) use ($request) {
        $q->where('user_id', $request->user_id);
    });

    // 1. Filter by Search
    $query->when($request->filled('search'), function ($q) use ($request) {
        $searchIn = $request->input('search_in', 'all');
        $q->where(function($sub) use ($request, $searchIn) {
            if ($searchIn === 'title') {
                $sub->where('title', 'like', '%' . $request->search . '%');
            } else {
                $sub->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            }
        });
    });

    // 2. Filter by Location
    $query->when($request->filled('location') && $request->location !== 'All', function ($q) use ($request) {
        $q->where('location', $request->location);
    });

    // 3. Filter by Status
    $query->when($request->filled('status') && strtolower($request->status) !== 'all', function ($q) use ($request) {
        $q->where('status', $request->status);
    });

    // 4. THE DUAL-LAYER SORTING LOGIC
    // Layer 1: Push Adopted to the bottom (Applies to Popular and Default)
    if (!$request->filled('status') || strtolower($request->status) === 'all') {
        $query->orderByRaw("status = 'adopted' ASC");
    }

    // Layer 2: Sub-sorting
    $sort = $request->query('sort');

    if ($sort === 'trending') {
        $query->where('created_at', '>=', now()->subDay())
              ->has('reactions', '>=', 10)
              ->orderBy('reactions_count', 'desc');
    } elseif ($sort === 'popular') {
        // Popular: MUST have at least 1 like, then sorted by most likes
        $query->has('reactions', '>=', 1)
              ->orderBy('reactions_count', 'desc');
    } else {
        $query->latest();
    }

    $paginated = $query->paginate(10);

    return response()->json([
        'status' => 'success',
        'data' => $paginated->items(),
        'current_page' => $paginated->currentPage(),
        'last_page' => $paginated->lastPage(),
        'total' => $paginated->total()
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
            'total_adopted' => PawsListing::where('status', 'adopted')->count(),
        ]
    ]);

}

public function update(Request $request, $id)
{
    $listing = PawsListing::where('paws_id', $id)->where('user_id', auth()->id())->firstOrFail();

    $request->validate([
        'title'  => 'required|string|max:30',
        'photos' => 'nullable|array|max:3',
        'photos.*' => 'image|mimes:jpeg,png,jpg|max:2048',
    ]);

    // Update Text
    $listing->update($request->only(['title', 'description', 'location', 'fb_link']));

    // Update Photos (If provided)
    if ($request->hasFile('photos')) {
        // Optional: Delete old photos from storage here
        $listing->photos()->delete(); 

        foreach ($request->file('photos') as $file) {
            $path = $file->store('paws_images', 'public');
            $listing->photos()->create(['photo_path' => $path]);
        }
    }

    return response()->json(['success' => true, 'data' => $listing->load('photos')]);
}


}
