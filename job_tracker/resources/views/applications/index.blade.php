<x-app-layout>
    <x-slot name="header">
        <h2 class="fw-semibold fs-4">Applications</h2>
    </x-slot>

    <div class="container py-4">

        {{-- Flash Message --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Search & Filter --}}
        <form method="GET" action="{{ route('applications.index') }}" class="row g-2 mb-3">
            <div class="col-md-5">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search by position or company..."
                    value="{{ request('search') }}"
                >
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
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-outline-secondary">Filter</button>
                <a href="{{ route('applications.index') }}" class="btn btn-link text-decoration-none">Clear</a>
            </div>
        </form>

        {{-- Top Bar --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="text-muted mb-0">
                Showing {{ $applications->count() }} of {{ $applications->total() }} applications
            </p>
            <a href="{{ route('applications.create') }}" class="btn btn-primary">+ Add Application</a>
        </div>

        {{-- Applications Table --}}
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Position</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Applied Date</th>
                        <th>Job Link</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $index => $app)
                    <tr>
                        <td>{{ $applications->firstItem() + $index }}</td>
                        <td>{{ $app->position }}</td>
                        <td>
                            <a href="{{ route('companies.show', $app->company) }}">
                                {{ $app->company->name }}
                            </a>
                        </td>
                        <td>
                            @php
                                $badgeClass = match($app->status) {
                                    'accepted'                                   => 'bg-success',
                                    'rejected'                                   => 'bg-danger',
                                    'offered'                                    => 'bg-primary',
                                    'interview_scheduled', 'interview_completed' => 'bg-warning text-dark',
                                    default                                      => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">
                                {{ \App\Models\Application::$statuses[$app->status] }}
                            </span>
                        </td>
                        <td>{{ $app->applied_date->format('d M Y') }}</td>
                        <td>
                            @if($app->job_link)
                                <a href="{{ $app->job_link }}" target="_blank">View ↗</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('applications.show', $app) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('applications.edit', $app) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('applications.destroy', $app) }}" method="POST"
                                      onsubmit="return confirm('Delete this application?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No applications found.
                            <a href="{{ route('applications.create') }}">Add your first one →</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center mt-3">
            {{ $applications->links() }}
        </div>

    </div>
</x-app-layout>
