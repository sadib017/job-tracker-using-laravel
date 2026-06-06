# Job Application Tracker — Laravel Project Plan

> **Stack:** Laravel 12 · PHP 8+ · MySQL · Blade · Bootstrap 5  
> **Target:** 2–3 days · Beginner-friendly · Resume-ready

---

## What This Project Demonstrates

- Laravel MVC Architecture
- MySQL Database Design & Migrations
- Authentication & Authorization (Laravel Breeze)
- Full CRUD Operations
- Eloquent ORM & Relationships
- Form Validation
- Search & Filtering with Query Builder
- Dashboard Statistics
- Security (Middleware + ownership checks)
- Git & GitHub Workflow
- Deployment

---

## Database Design (Refined)

> **Key fix from original:** `user_id` is added directly to `applications` so ownership checks don't require a join through companies. This is simpler, safer, and more beginner-friendly.

### users
| Field      | Type      | Notes                  |
|------------|-----------|------------------------|
| id         | bigint PK | Auto-increment         |
| name       | string    |                        |
| email      | string    | Unique                 |
| password   | string    | Hashed by Laravel      |
| timestamps | timestamp | created_at, updated_at |

### companies
| Field      | Type      | Notes                       |
|------------|-----------|-----------------------------|
| id         | bigint PK |                             |
| user_id    | bigint FK | References users.id         |
| name       | string    |                             |
| website    | string    | Nullable                    |
| location   | string    | Nullable                    |
| notes      | text      | Nullable                    |
| timestamps | timestamp |                             |

### applications
| Field        | Type      | Notes                              |
|--------------|-----------|------------------------------------|
| id           | bigint PK |                                    |
| user_id      | bigint FK | References users.id (**ADDED**)    |
| company_id   | bigint FK | References companies.id            |
| position     | string    |                                    |
| status       | enum      | See status values below            |
| applied_date | date      |                                    |
| job_link     | string    | Nullable                           |
| notes        | text      | Nullable                           |
| timestamps   | timestamp |                                    |

### Application Status Values
```
applied | interview_scheduled | interview_completed | offered | rejected | accepted
```
> `offered` is added — a common real-world step between interview and acceptance.

### Relationships Summary
```
User ──< Company ──< Application
User ──< Application  (direct ownership, for security)
```

---

## Eloquent Relationships

### User Model (`app/Models/User.php`)
```php
public function companies()
{
    return $this->hasMany(Company::class);
}

public function applications()
{
    return $this->hasMany(Application::class);
}
```

### Company Model (`app/Models/Company.php`)
```php
public function user()
{
    return $this->belongsTo(User::class);
}

public function applications()
{
    return $this->hasMany(Application::class);
}
```

### Application Model (`app/Models/Application.php`)
```php
public function user()
{
    return $this->belongsTo(User::class);
}

public function company()
{
    return $this->belongsTo(Company::class);
}
```

---

## Folder Structure

```
app/
├── Http/
│   └── Controllers/
│       ├── DashboardController.php
│       ├── CompanyController.php
│       └── ApplicationController.php
├── Models/
│   ├── User.php
│   ├── Company.php
│   └── Application.php

database/
└── migrations/
    ├── xxxx_create_users_table.php       ← comes with Laravel
    ├── xxxx_create_companies_table.php
    └── xxxx_create_applications_table.php

resources/
└── views/
    ├── layouts/
    │   └── app.blade.php
    ├── dashboard/
    │   └── index.blade.php
    ├── companies/
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   ├── edit.blade.php
    │   └── show.blade.php
    └── applications/
        ├── index.blade.php
        ├── create.blade.php
        ├── edit.blade.php
        └── show.blade.php

routes/
└── web.php
```

---

# Phase-by-Phase Build Guide

---

## Phase 1 — Project Setup

### Step 1.1 — Create Laravel Project
```bash
composer create-project laravel/laravel job-tracker
cd job-tracker
```

### Step 1.2 — Configure Database
Open `.env` and update:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=job_tracker
DB_USERNAME=root
DB_PASSWORD=
```
Then create the database in phpMyAdmin (or MySQL CLI):
```sql
CREATE DATABASE job_tracker;
```

### Step 1.3 — Run Default Migrations
```bash
php artisan migrate
```

### Step 1.4 — Start Dev Server
```bash
php artisan serve
```
Visit: `http://127.0.0.1:8000`

### Step 1.5 — Initialize Git
```bash
git init
git add .
git commit -m "Initial Laravel setup"
```
Push to GitHub:
```bash
git remote add origin https://github.com/YOUR_USERNAME/job-tracker.git
git branch -M main
git push -u origin main
```

---

## Phase 2 — Authentication (Laravel Breeze)

### Step 2.1 — Install Breeze
```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
npm run build
php artisan migrate
```

### Step 2.2 — Verify
Visit `http://127.0.0.1:8000/register` — you should see the register form.  
Test register → login → logout to confirm it works.

### Step 2.3 — Commit
```bash
git add .
git commit -m "Authentication setup with Laravel Breeze"
```

---

## Phase 3 — Company Module

### Step 3.1 — Generate Files
```bash
php artisan make:model Company -mcr
```
This creates: `Company.php` model, migration, and resource controller in one command.

### Step 3.2 — Write the Migration
Open `database/migrations/xxxx_create_companies_table.php`:
```php
public function up(): void
{
    Schema::create('companies', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('name');
        $table->string('website')->nullable();
        $table->string('location')->nullable();
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}
```
Run it:
```bash
php artisan migrate
```

### Step 3.3 — Set Up the Model
`app/Models/Company.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['user_id', 'name', 'website', 'location', 'notes'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
```

### Step 3.4 — Write the Controller
`app/Http/Controllers/CompanyController.php`:
```php
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
        $companies = Auth::user()->companies()->latest()->paginate(10);
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
```

### Step 3.5 — Add Routes
`routes/web.php` — add inside the `auth` middleware group:
```php
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\DashboardController;

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('companies', CompanyController::class);
    Route::resource('applications', ApplicationController::class);
});
```

### Step 3.6 — Company Views

**`resources/views/companies/index.blade.php`:**
```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="fw-semibold fs-4">Companies</h2>
    </x-slot>

    <div class="container py-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Your Companies</h4>
            <a href="{{ route('companies.create') }}" class="btn btn-primary">+ Add Company</a>
        </div>

        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Name</th><th>Location</th><th>Website</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $company)
                <tr>
                    <td>{{ $company->name }}</td>
                    <td>{{ $company->location ?? '—' }}</td>
                    <td>
                        @if($company->website)
                            <a href="{{ $company->website }}" target="_blank">Visit</a>
                        @else —
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('companies.show', $company) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('companies.edit', $company) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('companies.destroy', $company) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this company?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted">No companies yet.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $companies->links() }}
    </div>
</x-app-layout>
```

**`resources/views/companies/create.blade.php`:**
```blade
<x-app-layout>
    <x-slot name="header"><h2 class="fw-semibold fs-4">Add Company</h2></x-slot>

    <div class="container py-4" style="max-width: 600px;">
        <form method="POST" action="{{ route('companies.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Company Name *</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Website</label>
                <input type="url" name="website" class="form-control @error('website') is-invalid @enderror"
                       value="{{ old('website') }}" placeholder="https://example.com">
                @error('website') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-control" value="{{ old('location') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save Company</button>
            <a href="{{ route('companies.index') }}" class="btn btn-secondary ms-2">Cancel</a>
        </form>
    </div>
</x-app-layout>
```

> For `edit.blade.php`, copy `create.blade.php`, change the form action to `route('companies.update', $company)`, add `@method('PUT')`, and populate values with `$company->name` etc.

### Step 3.7 — Commit
```bash
git add .
git commit -m "Company CRUD completed"
```

---

## Phase 4 — Application Module

### Step 4.1 — Generate Files
```bash
php artisan make:model Application -mcr
```

### Step 4.2 — Write the Migration
`database/migrations/xxxx_create_applications_table.php`:
```php
public function up(): void
{
    Schema::create('applications', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('company_id')->constrained()->onDelete('cascade');
        $table->string('position');
        $table->enum('status', [
            'applied',
            'interview_scheduled',
            'interview_completed',
            'offered',
            'rejected',
            'accepted'
        ])->default('applied');
        $table->date('applied_date');
        $table->string('job_link')->nullable();
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}
```
```bash
php artisan migrate
```

### Step 4.3 — Set Up the Model
`app/Models/Application.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'user_id', 'company_id', 'position',
        'status', 'applied_date', 'job_link', 'notes'
    ];

    protected $casts = [
        'applied_date' => 'date',
    ];

    // Human-readable status labels
    public static array $statuses = [
        'applied'              => 'Applied',
        'interview_scheduled'  => 'Interview Scheduled',
        'interview_completed'  => 'Interview Completed',
        'offered'              => 'Offered',
        'rejected'             => 'Rejected',
        'accepted'             => 'Accepted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
```

### Step 4.4 — Write the Controller
`app/Http/Controllers/ApplicationController.php`:
```php
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
        $query = Auth::user()->applications()->with('company');

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
        $companies = Auth::user()->companies()->orderBy('name')->get();
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
        $companies = Auth::user()->companies()->orderBy('name')->get();
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
```

### Step 4.5 — Application Views

**`resources/views/applications/index.blade.php`:**
```blade
<x-app-layout>
    <x-slot name="header"><h2 class="fw-semibold fs-4">Applications</h2></x-slot>

    <div class="container py-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Search & Filter --}}
        <form method="GET" action="{{ route('applications.index') }}" class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control"
                       placeholder="Search by position or company..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-secondary">Filter</button>
                <a href="{{ route('applications.index') }}" class="btn btn-link">Clear</a>
            </div>
        </form>

        <div class="d-flex justify-content-end mb-2">
            <a href="{{ route('applications.create') }}" class="btn btn-primary">+ Add Application</a>
        </div>

        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Position</th><th>Company</th><th>Status</th>
                    <th>Applied Date</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $app)
                <tr>
                    <td>{{ $app->position }}</td>
                    <td>{{ $app->company->name }}</td>
                    <td>
                        @php
                            $badgeClass = match($app->status) {
                                'accepted'             => 'bg-success',
                                'rejected'             => 'bg-danger',
                                'offered'              => 'bg-primary',
                                'interview_scheduled',
                                'interview_completed'  => 'bg-warning text-dark',
                                default                => 'bg-secondary',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">
                            {{ Application::$statuses[$app->status] }}
                        </span>
                    </td>
                    <td>{{ $app->applied_date->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('applications.show', $app) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('applications.edit', $app) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('applications.destroy', $app) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this application?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">No applications found.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $applications->links() }}
    </div>
</x-app-layout>
```

**`resources/views/applications/create.blade.php`:**
```blade
<x-app-layout>
    <x-slot name="header"><h2 class="fw-semibold fs-4">Add Application</h2></x-slot>

    <div class="container py-4" style="max-width: 600px;">
        <form method="POST" action="{{ route('applications.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Company *</label>
                <select name="company_id" class="form-select @error('company_id') is-invalid @enderror" required>
                    <option value="">Select Company</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
                @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Position Title *</label>
                <input type="text" name="position" class="form-control @error('position') is-invalid @enderror"
                       value="{{ old('position') }}" required>
                @error('position') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Status *</label>
                <select name="status" class="form-select" required>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" {{ old('status', 'applied') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Applied Date *</label>
                <input type="date" name="applied_date" class="form-control @error('applied_date') is-invalid @enderror"
                       value="{{ old('applied_date', date('Y-m-d')) }}" required>
                @error('applied_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Job Link</label>
                <input type="url" name="job_link" class="form-control @error('job_link') is-invalid @enderror"
                       value="{{ old('job_link') }}" placeholder="https://...">
                @error('job_link') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save Application</button>
            <a href="{{ route('applications.index') }}" class="btn btn-secondary ms-2">Cancel</a>
        </form>
    </div>
</x-app-layout>
```

> For `edit.blade.php`, copy `create.blade.php`, change the action to `route('applications.update', $application)`, add `@method('PUT')`, and pre-fill values with `$application->position` etc.

### Step 4.6 — Commit
```bash
git add .
git commit -m "Application CRUD completed"
```

---

## Phase 5 — Dashboard

### Step 5.1 — Generate Controller
```bash
php artisan make:controller DashboardController
```

### Step 5.2 — Write the Controller
`app/Http/Controllers/DashboardController.php`:
```php
<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $stats = [
            'total_applications' => $user->applications()->count(),
            'total_companies'    => $user->companies()->count(),
            'interviews'         => $user->applications()
                                        ->whereIn('status', ['interview_scheduled', 'interview_completed'])
                                        ->count(),
            'offered'            => $user->applications()->where('status', 'offered')->count(),
            'accepted'           => $user->applications()->where('status', 'accepted')->count(),
            'rejected'           => $user->applications()->where('status', 'rejected')->count(),
        ];

        // 5 most recent applications
        $recentApplications = $user->applications()
            ->with('company')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', compact('stats', 'recentApplications'));
    }
}
```

### Step 5.3 — Dashboard View
`resources/views/dashboard/index.blade.php`:
```blade
<x-app-layout>
    <x-slot name="header"><h2 class="fw-semibold fs-4">Dashboard</h2></x-slot>

    <div class="container py-4">
        {{-- Stats Row --}}
        <div class="row g-3 mb-4">
            @foreach([
                ['label' => 'Applications', 'value' => $stats['total_applications'], 'color' => 'primary'],
                ['label' => 'Companies',    'value' => $stats['total_companies'],    'color' => 'secondary'],
                ['label' => 'Interviews',   'value' => $stats['interviews'],         'color' => 'warning'],
                ['label' => 'Offered',      'value' => $stats['offered'],            'color' => 'info'],
                ['label' => 'Accepted',     'value' => $stats['accepted'],           'color' => 'success'],
                ['label' => 'Rejected',     'value' => $stats['rejected'],           'color' => 'danger'],
            ] as $card)
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card text-center border-{{ $card['color'] }}">
                    <div class="card-body py-3">
                        <h3 class="display-6 fw-bold text-{{ $card['color'] }}">{{ $card['value'] }}</h3>
                        <small class="text-muted">{{ $card['label'] }}</small>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Recent Applications --}}
        <h5 class="mb-3">Recent Applications</h5>
        <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr><th>Position</th><th>Company</th><th>Status</th><th>Date</th></tr>
            </thead>
            <tbody>
                @forelse($recentApplications as $app)
                <tr>
                    <td>{{ $app->position }}</td>
                    <td>{{ $app->company->name }}</td>
                    <td>{{ \App\Models\Application::$statuses[$app->status] }}</td>
                    <td>{{ $app->applied_date->format('d M Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted">No applications yet.</td></tr>
                @endforelse
            </tbody>
        </table>

        <a href="{{ route('applications.create') }}" class="btn btn-primary mt-2">+ Add Application</a>
    </div>
</x-app-layout>
```

### Step 5.4 — Commit
```bash
git add .
git commit -m "Dashboard with stats added"
```

---

## Phase 6 — Navigation Bar

Update `resources/views/layouts/navigation.blade.php` (comes with Breeze) to add links to Companies and Applications. Find the nav links section and add:

```blade
<x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
    Dashboard
</x-nav-link>
<x-nav-link :href="route('companies.index')" :active="request()->routeIs('companies.*')">
    Companies
</x-nav-link>
<x-nav-link :href="route('applications.index')" :active="request()->routeIs('applications.*')">
    Applications
</x-nav-link>
```

---

## Phase 7 — Redirect After Login

By default Breeze redirects to `/dashboard`. Make sure `routes/web.php` has:
```php
Route::get('/', function () {
    return redirect()->route('dashboard');
});
```

---

## Phase 8 — Security Checklist

All critical security is already built into the controllers above. Verify each point:

- [x] All routes are inside `Route::middleware('auth')` — unauthenticated users can't access anything
- [x] `authorizeOwnership()` in CompanyController checks `company->user_id === Auth::id()`
- [x] `authorizeOwnership()` in ApplicationController checks `application->user_id === Auth::id()`
- [x] Company ownership is verified before an application is created under it
- [x] All forms use `@csrf` — protects against CSRF attacks
- [x] All user input passes through `$request->validate()` before hitting the database
- [x] `$fillable` arrays are set on every model — no mass assignment vulnerabilities

---

## Phase 9 — UI Polish Tips

Add to your main layout (`resources/views/layouts/app.blade.php`) inside `<head>`:
```html
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
```
And before `</body>`:
```html
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
```

Color-coded status badges (already in the views above) make the UI look polished with almost no effort.

---

## Phase 10 — Git Commit History (Final)

Your commit log should look like this:
```
Initial Laravel setup
Authentication setup with Laravel Breeze
Company CRUD completed
Application CRUD completed
Dashboard with stats added
Navigation updated
Search and filters added
Validation and security hardened
UI polish and Bootstrap styling
README updated
```

---

## Phase 11 — GitHub README (Important for Recruiters)

Create `README.md` at the root:
```markdown
# Job Application Tracker

A full-stack web application built with **Laravel 12** and **MySQL** to help users manage their job search.

## Features
- User authentication (register, login, logout)
- Company management (CRUD)
- Job application tracking with status updates
- Dashboard with application statistics
- Search by position/company, filter by status
- Ownership-based authorization — users only see their own data

## Tech Stack
- **Backend:** Laravel 12, PHP 8+
- **Database:** MySQL with Eloquent ORM
- **Frontend:** Blade Templates, Bootstrap 5
- **Auth:** Laravel Breeze

## Database Design
Users → Companies → Applications (with direct user_id on applications for security)

## Setup Instructions
1. Clone the repo
2. Run `composer install`
3. Copy `.env.example` to `.env` and configure your database
4. Run `php artisan key:generate`
5. Run `php artisan migrate`
6. Run `npm install && npm run build`
7. Run `php artisan serve`
```

---

## Phase 12 — Deployment (Optional but Impressive)

### Deploy to Railway (Free tier, easiest)
1. Push your project to GitHub
2. Go to [railway.app](https://railway.app) → New Project → Deploy from GitHub
3. Add a MySQL database service
4. Set your environment variables (copy from `.env`)
5. Railway auto-detects Laravel and deploys

### Before deploying, run:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

# Resume Description

> Built a full-stack **Job Application Tracker** using **Laravel 12** and **MySQL** featuring user authentication, company and application CRUD, status tracking, dashboard analytics, search/filter functionality, ownership-based authorization, and relational database design with Eloquent ORM.

---

# Learning Outcomes Checklist

After completing this project you will understand:

- [x] Laravel MVC Architecture
- [x] Routing (resource routes, middleware groups)
- [x] Controllers (resource controllers, dependency injection)
- [x] Blade Templates (layouts, components, loops, conditionals)
- [x] Authentication (Laravel Breeze)
- [x] Authorization (ownership checks, 403 abort)
- [x] CRUD Operations (all 5 HTTP methods)
- [x] Eloquent ORM (models, fillable, casts)
- [x] Database Relationships (hasMany, belongsTo)
- [x] Migrations (foreignId, constrained, onDelete)
- [x] Form Validation (required, nullable, url, exists, in)
- [x] Query Builder (where, orWhere, whereHas, whereIn, paginate)
- [x] Flash Messages (session success)
- [x] CSRF Protection
- [x] Mass Assignment Protection ($fillable)
- [x] Git & GitHub Workflow
- [x] Deployment Basics
