<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventRegistration;
use App\Models\User; // Pastikan model User di-import jika belum
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

class EventController extends Controller
{
    /**
     * Get fillable columns dynamically from the events table
     */
    private function getEventColumns()
    {
        return Schema::getColumnListing('events');
    }

    /**
     * Filter event data to only include existing columns
     */
    private function filterEventData(array $data)
    {
        $columns = $this->getEventColumns();
        $filtered = array_intersect_key($data, array_flip($columns));
        
        // Handle capacity field - set it before filtering
        // This logic seems to be duplicated or could be simplified if 'capacity' from form is preferred.
        // For now, keeping existing logic.
        if (in_array('capacity', $columns)) {
            if (!array_key_exists('capacity', $filtered) || $filtered['capacity'] === null || $filtered['capacity'] === '') {
                // Use max_participants if available, otherwise use capacity from original data, or default to 100
                $filtered['capacity'] = $data['max_participants'] ?? $data['capacity'] ?? 100;
            }
        }
        
        return $filtered;
    }

    /**
     * Display a listing of events
     */
    public function index(Request $request)
    {
        $query = Event::with(['user', 'registrations'])
                        ->where('is_active', true)
                        ->where('start_date', '>', now());

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Featured events (only if column exists)
        if ($request->has('featured') && Schema::hasColumn('events', 'is_featured')) {
            $query->where('is_featured', true);
        }

        $events = $query->orderBy('start_date', 'asc')->paginate(8);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $events
            ]);
        }

        return view('browse', compact('events')); // Sesuaikan dengan file browse.blade.php Anda
    }

    /**
     * Show the form for creating a new event (Public page)
     */
    public function createPage()
    {
        try {
            $categories = EventCategory::where('is_active', true)->orderBy('name')->get();
        } catch (\Exception $e) {
            $categories = collect([]);
        }
        
        // PERBAIKAN: Ubah dari 'Homepage.create' menjadi 'create'
        return view('create', compact('categories'));
    }

    /**
     * Show the form for creating a new event (Authenticated route)
     */
    public function create()
    {
        try {
            $categories = EventCategory::where('is_active', true)->orderBy('name')->get();
        } catch (\Exception $e) {
            $categories = collect([]);
        }
        
        // Untuk authenticated route, bisa tetap menggunakan view yang sama atau berbeda
        return view('create', compact('categories')); // atau 'events.create' jika ada
    }

    /**
     * Store a newly created event
     */
    public function store(Request $request)
    {
        // Dynamic validation rules based on existing columns
        $validationRules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date|after_or_equal:today', // Memungkinkan hari ini
            'end_date' => 'required|date|after_or_equal:start_date',
            // 'organizer' is expected from the form, added here
            'organizer' => 'required|string|max:255',
            // 'organizer_type' is expected from the form, added here
            'organizer_type' => 'required|string',
        ];

        $columns = $this->getEventColumns();
        
        if (in_array('category', $columns)) {
            $validationRules['category'] = 'required|string'; // Di form create, kategori sebaiknya required
        }
        if (in_array('location', $columns)) {
            $validationRules['location'] = 'nullable|string|max:255';
        }
        if (in_array('contact_email', $columns)) {
            $validationRules['contact_email'] = 'required|email'; // Di form create, contact_email sebaiknya required
        }
        if (in_array('contact_phone', $columns)) {
            $validationRules['contact_phone'] = 'nullable|string|max:20';
        }
        if (in_array('max_participants', $columns)) {
            $validationRules['max_participants'] = 'nullable|integer|min:1';
        }
        if (in_array('capacity', $columns)) {
            // 'capacity' from form is preferred. Default will be handled later if empty.
            $validationRules['capacity'] = 'nullable|integer|min:1';
        }
        if (in_array('registration_fee', $columns)) {
            $validationRules['registration_fee'] = 'nullable|numeric|min:0';
        }
        if (in_array('image', $columns)) {
            $validationRules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        }
        if (in_array('additional_info', $columns)) {
            $validationRules['additional_info'] = 'nullable|string';
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $eventData = $request->all();
        
        if (in_array('user_id', $columns)) {
            $eventData['user_id'] = Auth::id();
        }
        if (in_array('created_by', $columns)) {
            $eventData['created_by'] = Auth::id();
        }
        // 'organizer_type' comes from the form, so it should already be in $eventData
        // if (in_array('organizer_type', $columns)) {
        //     $eventData['organizer_type'] = $request->input('organizer_type'); // Ensure it's taken from request
        // }
        if (in_array('is_active', $columns)) {
            $eventData['is_active'] = true; // Default to active for new events
        }

        if ($request->hasFile('image') && in_array('image', $columns)) {
            $imagePath = $request->file('image')->store('events', 'public');
            $eventData['image'] = $imagePath;
            
            if (in_array('image_url', $columns)) {
                $eventData['image_url'] = Storage::url($imagePath);
            }
        }

        // Handle capacity: if 'capacity' is empty/null, use 'max_participants', then default.
        if (in_array('capacity', $columns)) {
            if (empty($eventData['capacity'])) { // Checks for null, empty string, 0
                $eventData['capacity'] = $eventData['max_participants'] ?? 100; // Default to 100 if max_participants also empty
            }
        }

        $eventData = $this->filterEventData($eventData); // Filter again after manual assignments

        $event = Event::create($eventData);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Event created successfully!',
                'event' => $event->load('user'),
                'formatted_fee' => method_exists($event, 'getFormattedRegistrationFeeAttribute') 
                    ? $event->formatted_registration_fee 
                    : ($event->registration_fee ?? 'Free')
            ], 201);
        }

        return redirect()->route('browse', $event) 
                                ->with('success', 'Event created successfully!');
    }

    /**
     * Display the specified event
     */
    public function show(Event $event)
    {
        $event->load(['user', 'registrations.user']);
        
        $isRegistered = Auth::check() 
            ? $event->registrations()->where('user_id', Auth::id())->exists()
            : false;

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'event' => $event,
                'is_registered' => $isRegistered,
                'formatted_fee' => method_exists($event, 'getFormattedRegistrationFeeAttribute') 
                    ? $event->formatted_registration_fee 
                    : ($event->registration_fee ?? 'Free'),
                'image_url' => method_exists($event, 'getImageUrlAttribute') 
                    ? $event->image_url 
                    : ($event->image ? Storage::url($event->image) : null)
            ]);
        }
        
        return view('detail', compact('event', 'isRegistered'));
    }

    /**
     * Show the form for editing event
     */
    public function edit(Event $event)
    {
        $canEdit = false;
        $columns = $this->getEventColumns();
        $userId = Auth::id();
        
        if ((in_array('user_id', $columns) && $event->user_id == $userId) ||
            (in_array('created_by', $columns) && $event->created_by == $userId) ||
            (in_array('organizer', $columns) && $event->organizer == Auth::user()->name) // Assuming organizer name matches user name
           ) {
            $canEdit = true;
        }
        // Note: organizer_type is usually a string like 'student', 'faculty', not Auth::id()
        // If 'organizer_type' stores user_id for specific types, the logic needs adjustment.
        
        if (!$canEdit && Auth::user()->role !== 'admin') { // Example: Allow admin to edit all
            abort(403, 'Unauthorized action.');
        }
        
        $categories = EventCategory::where('is_active', true)->orderBy('name')->get();
        // Pastikan view 'events.edit' ada
        return view('edit', compact('event', 'categories'));
    }

    /**
     * Update the specified event
     */
    public function update(Request $request, Event $event)
    {
        $canEdit = false;
        $columns = $this->getEventColumns();
        $userId = Auth::id();

        if ((in_array('user_id', $columns) && $event->user_id == $userId) ||
            (in_array('created_by', $columns) && $event->created_by == $userId) ||
            (in_array('organizer', $columns) && $event->organizer == Auth::user()->name)
           ) {
            $canEdit = true;
        }
        
        if (!$canEdit && Auth::user()->role !== 'admin') {
             if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $validationRules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date', // Validasi after_or_equal:today bisa ditambahkan jika perlu
            'end_date' => 'required|date|after_or_equal:start_date',
            'organizer' => 'required|string|max:255',
            'organizer_type' => 'required|string',
        ];

        if (in_array('category', $columns)) {
            $validationRules['category'] = 'required|string';
        }
        if (in_array('location', $columns)) {
            $validationRules['location'] = 'nullable|string|max:255';
        }
        if (in_array('contact_email', $columns)) {
            $validationRules['contact_email'] = 'required|email';
        }
        if (in_array('contact_phone', $columns)) {
            $validationRules['contact_phone'] = 'nullable|string|max:20';
        }
        if (in_array('max_participants', $columns)) {
            $validationRules['max_participants'] = 'nullable|integer|min:1';
        }
        if (in_array('capacity', $columns)) {
            $validationRules['capacity'] = 'nullable|integer|min:1';
        }
        if (in_array('registration_fee', $columns)) {
            $validationRules['registration_fee'] = 'nullable|numeric|min:0';
        }
        if (in_array('image', $columns)) {
            $validationRules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        }
        if (in_array('additional_info', $columns)) {
            $validationRules['additional_info'] = 'nullable|string';
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $eventData = $request->all();

        if ($request->hasFile('image') && in_array('image', $columns)) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $imagePath = $request->file('image')->store('events', 'public');
            $eventData['image'] = $imagePath;
            if (in_array('image_url', $columns)) {
                $eventData['image_url'] = Storage::url($imagePath);
            }
        } else if ($request->boolean('remove_image') && in_array('image', $columns)) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $eventData['image'] = null;
            if (in_array('image_url', $columns)) {
                $eventData['image_url'] = null;
            }
        }


        if (in_array('capacity', $columns)) {
            if (empty($eventData['capacity'])) {
                $eventData['capacity'] = $eventData['max_participants'] ?? $event->capacity ?? 100;
            }
        }
        
        // Ensure is_active is not accidentally overridden if not in request
        if (in_array('is_active', $columns) && !$request->has('is_active')) {
             $eventData['is_active'] = $event->is_active;
        }


        $eventData = $this->filterEventData($eventData);
        $event->update($eventData);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully!',
                'event' => $event->fresh()->load('user'),
                'formatted_fee' => method_exists($event, 'getFormattedRegistrationFeeAttribute') 
                    ? $event->formatted_registration_fee 
                    : ($event->registration_fee ?? 'Free')
            ]);
        }

        return redirect()->route('browse', $event)
                                ->with('success', 'Event updated successfully!');
    }

    /**
     * Remove the specified event
     */
    public function destroy(Event $event)
    {
        $canDelete = false;
        $columns = $this->getEventColumns();
        $userId = Auth::id();

        if ((in_array('user_id', $columns) && $event->user_id == $userId) ||
            (in_array('created_by', $columns) && $event->created_by == $userId) ||
            (in_array('organizer', $columns) && $event->organizer == Auth::user()->name)
           ) {
            $canDelete = true;
        }
        
        if (!$canDelete && Auth::user()->role !== 'admin') {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }
            abort(403);
        }

        if (in_array('image', $columns) && $event->image) {
            Storage::disk('public')->delete($event->image);
        }

        $event->registrations()->delete(); // Hapus registrasi terkait
        $event->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully!'
            ]);
        }

        // Pastikan route 'events.index' ada
        return redirect()->route('events.index')
                                ->with('success', 'Event deleted successfully!');
    }

    /**
     * Register user for an event
     */
    public function register(Request $request, Event $event)
    {
        if (!Auth::check()) {
            if ($request->expectsJson()){
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to register for events'
                ], 401);
            }
            return redirect()->route('login')->with('error', 'Please login to register for events.');
        }

        $user = Auth::user();
        // Pastikan email user terverifikasi jika disyaratkan
        // if (!$user->hasVerifiedEmail()) {
        //     if ($request->expectsJson()){
        //         return response()->json(['success' => false, 'message' => 'Please verify your email to register.'], 403);
        //     }
        //     return redirect()->route('verification.notice')->with('error', 'Please verify your email to register.');
        // }


        $columns = $this->getEventColumns();
        $currentRegistrationsCount = $event->registrations()->where('registration_status', 'confirmed')->count();

        $maxCapacity = null;
        if (in_array('capacity', $columns) && $event->capacity !== null) {
            $maxCapacity = $event->capacity;
        } elseif (in_array('max_participants', $columns) && $event->max_participants !== null) {
            $maxCapacity = $event->max_participants;
        }

        if ($maxCapacity !== null && $currentRegistrationsCount >= $maxCapacity) {
            return response()->json([
                'success' => false,
                'message' => 'Event is full'
            ], 400);
        }

        $existingRegistration = EventRegistration::where('event_id', $event->id)
                                                ->where('user_id', $user->id)
                                                ->first();

        if ($existingRegistration && $existingRegistration->registration_status === 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'You are already registered for this event'
            ], 400);
        }

        if ($event->end_date < now()) { // Cek end_date bukan start_date untuk registrasi
            return response()->json([
                'success' => false,
                'message' => 'Cannot register for past events'
            ], 400);
        }

        $registrationData = [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'registered_at' => now(),
            'registration_status' => 'confirmed', // Default ke confirmed
            'additional_data' => json_encode($request->input('additional_data', [])),
        ];

        if (in_array('registration_fee', $columns) && $event->registration_fee > 0) {
            $registrationData['amount_paid'] = $event->registration_fee;
            // Di sini Anda mungkin perlu integrasi payment gateway
            // $registrationData['registration_status'] = 'pending_payment';
        } else {
             $registrationData['amount_paid'] = 0;
        }
        
        if ($existingRegistration) {
            $existingRegistration->update($registrationData);
            $registration = $existingRegistration;
        } else {
            $registration = EventRegistration::create($registrationData);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully registered for the event!',
                'registration' => $registration,
                'formatted_fee' => method_exists($event, 'getFormattedRegistrationFeeAttribute') 
                    ? $event->formatted_registration_fee 
                    : ($event->registration_fee ?? 'Free')
            ]);
        }
        return back()->with('success', 'Successfully registered for the event!');
    }

    /**
     * Cancel event registration
     */
    public function cancelRegistration(Request $request, Event $event)
    {
        $registration = EventRegistration::where('event_id', $event->id)
                                        ->where('user_id', Auth::id())
                                        ->first();

        if (!$registration) {
            if ($request->expectsJson()){
                return response()->json(['success' => false, 'message' => 'Registration not found'], 404);
            }
            return back()->with('error', 'Registration not found');
        }

        // Logika refund jika ada pembayaran bisa ditambahkan di sini

        $registration->update(['registration_status' => 'cancelled']);

        if ($request->expectsJson()){
            return response()->json(['success' => true, 'message' => 'Registration cancelled successfully']);
        }
        return back()->with('success', 'Registration cancelled successfully');
    }


    /**
     * Get user's events (created or registered)
     */
    public function myEvents()
    {
        $user = Auth::user();
        $columns = $this->getEventColumns();
        
        $createdEventsQuery = Event::query();
        $createdEventsQuery->where(function($query) use ($user, $columns) {
            if (in_array('created_by', $columns)) {
                $query->where('created_by', $user->id);
            }
            if (in_array('user_id', $columns)) {
                $query->orWhere('user_id', $user->id);
            }
            // Consider how 'organizer' (string name) or 'organizer_id' (if exists) links to user
            // if (in_array('organizer', $columns)) {
            //     $query->orWhere('organizer', $user->name);
            // }
        });
                                
        $createdEvents = $createdEventsQuery->withCount('registrations') // Menggunakan withCount
                                            ->orderBy('start_date', 'desc')
                                            ->get();
                                    
        $registeredEvents = $user->registrations()
                                ->where('registration_status', 'confirmed')
                                ->with(['event' => function ($query) {
                                    $query->withCount('registrations'); // Muat juga jumlah registrasi untuk event yang diikuti
                                }])
                                ->orderBy('registered_at', 'desc')
                                ->get()
                                ->map(function ($registration) {
                                    return $registration->event; // Ambil objek event
                                })
                                ->filter(); // Hapus null jika ada event yang terhapus

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'created_events' => $createdEvents,
                'registered_events' => $registeredEvents
            ]);
        }

    return view('profile', compact('createdEvents', 'registeredEvents'));    }


    /**
     * Search events
     */
    public function search(Request $request)
    {
        $queryTerm = $request->input('q'); // Ganti 'query' menjadi 'q' agar konsisten dengan route
        
        if (!$queryTerm) {
             if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query is required'
                ], 400);
            }
            return redirect()->route('browse')->with('error', 'Search query is required.'); // Ganti dengan route browse yang benar
        }

        $events = Event::where(function($q) use ($queryTerm) {
            $q->where('title', 'like', '%' . $queryTerm . '%')
                ->orWhere('description', 'like', '%' . $queryTerm . '%')
                ->orWhere('location', 'like', '%' . $queryTerm . '%') // Tambahkan pencarian berdasarkan lokasi
                ->orWhere('category', 'like', '%' . $queryTerm . '%'); // Tambahkan pencarian berdasarkan kategori
        })
        ->where('is_active', true)
        ->where('start_date', '>', now()) // Hanya event mendatang
        ->with('user') // Eager load user
        ->orderBy('start_date', 'asc')
        ->paginate(10); // Gunakan pagination

        if ($request->expectsJson()) {
             // Transform data for API consistency
            $events->getCollection()->transform(function ($event) {
                $event->formatted_fee = method_exists($event, 'getFormattedRegistrationFeeAttribute') 
                    ? $event->formatted_registration_fee 
                    : ($event->registration_fee ?? 'Free');
                $event->full_image_url = method_exists($event, 'getImageUrlAttribute') 
                    ? $event->image_url 
                    : ($event->image ? Storage::url($event->image) : null);
                return $event;
            });
            return response()->json([
                'success' => true,
                'data' => $events // Kembalikan data pagination
            ]);
        }
        // Untuk tampilan web, kembalikan ke view browse dengan hasil pencarian
        return view('browse', compact('events', 'queryTerm')); // Ganti dengan view yang sesuai
    }


    /**
     * View created events by current user
     */
    public function viewCreatedEvents()
    {
        $user = Auth::user();
        $columns = $this->getEventColumns();
        
        $eventsQuery = Event::query();
        $eventsQuery->where(function($query) use ($user, $columns) {
             if (in_array('created_by', $columns)) {
                $query->where('created_by', $user->id);
            }
            if (in_array('user_id', $columns)) {
                $query->orWhere('user_id', $user->id);
            }
            // if (in_array('organizer', $columns)) {
            //     $query->orWhere('organizer', $user->name);
            // }
        });
        
        $events = $eventsQuery->withCount('registrations')
                            ->orderBy('start_date', 'desc')
                            ->paginate(10); // Gunakan pagination
                                
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $events // Kembalikan data pagination
            ]);
        }
                                
        // Pastikan view 'events.created' ada
        return view('dashboard', compact('events'));
    }

    /**
     * Join event (alias for register)
     */
    public function join(Request $request, Event $event)
    {
        return $this->register($request, $event);
    }

    /**
     * Browse events page
     */
    public function browse(Request $request) // Tambahkan Request $request
    {
        $query = Event::with(['user']) // Kurangi eager load jika tidak semua info registrasi dibutuhkan di list
                        ->withCount('registrations') // Lebih efisien untuk jumlah
                        ->where('is_active', true)
                        ->where('start_date', '>', now());

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('location', 'like', '%' . $searchTerm . '%');
            });
        }
        
        $events = $query->orderBy('start_date', 'asc')
                        ->paginate(12)
                        ->withQueryString(); // Agar filter tetap ada di pagination links
        
        $categories = EventCategory::where('is_active', true)->orderBy('name')->get(); // Untuk filter

        // Pastikan view 'browse' (atau 'Homepage.browse') ada
        return view('browse', compact('events', 'categories'));
    }


    /**
     * Get events for API/AJAX requests
     */
    public function getEvents(Request $request)
    {
        $query = Event::with(['user'])
                        ->withCount('registrations')
                        ->where('is_active', true)
                        ->where('start_date', '>', now());

        $columns = $this->getEventColumns();

        if ($request->filled('category') && in_array('category', $columns)) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($request->boolean('featured') && in_array('is_featured', $columns)) {
            $query->where('is_featured', true);
        }

        $events = $query->orderBy('start_date', 'asc')->paginate(12);
        
        $events->getCollection()->transform(function ($event) {
            $event->formatted_fee = method_exists($event, 'getFormattedRegistrationFeeAttribute') 
                ? $event->formatted_registration_fee 
                : ($event->registration_fee ?? 'Free');
            $event->full_image_url = method_exists($event, 'getImageUrlAttribute') 
                ? $event->image_url 
                : ($event->image ? Storage::url($event->image) : asset('path/to/default/image.jpg')); // Tambahkan default image
            return $event;
        });
                        
        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }

    /**
     * Get event statistics for dashboard
     */
    public function getEventStats()
    {
        $user = Auth::user();
        $columns = $this->getEventColumns();
        
        $createdCountQuery = Event::query();
        $createdCountQuery->where(function($query) use ($user, $columns) {
            if (in_array('created_by', $columns)) {
                $query->where('created_by', $user->id);
            }
            if (in_array('user_id', $columns)) {
                $query->orWhere('user_id', $user->id);
            }
            // if (in_array('organizer', $columns)) {
            //    $query->orWhere('organizer', $user->name);
            // }
        });
        $createdCount = $createdCountQuery->count();
        
        $registeredCount = EventRegistration::where('user_id', $user->id)
                                            ->where('registration_status', 'confirmed')
                                            ->count();
        
        $upcomingEventsQuery = Event::query();
         $upcomingEventsQuery->where(function($query) use ($user, $columns) {
            if (in_array('created_by', $columns)) {
                $query->where('created_by', $user->id);
            }
            if (in_array('user_id', $columns)) {
                $query->orWhere('user_id', $user->id);
            }
            // if (in_array('organizer', $columns)) {
            //    $query->orWhere('organizer', $user->name);
            // }
        });
        $upcomingEvents = $upcomingEventsQuery->where('start_date', '>', now())->count();

        return response()->json([
            'success' => true,
            'stats' => [
                'created_events' => $createdCount,
                'registered_events' => $registeredCount,
                'upcoming_events' => $upcomingEvents
            ]
        ]);
    }
}