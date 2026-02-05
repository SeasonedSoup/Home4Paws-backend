<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PawsListing;
use App\Models\Reaction;
use App\Models\InboxNotification;
class PawsController extends Controller
{
   public function store(Request $request)
{
    // 1. VALIDATION: Enforce 1-3 images and text requirements
    $request->validate([
        'title'       => 'required|string|max:30',
        'description' => 'required|string',
        'location'    => 'required|string',
        'photos'      => 'required|array|min:1|max:3', // MUST have at least 1, max 3
        'photos.*'    => 'image|mimes:jpeg,png,jpg,webp|max:2048', // Max 2MB per file
    ]);

    // 2. CREATE THE LISTING
    $listing = PawsListing::create([
        'user_id'     => auth()->id(),
        'title'       => $request->title,
        'description' => $request->description,
        'location'    => $request->location,
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
    $paws = PawsListing::with(['user', 'photos', 'reactions'])
        ->withCount('reactions')
        ->when($request->filled('search'), function ($query) use ($request) {
            // Grouping the OR condition so it doesn't bypass other filters
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%');
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
    
public function logEmailCopy($id)
{
    $user_id = auth()->id();
    $paw = PawsListing::findOrFail($id);

    // 1. Don't notify if the owner copies their own email
    if ($paw->user_id === $user_id) {
        return response()->json(['message' => 'Owner action ignored'], 200);
    }

    // 2. ONE-TIME LOGIC: firstOrCreate checks if this notification already exists
    InboxNotification::firstOrCreate([
        'receiver_id' => $paw->user_id,
        'sender_id'   => $user_id,
        'paws_id'     => $id,
        'type'        => 'email_copy',
    ], [
        // This part only runs if the notification DOES NOT exist yet
        'message'     => auth()->user()->name . " copied your email for the post: " . $paw->title,
        'is_read'     => false,
    ]);

    return response()->json(['message' => 'Notification logged (one-time)']);
}
}
