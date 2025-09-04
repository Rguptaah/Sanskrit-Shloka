<?php

namespace App\Http\Controllers;

use App\Models\Shloka;
use App\Models\QAPair;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    /**
     * Display export options
     */
    public function index()
    {
        $this->authorize('export');

        $stats = [
            'total_shlokas' => Shloka::count(),
            'approved_shlokas' => Shloka::approved()->count(),
            'total_qapairs' => QAPair::count(),
            'approved_qapairs' => QAPair::approved()->count(),
        ];

        return view('admin.export', compact('stats'));
    }

    /**
     * Export shlokas to JSON format
     */
    public function exportJson(Request $request)
    {
        $this->authorize('export');

        $request->validate([
            'include_pending' => 'nullable|boolean',
            'source_filter' => 'nullable|string',
            'category_filter' => 'nullable|string',
        ]);

        $query = Shloka::with(['approvedQAPairs']);

        // Only include approved shlokas unless specifically requested
        if (!$request->include_pending) {
            $query->approved();
        }

        // Apply filters
        if ($request->source_filter) {
            $query->where('source_text_name', $request->source_filter);
        }

        if ($request->category_filter) {
            $query->where('category', $request->category_filter);
        }

        $shlokas = $query->get();

        $exportData = $shlokas->map(function ($shloka) {
            return [
                'id' => $shloka->shloka_id,
                'sanskrit_shloka' => $shloka->sanskrit_shloka,
                'unicode' => $shloka->unicode,
                'transliteration' => $shloka->transliteration,
                'translations' => $shloka->translations,
                'metadata' => [
                    'source' => [
                        'text_name' => $shloka->source_text_name,
                        'section' => $shloka->source_section,
                        'chapter' => $shloka->source_chapter,
                        'verse' => $shloka->source_verse,
                    ],
                    'keywords' => $shloka->keywords,
                    'category' => $shloka->category,
                    'commentaries' => $shloka->commentaries,
                ],
                'qa_pairs' => $shloka->approvedQAPairs->map(function ($qaPair) {
                    return [
                        'question' => $qaPair->question,
                        'answer' => $qaPair->answer,
                        'keywords' => $qaPair->keywords,
                    ];
                })->toArray(),
                'context' => $shloka->approvedQAPairs->pluck('context')->filter()->first(),
            ];
        })->values()->toArray();

        $filename = 'shloka_export_' . now()->format('Y_m_d_His') . '.json';
        $jsonContent = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Store the file in storage/app/exports
        Storage::put('exports/' . $filename, $jsonContent);

        // Return download response
        return response()->download(
            Storage::path('exports/' . $filename),
            $filename,
            ['Content-Type' => 'application/json']
        )->deleteFileAfterSend();
    }

    /**
     * Get available filters for export
     */
    public function getFilters()
    {
        $sources = Shloka::distinct()->pluck('source_text_name')->filter();
        $categories = Shloka::distinct()->pluck('category')->filter();

        return response()->json([
            'sources' => $sources,
            'categories' => $categories,
        ]);
    }
}