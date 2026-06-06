<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = Application::where('user_id', Auth::id())->with('company');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('position', 'like', "%{$search}%")
                  ->orWhereHas('company', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->latest()->paginate(10)->withQueryString();
        $statuses = Application::$statuses;

        return view('applications.index', compact('applications', 'statuses'));
    }

    public function create()
    {
        $companies = Company::where('user_id', Auth::id())->orderBy('name')->get();
        $statuses  = Application::$statuses;
        return view('applications.create', compact('companies', 'statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id'   => 'required|exists:companies,id',
            'position'     => 'required|string|max:255',
            'status'       => 'required|in:' . implode(',', array_keys(Application::$statuses)),
            'applied_date' => 'required|date',
            'job_link'     => 'nullable|url|max:255',
            'notes'        => 'nullable|string',
        ]);

        // Ensure the company belongs to this user
        $company = Company::findOrFail($validated['company_id']);
        if ($company->user_id !== Auth::id()) {
            abort(403);
        }

        $validated['user_id'] = Auth::id();
        Application::create($validated);

        return redirect()->route('applications.index')
            ->with('success', 'Application added successfully.');
    }

    public function show(Application $application)
    {
        $this->authorizeOwnership($application);
        $application->load('company');
        return view('applications.show', compact('application'));
    }

    public function edit(Application $application)
    {
        $this->authorizeOwnership($application);
        $companies = Company::where('user_id', Auth::id())->orderBy('name')->get();
        $statuses  = Application::$statuses;
        return view('applications.edit', compact('application', 'companies', 'statuses'));
    }

    public function update(Request $request, Application $application)
    {
        $this->authorizeOwnership($application);

        $validated = $request->validate([
            'company_id'   => 'required|exists:companies,id',
            'position'     => 'required|string|max:255',
            'status'       => 'required|in:' . implode(',', array_keys(Application::$statuses)),
            'applied_date' => 'required|date',
            'job_link'     => 'nullable|url|max:255',
            'notes'        => 'nullable|string',
        ]);

        $application->update($validated);

        return redirect()->route('applications.index')
            ->with('success', 'Application updated.');
    }

    public function destroy(Application $application)
    {
        $this->authorizeOwnership($application);
        $application->delete();

        return redirect()->route('applications.index')
            ->with('success', 'Application deleted.');
    }

    private function authorizeOwnership(Application $application)
    {
        if ($application->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
    }
}
