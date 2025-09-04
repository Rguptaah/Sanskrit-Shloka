<?php

namespace App\Http\Controllers;

use App\Models\Shloka;
use App\Services\TransliterationService;
use App\Services\UnicodeConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
Auth::shouldUse('sanctum'); // Ensure Sanctum is used for authentication

class ShlokaController extends Controller
{
    protected $transliterationService;
    protected $unicodeService;

    public function __construct(TransliterationService $transliterationService, UnicodeConversionService $unicodeService)
    {
        $this->transliterationService = $transliterationService;
        $this->unicodeService = $unicodeService;
    }

    /**
     * Display a listing of shlokas
     */
    public function index(Request $request)
    {
        $query = Shloka::with(['creator', 'approver', 'qaPairs']);

        // Filter by approval status
        if ($request->has('status')) {
            if ($request->status === 'approved') {
                $query->approved();
            } elseif ($request->status === 'pending') {
                $query->pending();
            }
        }

        // Filter by user's own shlokas
        if ($request->has('mine') && $request->mine) {
            $query->where('created_by', Auth::id());
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('shloka_id', 'like', "%{$search}%")
                  ->orWhere('sanskrit_shloka', 'like', "%{$search}%")
                  ->orWhere('source_text_name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $shlokas = $query->latest()->paginate(15);

        return view('shlokas.index', compact('shlokas'));
    }

    /**
     * Show the form for creating a new shloka
     */
    public function create()
    {
        $this->authorize('create', Shloka::class);
        
        return view('shlokas.create');
    }

    /**
     * Store a newly created shloka
     */
    public function store(Request $request)
    {
        $this->authorize('create', Shloka::class);

        $validator = Validator::make($request->all(), [
            'shloka_id' => 'required|string|unique:shlokas,shloka_id|max:255',
            'sanskrit_shloka' => 'required|string',
            'translations.hindi' => 'required|string',
            'translations.english' => 'required|string',
            'source_text_name' => 'required|string|max:255',
            'source_section' => 'required|string|max:255',
            'source_chapter' => 'required|integer|min:1',
            'source_verse' => 'required|integer|min:1',
            'category' => 'nullable|string|max:255',
            'keywords' => 'nullable|string',
            'commentaries' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        
        // Process keywords and commentaries
        $data['keywords'] = $this->processArrayInput($request->keywords);
        $data['commentaries'] = $this->processArrayInput($request->commentaries);
        
        // Auto-generate Unicode and transliteration
        if (empty($data['unicode'])) {
            $data['unicode'] = $this->unicodeService->devanagariToUnicodeEscape($data['sanskrit_shloka']);
        }
        
        if (empty($data['transliteration'])) {
            $data['transliteration'] = $this->transliterationService->devanagariToIAST($data['sanskrit_shloka']);
        }

        $data['created_by'] = Auth::id();
        $data['approved'] = false;

        $shloka = Shloka::create($data);

        return redirect()->route('shlokas.show', $shloka)
            ->with('success', 'Shloka created successfully! It will be available after approval.');
    }

    /**
     * Display the specified shloka
     */
    public function show(Shloka $shloka)
    {
        $shloka->load(['creator', 'approver', 'qaPairs.creator']);
        
        return view('shlokas.show', compact('shloka'));
    }

    /**
     * Show the form for editing the specified shloka
     */
    public function edit(Shloka $shloka)
    {
        $this->authorize('update', $shloka);
        
        return view('shlokas.edit', compact('shloka'));
    }

    /**
     * Update the specified shloka
     */
    public function update(Request $request, Shloka $shloka)
    {
        $this->authorize('update', $shloka);

        $validator = Validator::make($request->all(), [
            'shloka_id' => 'required|string|max:255|unique:shlokas,shloka_id,' . $shloka->id,
            'sanskrit_shloka' => 'required|string',
            'translations.hindi' => 'required|string',
            'translations.english' => 'required|string',
            'source_text_name' => 'required|string|max:255',
            'source_section' => 'required|string|max:255',
            'source_chapter' => 'required|integer|min:1',
            'source_verse' => 'required|integer|min:1',
            'category' => 'nullable|string|max:255',
            'keywords' => 'nullable|string',
            'commentaries' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        
        // Process keywords and commentaries
        $data['keywords'] = $this->processArrayInput($request->keywords);
        $data['commentaries'] = $this->processArrayInput($request->commentaries);
        
        // Update Unicode and transliteration if sanskrit_shloka changed
        if ($data['sanskrit_shloka'] !== $shloka->sanskrit_shloka) {
            if (empty($data['unicode'])) {
                $data['unicode'] = $this->unicodeService->devanagariToUnicodeEscape($data['sanskrit_shloka']);
            }
            
            if (empty($data['transliteration'])) {
                $data['transliteration'] = $this->transliterationService->devanagariToIAST($data['sanskrit_shloka']);
            }
        }

        // Reset approval status if content changed
        if ($shloka->approved && $this->contentChanged($shloka, $data)) {
            $data['approved'] = false;
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }

        $shloka->update($data);

        return redirect()->route('shlokas.show', $shloka)
            ->with('success', 'Shloka updated successfully!');
    }

    /**
     * Remove the specified shloka
     */
    public function destroy(Shloka $shloka)
    {
        $this->authorize('delete', $shloka);
        
        $shloka->delete();

        return redirect()->route('shlokas.index')
            ->with('success', 'Shloka deleted successfully!');
    }

    /**
     * Auto-generate transliteration via AJAX
     */
    public function transliterate(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
            'from' => 'required|string',
            'to' => 'required|string',
        ]);

        $transliterated = $this->transliterationService->transliterate(
            $request->text,
            $request->from,
            $request->to
        );

        return response()->json(['transliterated' => $transliterated]);
    }

    /**
     * Auto-generate Unicode via AJAX
     */
    public function unicode(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
        ]);

        $unicode = $this->unicodeService->devanagariToUnicodeEscape($request->text);

        return response()->json(['unicode' => $unicode]);
    }

    /**
     * Process array input (keywords, commentaries)
     */
    private function processArrayInput($input)
    {
        if (empty($input)) {
            return null;
        }

        if (is_string($input)) {
            return array_map('trim', explode(',', $input));
        }

        return $input;
    }

    /**
     * Check if content has changed significantly
     */
    private function contentChanged(Shloka $shloka, array $newData): bool
    {
        $significantFields = [
            'sanskrit_shloka',
            'translations',
            'source_text_name',
            'source_section',
            'source_chapter',
            'source_verse',
        ];

        foreach ($significantFields as $field) {
            if (isset($newData[$field]) && $shloka->$field !== $newData[$field]) {
                return true;
            }
        }

        return false;
    }
}