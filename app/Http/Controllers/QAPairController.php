<?php

namespace App\Http\Controllers;

use App\Models\QAPair;
use App\Models\Shloka;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QAPairController extends Controller
{
    /**
     * Display a listing of Q&A pairs for a specific shloka
     */
    public function index(Request $request, Shloka $shloka = null)
    {
        $query = QAPair::with(['shloka', 'creator', 'approver']);

        if ($shloka) {
            $query->where('shloka_id', $shloka->id);
        }

        // Filter by approval status
        if ($request->has('status')) {
            if ($request->status === 'approved') {
                $query->approved();
            } elseif ($request->status === 'pending') {
                $query->pending();
            }
        }

        // Filter by user's own Q&A pairs
        if ($request->has('mine') && $request->mine) {
            $query->where('created_by', Auth::id());
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                  ->orWhere('answer', 'like', "%{$search}%")
                  ->orWhere('context', 'like', "%{$search}%");
            });
        }

        $qaPairs = $query->latest()->paginate(15);

        // Get all approved shlokas for dropdown
        $shlokas = Shloka::approved()->orderBy('shloka_id')->get();

        return view('qapairs.index', compact('qaPairs', 'shlokas', 'shloka'));
    }

    /**
     * Show the form for creating a new Q&A pair
     */
    public function create(Request $request)
    {
        $this->authorize('create', QAPair::class);

        $selectedShloka = null;
        if ($request->has('shloka_id')) {
            $selectedShloka = Shloka::approved()->find($request->shloka_id);
        }

        $shlokas = Shloka::approved()->orderBy('shloka_id')->get();

        return view('qapairs.create', compact('shlokas', 'selectedShloka'));
    }

    /**
     * Store a newly created Q&A pair
     */
    public function store(Request $request)
    {
        $this->authorize('create', QAPair::class);

        $validator = Validator::make($request->all(), [
            'shloka_id' => 'required|exists:shlokas,id',
            'question' => 'required|string',
            'answer' => 'required|string',
            'context' => 'nullable|string',
            'keywords' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        
        // Process keywords
        $data['keywords'] = $this->processArrayInput($request->keywords);
        $data['created_by'] = Auth::id();
        $data['approved'] = false;

        $qaPair = QAPair::create($data);

        return redirect()->route('qapairs.show', $qaPair)
            ->with('success', 'Q&A pair created successfully! It will be available after approval.');
    }

    /**
     * Display the specified Q&A pair
     */
    public function show(QAPair $qapair)
    {
        $qapair->load(['shloka', 'creator', 'approver']);
        
        return view('qapairs.show', compact('qapair'));
    }

    /**
     * Show the form for editing the specified Q&A pair
     */
    public function edit(QAPair $qapair)
    {
        $this->authorize('update', $qapair);
        
        $shlokas = Shloka::approved()->orderBy('shloka_id')->get();
        
        return view('qapairs.edit', compact('qapair', 'shlokas'));
    }

    /**
     * Update the specified Q&A pair
     */
    public function update(Request $request, QAPair $qapair)
    {
        $this->authorize('update', $qapair);

        $validator = Validator::make($request->all(), [
            'shloka_id' => 'required|exists:shlokas,id',
            'question' => 'required|string',
            'answer' => 'required|string',
            'context' => 'nullable|string',
            'keywords' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        
        // Process keywords
        $data['keywords'] = $this->processArrayInput($request->keywords);

        // Reset approval status if content changed
        if ($qapair->approved && $this->contentChanged($qapair, $data)) {
            $data['approved'] = false;
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }

        $qapair->update($data);

        return redirect()->route('qapairs.show', $qapair)
            ->with('success', 'Q&A pair updated successfully!');
    }

    /**
     * Remove the specified Q&A pair
     */
    public function destroy(QAPair $qapair)
    {
        $this->authorize('delete', $qapair);
        
        $qapair->delete();

        return redirect()->route('qapairs.index')
            ->with('success', 'Q&A pair deleted successfully!');
    }

    /**
     * Get existing Q&A pairs for a shloka (AJAX)
     */
    public function getByShloka(Request $request)
    {
        $request->validate([
            'shloka_id' => 'required|exists:shlokas,id',
        ]);

        $shloka = Shloka::with(['qaPairs.creator'])->find($request->shloka_id);
        
        $qaPairs = $shloka->qaPairs->map(function ($qaPair) {
            return [
                'id' => $qaPair->id,
                'question' => $qaPair->question,
                'answer' => $qaPair->answer,
                'context' => $qaPair->context,
                'keywords' => $qaPair->keywords,
                'approved' => $qaPair->approved,
                'creator' => $qaPair->creator->name,
                'created_at' => $qaPair->created_at->format('M d, Y'),
            ];
        });

        return response()->json([
            'shloka' => [
                'id' => $shloka->shloka_id,
                'sanskrit_shloka' => $shloka->sanskrit_shloka,
                'transliteration' => $shloka->transliteration,
                'hindi_translation' => $shloka->hindi_translation,
                'english_translation' => $shloka->english_translation,
            ],
            'qa_pairs' => $qaPairs,
        ]);
    }

    /**
     * Process array input (keywords)
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
    private function contentChanged(QAPair $qapair, array $newData): bool
    {
        $significantFields = [
            'question',
            'answer',
            'context',
            'keywords',
        ];

        foreach ($significantFields as $field) {
            if (isset($newData[$field]) && $qapair->$field !== $newData[$field]) {
                return true;
            }
        }

        return false;
    }
}