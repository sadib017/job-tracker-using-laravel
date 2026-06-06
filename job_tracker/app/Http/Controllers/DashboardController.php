<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Use the Application model directly instead of relationship methods on the User
        $stats = [
            'total_applications' => Application::where('user_id', $user->id)->count(),
            // count distinct companies from the user's applications
            'total_companies'    => Application::where('user_id', $user->id)->distinct('company_id')->count('company_id'),
            'interviews'         => Application::where('user_id', $user->id)
                                        ->whereIn('status', ['interview_scheduled', 'interview_completed'])
                                        ->count(),
            'offered'            => Application::where('user_id', $user->id)->where('status', 'offered')->count(),
            'accepted'           => Application::where('user_id', $user->id)->where('status', 'accepted')->count(),
            'rejected'           => Application::where('user_id', $user->id)->where('status', 'rejected')->count(),
        ];

        // 5 most recent applications
        $recentApplications = Application::where('user_id', $user->id)
            ->with('company')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', compact('stats', 'recentApplications'));
    }
}
