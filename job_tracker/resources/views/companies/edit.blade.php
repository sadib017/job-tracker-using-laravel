<x-app-layout>
    <x-slot name="header">
        <h2 class="fw-semibold fs-4">Edit Company</h2>
    </x-slot>

    <div class="container py-4" style="max-width: 600px;">

        <form method="POST" action="{{ route('companies.update', $company) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Company Name *</label>
                <input
                    type="text"
                    name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $company->name) }}"
                    required
                >
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Website</label>
                <input
                    type="url"
                    name="website"
                    class="form-control @error('website') is-invalid @enderror"
                    value="{{ old('website', $company->website) }}"
                    placeholder="https://example.com"
                >
                @error('website')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Location</label>
                <input
                    type="text"
                    name="location"
                    class="form-control"
                    value="{{ old('location', $company->location) }}"
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $company->notes) }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Update Company</button>
            <a href="{{ route('companies.index') }}" class="btn btn-secondary ms-2">Cancel</a>
        </form>

    </div>
</x-app-layout>
