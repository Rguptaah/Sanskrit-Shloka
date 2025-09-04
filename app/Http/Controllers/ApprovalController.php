<?php

namespace App\Http\Controllers;

use App\Models\QAPair;
use App\Models\Shloka;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    /**
     * Display the approval dashboard
     */
    public function index()
    {
        $this->authorize('approve', [Shloka::class, QAPair::class]);

        $pendingShlokas = Shloka::pending()
            ->with('creator')
            ->latest()
            ->take(10)
            ->get();

        $pendingQAPairs = QAPair::pending()
            ->with(['shloka', 'creator'])
            ->latest()
            ->take(10)
            ->get();

        $stats = [
            'pending_shlokas' => Shloka::pending()->count(),
            'pending_qapairs' => QAPair::pending()->count(),
            'approved_shlokas' => Shloka::approved()->count(),
            'approved_qapairs' => QAPair::approved()->count(),
        ];

        return view('approver.dashboard', compact('pendingShlokas', 'pendingQAPairs', 'stats'));
    }

    /**
     * Display pending shlokas for approval
     */
    public function pendingShlokas(Request $request)
    {
        $this->authorize('approve', Shloka::class);

        $query = Shloka::pending()->with(['creator', 'qaPairs']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('shloka_id', 'like', "%{$search}%")
                  ->orWhere('sanskrit_shloka', 'like', "%{$search}%")
                  ->orWhere('source_text_name', 'like', "%{$search}%");
            });
        }

        $shlokas = $query->latest()->paginate(15);

        return view('approver.pending-shlokas', compact('shlokas'));
    }

    /**
     * Display pending Q&A pairs for approval
     */
    public function pendingQAPairs(Request $request)
    {
        $this->authorize('approve', QAPair::class);

        $query = QAPair::pending()->with(['shloka', 'creator']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                  ->orWhere('answer', 'like', "%{$search}%")
                  ->orWhereHas('shloka', function ($sq) use ($search) {
                      $sq->where('shloka_id', 'like', "%{$search}%");
                  });
            });
        }

        $qaPairs = $query->latest()->paginate(15);

        return view('approver.pending-qapairs', compact('qaPairs'));
    }

    /**
     * Approve a shloka
     */
    public function approveShloka(Request $request, Shloka $shloka)
    {
        $this->authorize('approve', $shloka);

        if ($shloka->isApproved()) {
            return redirect()->back()->with('warning', 'Shloka is already approved.');
        }

        $shloka->approve(Auth::user());

        return redirect()->back()->with('success', 'Shloka approved successfully!');
    }

    /**
     * Reject a shloka
     */
    public function rejectShloka(Request $request, Shloka $shloka)
    {
        $this->authorize('approve', $shloka);

        $shloka->reject();

        return redirect()->back()->with('success', 'Shloka rejected successfully!');
    }

    /**
     * Approve a Q&A pair
     */
    public function approveQAPair(Request $request, QAPair $qapair)
    {
        $this->authorize('approve', $qapair);

        if ($qapair->isApproved()) {
            return redirect()->back()->with('warning', 'Q&A pair is already approved.');
        }

        $qapair->approve(Auth::user());

        return redirect()->back()->with('success', 'Q&A pair approved successfully!');
    }

    /**
     * Reject a Q&A pair
     */
    public function rejectQAPair(Request $request, QAPair $qapair)
    {
        $this->authorize('approve', $qapair);

        $qapair->reject();

        return redirect()->back()->with('success', 'Q&A pair rejected successfully!');
    }

    /**
     * Bulk approve shlokas
     */
    public function bulkApproveShlokas(Request $request)
    {
        $this->authorize('approve', Shloka::class);

        $request->validate([
            'shloka_ids' => 'required|array',
            'shloka_ids.*' => 'exists:shlokas,id',
        ]);

        $shlokas = Shloka::whereIn('id', $request->shloka_ids)->pending()->get();
        $approved = 0;

        foreach ($shlokas as $shloka) {
            if ($shloka->approve(Auth::user())) {
                $approved++;
            }
        }

        return redirect()->back()->with('success', "Successfully approved {$approved} shloka(s)!");
    }

    /**
     * Bulk approve Q&A pairs
     */
    public function bulkApproveQAPairs(Request $request)
    {
        $this->authorize('approve', QAPair::class);

        $request->validate([
            'qapair_ids' => 'required|array',
            'qapair_ids.*' => 'exists:qa_pairs,id',
        ]);

        $qaPairs = QAPair::whereIn('id', $request->qapair_ids)->pending()->get();
        $approved = 0;

        foreach ($qaPairs as $qaPair) {
            if ($qaPair->approve(Auth::user())) {
                $approved++;
            }
        }

        return redirect()->back()->with('success', "Successfully approved {$approved} Q&A pair(s)!");
    }

    /**
     * Get approval history
     */
    public function history(Request $request)
    {
        $this->authorize('approve', [Shloka::class, QAPair::class]);

        $approvedShlokas = Shloka::approved()
            ->with(['creator', 'approver'])
            ->where('approved_by', Auth::id())
            ->latest('approved_at')
            ->paginate(10, ['*'], 'shlokas_page');

        $approvedQAPairs = QAPair::approved()
            ->with(['shloka', 'creator', 'approver'])
            ->where('approved_by', Auth::id())
            ->latest('approved_at')
            ->paginate(10, ['*'], 'qapairs_page');

        return view('approver.history', compact('approvedShlokas', 'approvedQAPairs'));
    }
}