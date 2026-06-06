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
