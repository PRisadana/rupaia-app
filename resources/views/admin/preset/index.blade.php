@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center my-4">
            <h1 class="m-0">Presets</h1>

            <a href="{{ route('admin.preset.create') }}"
                class="btn btn-outline-secondary px-4 d-inline-flex align-items-center fw-semibold">
                {{ __('Add Preset') }}
            </a>
        </div>
        <hr>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Preset Name</th>
                    <th scope="col">Preset File Path</th>
                    <th scope="col">Is Active</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($presets as $preset)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $preset->preset_name }}</td>
                        <td>{{ $preset->preset_file_path }}</td>
                        <td>{{ $preset->is_active ? 'Yes' : 'No' }}</td>
                        <td>
                            <a href="{{ route('admin.preset.edit', $preset) }}"
                                class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('admin.preset.destroy', $preset) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Are you sure you want to delete this preset?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No presets found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
