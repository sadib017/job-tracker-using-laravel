<x-app-layout>
    <x-slot name="header">
        <h2 class="fw-semibold fs-4">Application Details</h2>
    </x-slot>

    <div class="container py-4" style="max-width: 700px;">

        {{-- Status Badge at top --}}
        @php
            $badgeClass = match($application->status) {
                'accepted'                                  => 'bg-success',
                'rejected'                                  => 'bg-danger',
                'offered'                                   => 'bg-primary',
                'interview_scheduled', 'interview_completed' => 'bg-warning text-dark',
                default                                     => 'bg-secondary',
            };
        @endphp

        <div class="mb-3">
            <span class="badge {{ $badgeClass }} fs-6 px-3 py-2">
                {{ \App\Models\Application::$statuses[$application->status] }}
            </span>
        </div>

        {{-- Application Details Card --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">{{ $application->position }}</span>
                <div>
                    <a href="{{ route('applications.edit', $application) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('applications.destroy', $application) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Delete this application?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th style="width: 150px;">Position</th>
                        <td>{{ $application->position }}</td>
                    </tr>
                    <tr>
                        <th>Company</th>
                        <td>
                            <a href="{{ route('companies.show', $application->company) }}">
                                {{ $application->company->name }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge {{ $badgeClass }}">
                                {{ \App\Models\Application::$statuses[$application->status] }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Applied Date</th>
                        <td>{{ $application->applied_date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <th>Job Link</th>
                        <td>
                            @if($application->job_link)
                                <a href="{{ $application->job_link }}" target="_blank">View Job Posting ↗</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Notes</th>
                        <td>
                            @if($application->notes)
                                {{ $application->notes }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Added On</th>
                        <td>{{ $application->created_at->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <th>Last Updated</th>
                        <td>{{ $application->updated_at->format('d M Y, h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Quick Status Update --}}
        <div class="card mb-4">
            <div class="card-header fw-semibold">Quick Status Update</div>
            <div class="card-body">
                <form method="POST" action="{{ route('applications.update', $application) }}" class="d-flex gap-2 align-items-center">
                    @csrf
                    @method('PUT')
                    {{-- Keep all existing fields unchanged --}}
                    <input type="hidden" name="company_id"   value="{{ $application->company_id }}">
                    <input type="hidden" name="position"     value="{{ $application->position }}">
                    <input type="hidden" name="applied_date" value="{{ $application->applied_date->format('Y-m-d') }}">
                    <input type="hidden" name="job_link"     value="{{ $application->job_link }}">
                    <input type="hidden" name="notes"        value="{{ $application->notes }}">

                    <select name="status" class="form-select" style="max-width: 250px;">
                        @foreach(\App\Models\Application::$statuses as $key => $label)
                            <option value="{{ $key }}" {{ $application->status === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-outline-primary">Update Status</button>
                </form>
            </div>
        </div>

        <a href="{{ route('applications.index') }}" class="btn btn-secondary">← Back to Applications</a>

    </div>
</x-app-layout>
