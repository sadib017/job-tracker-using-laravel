<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    // Show all companies for the logged-in user
    public function index()
    {
        $companies = Company::where('user_id', Auth::id())->latest()->paginate(10);
        return view('companies.index', compact('companies'));
    }

    // Show create form
    public function create()
    {
        return view('companies.create');
    }

    // Save new company
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'website'  => 'nullable|url|max:255',
            'location' => 'nullable|string|max:255',
            'notes'    => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();
        Company::create($validated);

        return redirect()->route('companies.index')
            ->with('success', 'Company added successfully.');
    }

    // Show single company with its applications
    public function show(Company $company)
    {
        $this->authorizeOwnership($company);
        $company->load('applications');
        return view('companies.show', compact('company'));
    }

    // Show edit form
    public function edit(Company $company)
    {
        $this->authorizeOwnership($company);
        return view('companies.edit', compact('company'));
    }

    // Update company
    public function update(Request $request, Company $company)
    {
        $this->authorizeOwnership($company);

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'website'  => 'nullable|url|max:255',
            'location' => 'nullable|string|max:255',
            'notes'    => 'nullable|string',
        ]);

        $company->update($validated);

        return redirect()->route('companies.index')
            ->with('success', 'Company updated successfully.');
    }

    // Delete company
    public function destroy(Company $company)
    {
        $this->authorizeOwnership($company);
        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', 'Company deleted.');
    }

    // Private ownership check — used instead of Policy for simplicity
    private function authorizeOwnership(Company $company)
    {
        if ($company->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
    }
}
