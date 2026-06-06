<x-app-layout>
    <x-slot name="header">
        <h2 class="fw-semibold fs-4">Edit Application</h2>
    </x-slot>

    <div class="container py-4" style="max-width: 600px;">

        <form method="POST" action="{{ route('applications.update', $application) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Company *</label>
                <select name="company_id" class="form-select @error('company_id') is-invalid @enderror" required>
                    <option value="">Select Company</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}"
                            {{ old('company_id', $application->company_id) == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
                @error('company_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Position Title *</label>
                <input
                    type="text"
                    name="position"
                    class="form-control @error('position') is-invalid @enderror"
                    value="{{ old('position', $application->position) }}"
                    required
                >
                @error('position')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Status *</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}"
                            {{ old('status', $application->status) === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Applied Date *</label>
                <input
                    type="date"
                    name="applied_date"
                    class="form-control @error('applied_date') is-invalid @enderror"
                    value="{{ old('applied_date', $application->applied_date->format('Y-m-d')) }}"
                    required
                >
                @error('applied_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Job Link</label>
                <input
                    type="url"
                    name="job_link"
                    class="form-control @error('job_link') is-invalid @enderror"
                    value="{{ old('job_link', $application->job_link) }}"
                    placeholder="https://..."
                >
                @error('job_link')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $application->notes) }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Update Application</button>
            <a href="{{ route('applications.index') }}" class="btn btn-secondary ms-2">Cancel</a>
        </form>

    </div>
</x-app-layout>
