<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LoanDoc;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\Penalty;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Redirection selon les permissions
        if ($user->can('view-dashboard-receptionniste')) {
            return redirect()->route('receptionniste.dashboard');
        } elseif ($user->can('view-dashboard-charge-credits')) {
            return redirect()->route('charge_credits.dashboard');
        } elseif ($user->can('view-dashboard-gerant')) {
            return redirect()->route('gerant.dashboard');
        } elseif ($user->can('view-dashboard-caissiere')) {
            return redirect()->route('caissiere.dashboard');
        } elseif ($user->can('view-dashboard-directeur')) {
            return redirect()->route('directeur.dashboard');
        } else {
            return redirect()->route('login');
        }
    }

    /**
     * Get dashboard statistics.
     */
    public function getStats()
    {
        $stats = [
            'totalLoans' => LoanDoc::count(),
            'totalMembers' => Member::count(),
            'totalRepayments' => LoanRepayment::sum('amount'),
            'pendingPenalties' => Penalty::where('status', 'notPaid')->count(),
        ];

        return response()->json($stats);
    }
}
