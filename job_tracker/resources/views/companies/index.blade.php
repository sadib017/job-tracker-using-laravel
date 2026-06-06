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
