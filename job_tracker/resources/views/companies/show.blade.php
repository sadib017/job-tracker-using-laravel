<x-app-layout>
    <x-slot name="header">
        <h2 class="fw-semibold fs-4">{{ $company->name }}</h2>
    </x-slot>

    <div class="container py-4" style="max-width: 800px;">

        {{-- Company Details Card --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Company Details</span>
                <div>
                    <a href="{{ route('companies.edit', $company) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('companies.destroy', $company) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Delete this company and all its applications?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th style="width: 150px;">Company Name</th>
                        <td>{{ $company->name }}</td>
                    </tr>
                    <tr>
                        <th>Website</th>
                        <td>
                            @if($company->website)
                                <a href="{{ $company->website }}" target="_blank">{{ $company->website }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Location</th>
                        <td>{{ $company->location ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Notes</th>
                        <td>{{ $company->notes ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Added On</th>
                        <td>{{ $company->created_at->format('d M Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Applications Under This Company --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0">Applications at {{ $company->name }}</h5>
            <a href="{{ route('applications.create') }}" class="btn btn-sm btn-primary">+ Add Application</a>
        </div>

        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Position</th>
                    <th>Status</th>
                    <th>Applied Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($company->applications as $app)
                <tr>
                    <td>{{ $app->position }}</td>
                    <td>
                        @php
                            $badgeClass = match($app->status) {
                                'accepted'                                  => 'bg-success',
                                'rejected'                                  => 'bg-danger',
                                'offered'                                   => 'bg-primary',
                                'interview_scheduled', 'interview_completed' => 'bg-warning text-dark',
                                default                                     => 'bg-secondary',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">
                            {{ \App\Models\Application::$statuses[$app->status] }}
                        </span>
                    </td>
                    <td>{{ $app->applied_date->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('applications.show', $app) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('applications.edit', $app) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('applications.destroy', $app) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this application?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">No applications yet for this company.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <a href="{{ route('companies.index') }}" class="btn btn-secondary mt-2">← Back to Companies</a>

    </div>
</x-app-layout>
